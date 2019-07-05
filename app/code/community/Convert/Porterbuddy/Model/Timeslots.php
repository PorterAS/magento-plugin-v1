<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Timeslots
{
    /**
     * @var Mage_Core_Helper_Data
     */
    protected $coreHelper;

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
        Convert_Porterbuddy_Helper_Data $helper = null,
        Mage_Core_Helper_Data $coreHelper = null
    ) {
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');
        $this->coreHelper = $coreHelper ?: Mage::helper('core');
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     * @return array
     * @throws Exception
     */
    public function getShippingRates(Mage_Sales_Model_Quote_Address $address)
    {
        $address->setLimitCarrier(Convert_Porterbuddy_Model_Carrier::CODE);
        $address->collectShippingRates()->save();

        return $address->getGroupedAllShippingRates();
    }


    /**
     * Formats timeslot title, e.g. "Friday 10:00 - 12:00" or "Today 14:00 - 16:00"
     *
     * @param DateTime $startTime
     * @param DateTime $endTime
     * @param bool $moreInfo
     * @return string
     */
    public function formatTimeslot(DateTime $startTime, DateTime $endTime, $moreInfo = true)
    {
        $parts = [];

        if ($moreInfo) {
            $today = $this->helper->getCurrentTime();
            $tomorrow = clone $today;
            $tomorrow->modify('+1 day');

            if ($startTime->format('Y-m-d') == $today->format('Y-m-d')) {
                $dayOfWeek = $this->helper->__('Today');
            } elseif ($startTime->format('Y-m-d') == $tomorrow->format('Y-m-d')) {
                $dayOfWeek = $this->helper->__('Tomorrow');
            } else {
                $dayOfWeek = $this->helper->__($startTime->format('l'));
            }

            $parts[] = $dayOfWeek;
        }

        // local time - shift timezone
        $timezone = $this->helper->getTimezone();
        $startTime = clone $startTime;
        $startTime->setTimezone($timezone);
        $endTime = clone $endTime;
        $endTime->setTimezone($timezone);

        $parts[] = $startTime->format('H:i') . '–' . $endTime->format('H:i');

        return implode(' ', $parts);
    }
}
