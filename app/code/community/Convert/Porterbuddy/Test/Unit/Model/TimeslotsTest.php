<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Test_Unit_Model_TimeslotsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Convert_Porterbuddy_Model_Timeslots
     */
    protected $timeslots;

    /**
     * @var Convert_Porterbuddy_Helper_Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        // FIXME: bootstrap file
        require_once realpath(__DIR__) . '/../../../../../../../../../../../app/Mage.php';
        Mage::app();

        $this->helper = $this->getMockBuilder(Convert_Porterbuddy_Helper_Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper
            ->method('getTimezone')
            ->willReturn(new DateTimeZone('Europe/Warsaw'));

        $this->timeslots = new Convert_Porterbuddy_Model_Timeslots(null, $this->helper);
    }

    /**
     * @param int $timeslotWindow
     * @param string $from
     * @param string $until
     * @param array $expected
     * @param string $message optional
     * @dataProvider generateIntervalsProvider
     */
    public function testGenerateIntervals($timeslotWindow, $from, $until, array $expected, $message = null)
    {
        $this->helper
            ->method('getTimeslotWindow')
            ->willReturn($timeslotWindow);

        $from = new DateTime($from);
        $until = new DateTime($until);
        $expected = array_map(function($pair) {
            return array(
                new DateTime($pair[0]),
                new DateTime($pair[1]),
            );
        }, $expected);

        $result = $this->timeslots->generateIntervals($from, $until);
        $this->assertEquals($expected, $result, $message);
    }

    /**
     * @return array
     */
    public function generateIntervalsProvider()
    {
        // timeslot cutoff, timeslot window, from, until, expected results
        return array(
            // (6:40 - 22), 2 hrs => 7, 9, 11, 13, 15, 17, 19, 21-22
            array(
                2, '2017-07-25T07:00:00+00:00', '2017-07-25T22:00:00.000Z',
                array(
                    array('2017-07-25T07:00:00+00:00', '2017-07-25T09:00:00+00:00'),
                    array('2017-07-25T09:00:00+00:00', '2017-07-25T11:00:00+00:00'),
                    array('2017-07-25T11:00:00+00:00', '2017-07-25T13:00:00+00:00'),
                    array('2017-07-25T13:00:00+00:00', '2017-07-25T15:00:00+00:00'),
                    array('2017-07-25T15:00:00+00:00', '2017-07-25T17:00:00+00:00'),
                    array('2017-07-25T17:00:00+00:00', '2017-07-25T19:00:00+00:00'),
                    array('2017-07-25T19:00:00+00:00', '2017-07-25T21:00:00+00:00'),
                    array('2017-07-25T20:00:00+00:00', '2017-07-25T22:00:00+00:00'),
                ),
                'First testcase'
            ),
            // (8 - 13), 2 hrs => 8, 10, 12-13
            array(
                2, '2017-07-25T09:00:00+00:00', '2017-07-25T13:00:00.000Z',
                array(
                    array('2017-07-25T09:00:00+00:00', '2017-07-25T11:00:00+00:00'),
                    array('2017-07-25T11:00:00+00:00', '2017-07-25T13:00:00+00:00'),
                ),
                'Second testcase'
            ),
            // (8 - 12), 2 hrs => 8, 10
            array(
                2, '2017-07-25T09:00:00+00:00', '2017-07-25T12:00:00.000Z',
                array(
                    array('2017-07-25T09:00:00+00:00', '2017-07-25T11:00:00+00:00'),
                    array('2017-07-25T10:00:00+00:00', '2017-07-25T12:00:00+00:00'),
                ),
                'Third testcase'
            ),
            // (7 - 9), 1 hr => 7, 8
            array(
                1, '2017-07-25T08:00:00+00:00', '2017-07-25T09:00:00.000Z',
                array(
                    array('2017-07-25T08:00:00+00:00', '2017-07-25T09:00:00+00:00'),
                ),
                '1 hour timeslot'
            ),
            // (7:05 - 10), 1 hr => 8, 9
            array(
                1, '2017-07-25T08:00:00+00:00', '2017-07-25T10:00:00.000Z',
                array(
                    array('2017-07-25T08:00:00+00:00', '2017-07-25T09:00:00+00:00'),
                    array('2017-07-25T09:00:00+00:00', '2017-07-25T10:00:00+00:00'),
                ),
                '1 hour timeslot'
            ),
            // weird extra second in the end time
            array(
                2, '2017-07-25T17:00:00.000Z', '2017-07-25T18:00:01.000Z',
                array(
                    array('2017-07-25T17:00:00+00:00', '2017-07-25T18:00:00+00:00'),
                ),
                '1 extra second'
            ),
            array(
                2, '2017-07-25T15:00:00+00:00', '2017-07-25T18:00:00.000Z',
                array(
                    array('2017-07-25T15:00:00+00:00', '2017-07-25T17:00:00+00:00'),
                    array('2017-07-25T16:00:00+00:00', '2017-07-25T18:00:00+00:00'),
                ),
                'Overlapping last short timeslot'
            ),
        );
    }

    /**
     * @param string $now
     * @param float $cutoff
     * @param string $expected
     * @param null $message
     * @dataProvider getCutoffTimeslotProvider
     */
    public function testGetCutoffTimeslot($now, $cutoff, $expected, $message = null)
    {
        $now = new DateTime($now);
        $expected = new DateTime($expected);

        $this->helper
            ->method('getCurrentTime')
            ->willReturn($now);
        $this->helper
            ->method('getTimeslotCutoff')
            ->willReturn($cutoff);

        $result = $this->timeslots->getCutoffTimeslot();
        $this->assertEquals($expected, $result, $message);
    }

    /**
     * @return array
     */
    public function getCutoffTimeslotProvider()
    {
        return array(
            array('2017-07-25T12:00:00+00:00', 1, '2017-07-25T13:00:00+00:00'),
            array('2017-07-25T12:11:00+00:00', 1, '2017-07-25T13:00:00+00:00'),
            array('2017-07-25T12:59:00+00:00', 1, '2017-07-25T13:00:00+00:00'),

            array('2017-07-25T12:11:00+00:00', 2, '2017-07-25T14:00:00+00:00'),
            array('2017-07-25T12:30:00+00:00', 2, '2017-07-25T14:00:00+00:00'),
        );
    }

    /**
     * @param string $apiFrom
     * @param string $apiUntil
     * @param string $openHours
     * @param array $expected
     * @param string $message optional
     * @dataProvider adjustToOpenHoursProvider
     */
    public function testAdjustToOpenHours($currentTime, $apiFrom, $apiUntil, $openHours, $expected, $message = null)
    {
        $this->helper
            ->method('getCurrentTime')
            ->willReturn(new DateTime($currentTime));
        $this->helper
            ->method('getOpenHours')
            ->willReturn($openHours);
        $expected = array_map(function($date) {
            return new DateTime($date);
        }, $expected);

        $result = $this->timeslots->adjustToOpenHours(new DateTime($apiFrom), new DateTime($apiUntil));
        $this->assertEquals($expected, $result, $message);
    }

    /**
     * @return array
     */
    public function adjustToOpenHoursProvider()
    {
        return array(
            array(
                '2017-07-25T06:00:00+00:00',
                '2017-07-25T06:40:00+00:00', '2017-07-25T18:00:01.000Z', // api times
                array('09:00', '18:00'), // shop open hours, 09:00 CET = 07:00 UTC
                array('2017-07-25T07:00:00+00:00', '2017-07-25T16:00:00+00:00'),
                'Today - shorter open hours',
            ),
            array(
                '2017-07-25T06:00:00+00:00',
                '2017-07-26T06:00:00+00:00', '2017-07-26T18:00:01.000Z',
                array('09:00', '18:00'), // shop open hours, 09:00 CET = 07:00 UTC
                array('2017-07-26T07:00:00+00:00', '2017-07-26T16:00:00+00:00'),
                'Next day',
            ),
        );
    }

    /**
     * @param string $currentTime
     * @param array $openHours
     * @param float $asapCutoff
     * @param bool $expected
     * @param null $message
     * @dataProvider canUseAsapProvider
     */
    public function testCanUseAsap($currentTime, $openHours, $asapCutoff, $expected, $message = null)
    {
        $this->helper
            ->method('getCurrentTime')
            ->willReturn(new DateTime($currentTime));
        $this->helper
            ->method('getOpenHours')
            ->willReturn($openHours);
        $this->helper
            ->method('getAsapCutoff')
            ->willReturn($asapCutoff);

        $result = $this->timeslots->canUseAsap();

        $this->assertEquals($expected, $result, $message);
    }

    /**
     * @return array
     */
    public function canUseAsapProvider()
    {
        return array(
            array(
                '2017-07-25T06:40:00+00:00', array('09:00', '18:00'), 1.0,
                false, 'early morning - shop not opened'
            ),
            array(
                '2017-07-25T07:40:00+00:00', array('09:00', '18:00'), 1.0,
                true, 'shop just opened'
            ),
            array(
                '2017-07-25T14:59:00+00:00', array('09:00', '18:00'), 1.0,
                true, 'more than one hour to closing - ASAP available'
            ),
            array(
                '2017-07-25T15:01:00+00:00', array('09:00', '18:00'), 1.0,
                false, 'less than an hour to closing, ASAP disabled'
            ),
            array(
                '2017-07-25T20:00:00+00:00', array('09:00', '18:00'), 1.0,
                false, 'last evening, shop closed'
            ),
            // 1.5 hr cutoff
            array(
                '2017-07-25T14:29:00+00:00', array('09:00', '18:00'), 1.5,
                true, 'more than 1.5 hrs to closing - ASAP available'
            ),
            array(
                '2017-07-25T14:31:00+00:00', array('09:00', '18:00'), 1.5,
                false, 'less than 1.5 hrs to closing, ASAP disabled'
            ),
        );
    }

    /**
     * @param $currentTime
     * @param $openHours
     * @param $apiFrom
     * @param $apiUntil
     * @param $expected
     * @param null $message
     * @dataProvider getTimeslotsProvider
     */
    public function testGetTimeslots($currentTime, $openHours, $apiFrom, $apiUntil, $expected, $message = null)
    {
        $this->helper
            ->method('getCurrentTime')
            ->willReturn(new DateTime($currentTime));
        $this->helper
            ->method('getTimeslotWindow')
            ->willReturn(2);
        $this->helper
            ->method('getTimeslotCutoff')
            ->willReturn(1);
        $this->helper
            ->method('getOpenHours')
            ->willReturn($openHours);
        $this->helper
            ->method('getAsapCutoff')
            ->willReturn(1.0);

        $expected = array_map(function($pair) {
            return array(
                new DateTime($pair[0]),
                new DateTime($pair[1]),
            );
        }, $expected);

        $result = $this->timeslots->getTimeslots(new DateTime($apiFrom), new DateTime($apiUntil));
        $this->assertEquals($expected, $result, $message);
    }

    /**
     * @return array
     */
    public function getTimeslotsProvider()
    {
        return array(
            array(
                '2017-09-25T16:38:00+00:00', array('09:00', '18:00'),
                '2017-09-25T16:40:00.672+00:00', '2017-09-25T18:00:01.000Z',
                array(),
                'shop closed'
            ),
            array(
                '2017-09-25T16:38:00+00:00', array('09:00', '18:00'),
                '2017-09-26T06:00:00.000Z', '2017-09-26T18:00:01.000Z',
                array(
                    array('2017-09-26T07:00:00+00:00', '2017-09-26T09:00:00+00:00'), // 09:00 local
                    array('2017-09-26T09:00:00+00:00', '2017-09-26T11:00:00+00:00'),
                    array('2017-09-26T11:00:00+00:00', '2017-09-26T13:00:00+00:00'),
                    array('2017-09-26T13:00:00+00:00', '2017-09-26T15:00:00+00:00'), // 15:00-17:00 local
                    array('2017-09-26T14:00:00+00:00', '2017-09-26T16:00:00+00:00'), // 16:00-18:00 local
                ),
                'next day available'
            ),
            array(
                '2017-09-25T16:38:00+00:00', array('09:00', '18:00'),
                '2017-09-24T16:40:00.672+00:00', '2017-09-24T18:00:01.000Z',
                array(),
                'passed dates not available (just in case)'
            ),
            array(
                '2017-09-28T13:59:00+00:00', array('09:00', '17:00'), // local time - 1+ hr to closing
                '2017-09-28T14:01:15.364+00:00', '2017-09-28T18:00:01.000Z', // API time - < 1 hr to closing
                array(),
                'clocks unsynced'
            ),
            array(
                '2017-09-28T14:41:00+00:00', array('09:00', '17:30'),
                '2017-09-28T14:42:00+00:00', '2017-09-28T23:00:01.000Z',
                array(),
                'half-hour?'
            ),
        );
    }
}
