<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Test_Unit_Model_AvailabilityTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Convert_Porterbuddy_Model_Timeslots
     */
    protected $timeslots;

    /**
     * @var Convert_Porterbuddy_Model_Availability
     */
    protected $availability;

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
        $this->availability = new Convert_Porterbuddy_Model_Availability(
            null,
            null,
            null,
            $this->helper,
            $this->timeslots
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


        $result = $this->availability->getAvailableUntil();
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
