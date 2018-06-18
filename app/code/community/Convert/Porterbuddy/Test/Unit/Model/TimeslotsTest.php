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

    protected $openHours = array(
        'mon' => array('open' => '09:00', 'close' => '18:00'), // 07:00-16:00 UTC
        'tue' => array('open' => '09:00', 'close' => '18:00'),
        'wed' => array('open' => '09:00', 'close' => '18:00'),
        'thu' => array('open' => '09:00', 'close' => '18:00'),
        'fri' => array('open' => '09:00', 'close' => '18:00'),
        'sat' => array('open' => '10:00', 'close' => '16:00'), // 08:00-14:00 UTC
        'sun' => false, // closed
    );

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        require_once realpath(__DIR__) . '/../bootstrap.php';

        $this->helper = $this->getMockBuilder(Convert_Porterbuddy_Helper_Data::class)
            ->setMethodsExcept(['formatApiDateTime'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper
            ->method('getTimezone')
            ->willReturn(new DateTimeZone('Europe/Warsaw'));
        $this->helper
            ->method('getExtraPickupWindows')
            ->willReturn(3);

        $this->timeslots = new Convert_Porterbuddy_Model_Timeslots(null, $this->helper);
    }

    /**
     * @param string $description
     * @param string $now
     * @param array $expected
     *
     * @dataProvider getAvailabilityPickupWindowsProvider
     */
    public function testGetAvailabilityPickupWindows($description, $now, array $expected)
    {
        $this->helper
            ->method('getCurrentTime')
            ->willReturnCallback(function() use ($now) {
                return new DateTime($now);
            });
        $this->helper
            ->method('getOpenHours')
            ->willReturnCallback(function($dayOfWeek) {
                return $this->openHours[$dayOfWeek];
            });
        $this->helper
            ->method('getPackingTime')
            ->willReturn(15);
        $this->helper
            ->method('getRefreshOptionsTimeout')
            ->willReturn(5);
        $this->helper
            ->method('getDaysAhead')
            ->willReturn(3);

        $result = $this->timeslots->getAvailabilityPickupWindows();
        $this->assertEquals(
            $result,
            $expected,
            $description
        );
    }

    public function getAvailabilityPickupWindowsProvider()
    {
        return array(
            array(
                'Morning',
                // now Wed 10:20 CET
                '2018-06-06T08:20:00+00:00',
                // expected
                array(
                    array('start' => '2018-06-06T10:40:00+02:00', 'end' => '2018-06-06T18:00:00+02:00'),
                    array('start' => '2018-06-07T09:00:00+02:00', 'end' => '2018-06-07T18:00:00+02:00'),
                    array('start' => '2018-06-08T09:00:00+02:00', 'end' => '2018-06-08T18:00:00+02:00'),
                ),
            ),
            array(
                'Availability, late evening, pickup next opening hour + packing time',
                // now Mon 21:23 CET
                '2018-06-04T19:23:00+00:00',
                // expected
                array(
                    // now closed
                    array('start' => '2018-06-05T09:20:00+02:00', 'end' => '2018-06-05T18:00:00+02:00'), // +3
                    array('start' => '2018-06-06T09:00:00+02:00', 'end' => '2018-06-06T18:00:00+02:00'),
                    array('start' => '2018-06-07T09:00:00+02:00', 'end' => '2018-06-07T18:00:00+02:00'),
                ),
            ),
            array(
                'Availability - order on Tue morning, deliver during the day, pickup today, total 3 days',
                // now Tue 11:00 CET
                '2018-05-08T09:00:00+00:00',
                // expected
                array(
                    array('start' => '2018-05-08T11:20:00+02:00', 'end' => '2018-05-08T18:00:00+02:00'), // Today
                    array('start' => '2018-05-09T09:00:00+02:00', 'end' => '2018-05-09T18:00:00+02:00'), // total 3
                    array('start' => '2018-05-10T09:00:00+02:00', 'end' => '2018-05-10T18:00:00+02:00'),
                ),
            ),
        );
    }

    /**
     * @param string $description
     * @param string $now
     * @param array $methodInfo
     * @param array $expected
     *
     * @dataProvider getPickupWindowsProvider
     */
    public function testGetPickupWindows($description, $now, $methodInfo, array $expected)
    {
        $this->helper
            ->method('getCurrentTime')
            ->willReturnCallback(function() use ($now) {
                return new DateTime($now);
            });
        $this->helper
            ->method('getOpenHours')
            ->willReturnCallback(function($dayOfWeek) {
                return $this->openHours[$dayOfWeek];
            });
        $this->helper
            ->method('getPackingTime')
            ->willReturn(15);

        $result = $this->timeslots->getPickupWindows($methodInfo);
        $this->assertEquals(
            $result,
            $expected,
            $description
        );
    }

    public function getPickupWindowsProvider()
    {
        return array(
            array(
                'Espresso in the morning',
                // now Wed 10:22 CET - 2 minutes after availability request (before options refresh)
                '2018-06-06T08:22:00+00:00',
                // method info
                array(
                    'type' => 'express',
                    'start' => '2018-06-06T10:40:00+02:00',
                    'end' => '2018-06-06T12:00:00+02:00',
                    'return' => false
                ),
                // expected
                array(
                    array('start' => '2018-06-06T10:37:00+02:00', 'end' => '2018-06-06T18:00:00+02:00'),
                    array('start' => '2018-06-07T09:00:00+02:00', 'end' => '2018-06-07T18:00:00+02:00'),
                    array('start' => '2018-06-08T09:00:00+02:00', 'end' => '2018-06-08T18:00:00+02:00'),
                    array('start' => '2018-06-09T10:00:00+02:00', 'end' => '2018-06-09T16:00:00+02:00'), // sat, short
                ),
            ),
            array(
                'Order late evening, pickup next opening hour + packing time',
                // now Mon 21:23 CET
                '2018-06-04T19:23:00+00:00',
                // method info
                array(
                    'type' => 'delivery',
                    'start' => '2018-06-05T15:00:00+02:00',
                    'end' => '2018-06-05T17:00:00+02:00',
                    'return' => false
                ),
                // expected
                array(
                    array('start' => '2018-06-05T09:15:00+02:00', 'end' => '2018-06-05T18:00:00+02:00'), // +3
                    array('start' => '2018-06-06T09:00:00+02:00', 'end' => '2018-06-06T18:00:00+02:00'),
                    array('start' => '2018-06-07T09:00:00+02:00', 'end' => '2018-06-07T18:00:00+02:00'),
                ),
            ),
            array(
                'Order now, pickup now + packing time',
                // now Tue 11:00 CET
                '2018-05-08T09:00:00+00:00',
                // method info
                array(
                    'type' => 'delivery',
                    'start' => '2018-05-08T13:00:00+02:00',
                    'end' => '2018-05-08T15:00:00+02:00',
                    'return' => false
                ),
                // expected
                array(
                    array('start' => '2018-05-08T11:15:00+02:00', 'end' => '2018-05-08T18:00:00+02:00'), // Today
                    array('start' => '2018-05-09T09:00:00+02:00', 'end' => '2018-05-09T18:00:00+02:00'), // +3
                    array('start' => '2018-05-10T09:00:00+02:00', 'end' => '2018-05-10T18:00:00+02:00'),
                    array('start' => '2018-05-11T09:00:00+02:00', 'end' => '2018-05-11T18:00:00+02:00'),
                ),
            ),
            array(
                'Delivery, select timeslot later',
                // now Tue 11:00 CET
                '2018-05-08T09:00:00+00:00',
                // method info
                array(
                    'type' => 'delivery',
                    'start' => null,
                    'end' => null,
                    'return' => false
                ),
                // expected
                array(
                    array('start' => '2018-05-08T11:15:00+02:00', 'end' => '2018-05-08T18:00:00+02:00'), // Today
                    array('start' => '2018-05-09T09:00:00+02:00', 'end' => '2018-05-09T18:00:00+02:00'), // +3
                    array('start' => '2018-05-10T09:00:00+02:00', 'end' => '2018-05-10T18:00:00+02:00'),
                ),
            ),
            array(
                'Order at night, pickup next opening hour + packing time',
                // now Tue 05:00 CET
                '2018-05-08T02:00:00+00:00',
                // method info
                array(
                    'type' => 'delivery',
                    'start' => '2018-05-08T13:00:00+02:00',
                    'end' => '2018-05-08T15:00:00+02:00',
                    'return' => false
                ),
                // expected
                array(
                    array('start' => '2018-05-08T09:15:00+02:00', 'end' => '2018-05-08T18:00:00+02:00'), // Today
                    array('start' => '2018-05-09T09:00:00+02:00', 'end' => '2018-05-09T18:00:00+02:00'), // +3
                    array('start' => '2018-05-10T09:00:00+02:00', 'end' => '2018-05-10T18:00:00+02:00'),
                    array('start' => '2018-05-11T09:00:00+02:00', 'end' => '2018-05-11T18:00:00+02:00'),
                ),
            ),
            array(
                'Order on Tue morning, deliver during the day, pickup today +3 days',
                // now Tue 11:00 CET
                '2018-05-08T09:00:00+00:00',
                // method info
                array(
                    'type' => 'delivery', // Tue 13:00-15:00 CET
                    'start' => '2018-05-08T13:00:00+02:00',
                    'end' => '2018-05-08T15:00:00+02:00',
                    'return' => false
                ),
                // expected
                array(
                    array('start' => '2018-05-08T11:15:00+02:00', 'end' => '2018-05-08T18:00:00+02:00'), // Today
                    array('start' => '2018-05-09T09:00:00+02:00', 'end' => '2018-05-09T18:00:00+02:00'), // +3
                    array('start' => '2018-05-10T09:00:00+02:00', 'end' => '2018-05-10T18:00:00+02:00'),
                    array('start' => '2018-05-11T09:00:00+02:00', 'end' => '2018-05-11T18:00:00+02:00'),
                ),
            ),
            array(
                'Order on Fri, deliver on Mon +3 days',
                // now Fri 11:00 CET
                '2018-05-11T09:00:00+00:00',
                // method info
                array(
                    'type' => 'delivery', // Mon 13:00-15:00 CET
                    'start' => '2018-05-14T13:00:00+02:00',
                    'end' => '2018-05-14T15:00:00+02:00',
                    'return' => false
                ),
                array(
                    array('start' => '2018-05-11T11:15:00+02:00', 'end' => '2018-05-11T18:00:00+02:00'), // Today
                    array('start' => '2018-05-12T10:00:00+02:00', 'end' => '2018-05-12T16:00:00+02:00'), // Sat
                    // Sun holiday
                    array('start' => '2018-05-14T09:00:00+02:00', 'end' => '2018-05-14T18:00:00+02:00'), // Mon
                    array('start' => '2018-05-15T09:00:00+02:00', 'end' => '2018-05-15T18:00:00+02:00'), // +3
                    array('start' => '2018-05-16T09:00:00+02:00', 'end' => '2018-05-16T18:00:00+02:00'),
                    array('start' => '2018-05-17T09:00:00+02:00', 'end' => '2018-05-17T18:00:00+02:00'),
                ),
            ),
            array(
                'Express delivery',
                // now Tue 10:30 CET
                '2018-05-08T08:30:00+00:00',
                // method info
                array(
                    'type' => 'express',
                    'start' => '2018-05-08T12:30:00+02:00',
                    'end' => '2018-05-08T14:30:00+02:00',
                    'return' => false
                ),
                // expected
                array(
                    array('start' => '2018-05-08T10:45:00+02:00', 'end' => '2018-05-08T18:00:00+02:00'), // Today
                    array('start' => '2018-05-09T09:00:00+02:00', 'end' => '2018-05-09T18:00:00+02:00'), // +3
                    array('start' => '2018-05-10T09:00:00+02:00', 'end' => '2018-05-10T18:00:00+02:00'),
                    array('start' => '2018-05-11T09:00:00+02:00', 'end' => '2018-05-11T18:00:00+02:00'),
                ),
            ),
        );
    }

    /**
     * @dataProvider getAvailableUntilDataProvider
     */
    public function testGetAvailableUntil($message, $now, $openHours, $porterbudyUntil, $expected)
    {
        $this->helper
            ->method('getCurrentTime')
            ->willReturn(new DateTime($now));
        $this->helper
            ->method('getPorterbuddyUntil')
            ->willReturn($porterbudyUntil);
        $this->helper
            ->method('getOpenHours')
            ->willReturnCallback(function($dayOfWeek) use ($openHours) {
                return $openHours[$dayOfWeek];
            });
        $expected = new DateTime($expected);


        $result = $this->timeslots->getAvailableUntil();
        $this->assertEquals($expected, $result, $message);
    }

    public function getAvailableUntilDataProvider()
    {
        return array(
            array(
                'Can be delivered today until 17:30',
                // now Tuesday 5 am CET
                '2018-05-24T03:00:00+00:00',
                $this->openHours,
                // porterbuddy until
                30,
                // expected
                '2018-05-24T17:30:00+02:00'
            ),
            array(
                'Can be delivered tomorrow until 17:30',
                // now Tuesday 18:00 CET
                '2018-05-24T16:00:00+00:00',
                $this->openHours,
                // porterbuddy until
                30,
                // expected
                '2018-05-25T17:30:00+02:00'
            ),
            array(
                'Can be delivered on Saturday until 15:30',
                // now Friday 22:00 CET
                '2018-05-25T20:00:00+00:00',
                $this->openHours,
                // porterbuddy until
                30,
                // expected
                '2018-05-26T15:30:00+02:00'
            ),
            array(
                'Non-working day is skipped, delivery on Monday until 17:30',
                // now Saturday 17:00 CET
                '2018-05-26T15:00:00+00:00',
                $this->openHours,
                // porterbuddy until
                30,
                // expected
                '2018-05-28T17:30:00+02:00'
            ),
        );
    }
}
