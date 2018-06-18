<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Test_Unit_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once realpath(__DIR__) . '/../bootstrap.php';
    }

    /**
     * @dataProvider parseMethodProvider
     */
    public function testParseMethod($methodCode, $type, $start, $end, $return)
    {
        $helper = new Convert_Porterbuddy_Helper_Data();

        $this->assertEquals(
            array(
                'type' => $type,
                'start' => $start,
                'end' => $end,
                'return' => $return,
            ),
            $helper->parseMethod($methodCode)
        );
    }

    public function parseMethodProvider()
    {
        // input, type, date, timeslotLength, return
        return array(
            array('cnvporterbuddy_express_2018-05-11T11:00:00+00:00_2018-05-11T13:00:00+00:00', 'express', '2018-05-11T11:00:00+00:00', '2018-05-11T13:00:00+00:00', false),
            array('express_2018-05-11T11:00:00+00:00_2018-05-11T13:00:00+00:00', 'express', '2018-05-11T11:00:00+00:00', '2018-05-11T13:00:00+00:00', false),
            array('cnvporterbuddy_delivery_2018-05-11T11:00:00+00:00_2018-05-11T13:00:00+00:00', 'delivery', '2018-05-11T11:00:00+00:00', '2018-05-11T13:00:00+00:00', false),
            array('delivery_2018-05-11T11:00:00+00:00_2018-05-11T13:00:00+00:00', 'delivery', '2018-05-11T11:00:00+00:00', '2018-05-11T13:00:00+00:00', false),
            // returns
            array('cnvporterbuddy_express_2018-05-11T13:00:00+00:00_2018-05-11T15:00:00+00:00_return', 'express', '2018-05-11T13:00:00+00:00', '2018-05-11T15:00:00+00:00', true),
            array('express_2018-05-11T13:00:00+00:00_2018-05-11T15:00:00+00:00_return', 'express', '2018-05-11T13:00:00+00:00', '2018-05-11T15:00:00+00:00', true),
            array('cnvporterbuddy_delivery_2018-05-11T13:00:00+00:00_2018-05-11T15:00:00+00:00_return', 'delivery', '2018-05-11T13:00:00+00:00', '2018-05-11T15:00:00+00:00', true),
            array('delivery_2018-05-11T13:00:00+00:00_2018-05-11T15:00:00+00:00_return', 'delivery', '2018-05-11T13:00:00+00:00', '2018-05-11T15:00:00+00:00', true),
            // select delivery time later
            array('delivery', 'delivery', null, null, false),
            array('delivery_return', 'delivery', null, null, true),
        );
    }

    public function testSplitPhoneCodeNumber()
    {
        $helper = new Convert_Porterbuddy_Helper_Data();

        $phone = '';
        $this->assertEquals(
            array('', ''),
            $helper->splitPhoneCodeNumber($phone),
            'Empty number'
        );

        $phone = '40123456';
        $this->assertEquals(
            array('', '40123456'),
            $helper->splitPhoneCodeNumber($phone),
            'Number without postcode'
        );

        $phone = '+47 22 86 24 00';
        $this->assertEquals(
            array('+47', '22862400'),
            $helper->splitPhoneCodeNumber($phone),
            'Norwegian postcode'
        );

        $phone = '+46 40 10 16 20';
        $this->assertEquals(
            array('+46', '40101620'),
            $helper->splitPhoneCodeNumber($phone),
            'Swedish postcode'
        );
    }
}
