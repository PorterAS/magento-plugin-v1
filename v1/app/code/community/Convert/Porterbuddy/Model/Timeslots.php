<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Timeslots
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @param array|null $data
     * @param Convert_Porterbuddy_Helper_Data|null $helper
     */
    public function __construct(
        array $data = null, // for getModel to work
        Convert_Porterbuddy_Helper_Data $helper = null
    ) {
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');
    }

    /**
     * Exclude ASAP if less than 1 hour to closing
     *
     * @return bool
     */
    public function canUseAsap()
    {
        $now = $this->helper->getCurrentTime();
        list($openTime, $closeTime) = $this->getOpenHours($now);

        if ($now < $openTime || $now > $closeTime) {
            $this->helper->log(
                sprintf(
                    "Cannot use ASAP - outside working hours `%s`-`%s` (UTC).",
                    $openTime->format('H:i'),
                    $closeTime->format('H:i')
                ),
                null,
                Zend_Log::NOTICE
            );
            return false;
        }

        // unavailable if less than 1 hour to closing
        $minutesToClosing = ($closeTime->getTimestamp() - $now->getTimestamp())/60;
        $asapCutoff = $this->helper->getAsapCutoff();
        if ($minutesToClosing < $asapCutoff*60) {
            $this->helper->log(
                sprintf(
                    "Cannot use ASAP - `%d` minutes to closing at `%s` (UTC).",
                    (int)$minutesToClosing,
                    $closeTime->format('H:i')
                ),
                null,
                Zend_Log::NOTICE
            );
            return false;
        }

        return true;
    }

    /**
     * Samples:
     * (6:40 - 22) => 7, 9, 11, 13, 15, 17, 19, 21-22
     * (8 - 13) => 8, 9, 11
     *
     * @param DateTime $apiFrom
     * @param DateTime $apiUntil
     * @return array pairs of start-end times [DateTime, DateTime]
     */
    public function getTimeslots(DateTime $apiFrom, DateTime $apiUntil)
    {
        // don't change input data. better safe than sorry
        $apiFrom = clone $apiFrom;
        $apiUntil = clone $apiUntil;

        list($from, $until) = $this->adjustToOpenHours($apiFrom, $apiUntil);

        // align API from to next round hour
        if ($from->format('i') > 0) {
            $from->modify('+1 hour');
        }
        $from->setTime($from->format('H'), 0, 0);

        // cutoff timeslot
        $from = max($this->getCutoffTimeslot(), $from);


        $totalMinutes = ($until->getTimestamp() - $from->getTimestamp())/60;
        if ($totalMinutes < $this->helper->getTimeslotWindow()*60) {
            // about to close, can't fit full timeslot
            return array();
        }

        $result = $this->generateIntervals($from, $until);

        return $result;
    }

    /**
     * Generates equal time intervals
     * If last inverval is not full, its starting time extends back to make it full
     *
     * @param DateTime $from
     * @param DateTime $until
     * @return array
     */
    public function generateIntervals(DateTime $from, DateTime $until)
    {
        $result = array();

        $timeslotLength = $this->helper->getTimeslotWindow();
        for ($timeslotStart = clone $from; $timeslotStart < $until; $timeslotStart->modify("+$timeslotLength hours")) {
            // ignore intervals with 1 second, e.g. 18:00-18:00:01
            if ($until->getTimestamp() - $timeslotStart->getTimestamp() <= 1) {
                break;
            }

            $timeslotEnd = clone $timeslotStart;
            $timeslotEnd->modify("+$timeslotLength hours");

            if ($timeslotEnd > $until) {
                // shorter interval - it ends with $until border
                $timeslotEnd = clone $until;
                // ignore seconds in end interval, e.g. 18:00:01 => 18:00
                $timeslotEnd->setTime($timeslotEnd->format('H'), $timeslotEnd->format('i'), 0);

                // for last short timeslot make it overlap previous one, but make it full shifting start time back
                if ($result) {
                    // this is a check to ensure previous timeslot exists so we can safely shift back
                    // full timeslot length and not pass original $from restriction
                    $timeslotStart = clone $timeslotEnd;
                    $timeslotStart->modify("-$timeslotLength hours");
                }
            }

            $result[] = array(clone $timeslotStart, clone $timeslotEnd);
        }

        return $result;
    }

    /**
     * @return DateTime
     */
    public function getCutoffTimeslot()
    {
        $timeslotStart = $this->helper->getCurrentTime();
        $timeslotCutoff = $this->helper->getTimeslotCutoff();

        // 1 hour cutoff:
        // 12:00 => 13:00
        // 12:11 => 13:00
        // 12:59 => 13:00
        // 12:11, 2 hour cutoff => 14:00
        if ($timeslotStart->format('i') > 0) {
            $timeslotCutoff -= 1; // count started hour as full
        }
        $timeslotStart->modify("+$timeslotCutoff hours");

        // round to next full hour, e.g. 13:30 => 14:00
        if ($timeslotStart->format('i') > 0) {
            $timeslotStart->modify('+1 hour');
        }

        // reset minutes and seconds
        $timeslotStart->setTime($timeslotStart->format('H'), 0, 0);

        return $timeslotStart;
    }

    /**
     * @param DateTime $apiFrom
     * @param DateTime $apiUntil
     * @return DateTime[]
     */
    public function adjustToOpenHours(DateTime $apiFrom, DateTime $apiUntil)
    {
        list($openTime, $closeTime) = $this->getOpenHours($apiFrom);

        if ($apiFrom < $openTime) {
            $apiFrom = $openTime;
        }
        if ($apiUntil > $closeTime) {
            $apiUntil = $closeTime;
        }

        return array($apiFrom, $apiUntil);
    }

    /**
     * Returns open hours range in UTC timezone
     *
     * @param DateTime
     * @return DateTime[]
     */
    public function getOpenHours(DateTime $baseDate)
    {
        $localTimezone = $this->helper->getTimezone();
        $defaultTimezone = new DateTimeZone('UTC');

        // ensure local timezone
        $baseDate = clone $baseDate;
        $baseDate->setTimezone($localTimezone);

        list($openTime, $closeTime) = $this->helper->getOpenHours(strtolower($baseDate->format('D')));

        // set time in local timezone and convert to UTC
        $openDatetime = clone $baseDate;
        $parts = explode(':', $openTime);
        $openDatetime->setTimezone($localTimezone);
        $openDatetime->setTime($parts[0], $parts[1], 0);
        $openDatetime->setTimezone($defaultTimezone);

        $closeDatetime = clone $baseDate;
        $parts = explode(':', $closeTime);
        $closeDatetime->setTimezone($localTimezone);
        $closeDatetime->setTime($parts[0], $parts[1], 0);
        $closeDatetime->setTimezone($defaultTimezone);

        return array($openDatetime, $closeDatetime);
    }

    /**
     * Formats timeslot title, e.g. "Friday 10:00 - 12:00" or "Today 14:00 - 16:00"
     *
     * @param DateTime $startTime
     * @param DateTime $endTime
     * @return string
     */
    public function formatTimeslot(DateTime $startTime, DateTime $endTime)
    {
        $today = new DateTime();
        $tomorrow = new DateTime('tomorrow');

        if ($startTime->format('Y-m-d') == $today->format('Y-m-d')) {
            $dayOfWeek = $this->helper->__('Today');
        } elseif ($startTime->format('Y-m-d') == $tomorrow->format('Y-m-d')) {
            $dayOfWeek = $this->helper->__('Tomorrow');
        } else {
            $dayOfWeek = $this->helper->__($startTime->format('l'));
        }

        // local time - shift timezone
        $timezone = $this->helper->getTimezone();
        $startTime = clone $startTime;
        $startTime->setTimezone($timezone);
        $endTime = clone $endTime;
        $endTime->setTimezone($timezone);

        return $dayOfWeek . ' ' . $startTime->format('H:i') . '&ndash;' . $endTime->format('H:i');
    }
}
