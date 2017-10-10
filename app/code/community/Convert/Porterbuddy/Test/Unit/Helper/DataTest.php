<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Test_Unit_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // FIXME: bootstrap file
        require_once realpath(__DIR__) . '/../../../../../../../../../../../app/Mage.php';
        Mage::app();
    }

    /**
     * @dataProvider parseMethodProvider
     */
    public function testParseMethod($methodCode, $type, $date, $timeslotLength, $return)
    {
        $helper = new Convert_Porterbuddy_Helper_Data();

        $this->assertEquals(
            array(
                'type' => $type,
                'date' => $date,
                'timeslotLength' => $timeslotLength,
                'return' => $return,
            ),
            $helper->parseMethod($methodCode)
        );
    }

    public function parseMethodProvider()
    {
        // input, type, date, timeslotLength, return
        return array(
            array('cnvporterbuddy_asap', 'asap', null, null, false),
            array('asap', 'asap', null, null, false),
            array('cnvporterbuddy_scheduled_2017-08-07T11:00:00+00:00_2', 'scheduled', '2017-08-07T11:00:00+00:00', 2, false),
            array('scheduled_2017-08-07T11:00:00+00:00_2', 'scheduled', '2017-08-07T11:00:00+00:00', 2, false),
            // returns
            array('cnvporterbuddy_asap_return', 'asap', null, null, true),
            array('asap_return', 'asap', null, null, true),
            array('cnvporterbuddy_scheduled_2017-08-07T11:00:00+00:00_2_return', 'scheduled', '2017-08-07T11:00:00+00:00', 2, true),
            array('scheduled_2017-08-07T11:00:00+00:00_2_return', 'scheduled', '2017-08-07T11:00:00+00:00', 2, true),
        );
    }

    public function testFormatAddress()
    {
        /** @var Mage_Sales_Model_Order_Address $shippingAddress */
        $shippingAddress = Mage::getSingleton('sales/order_address');
        $shippingAddress->setData(array(
            'postcode' => '0275',
            'street' => "HOVFARET 17B\r\nPorter AS",
            'city' => 'OSLO',
            'telephone' => '12 34 56 78',
            'country_id' => 'no',
            'lastname' => 'Andvik',
            'firstname' => 'Vegard',
            'email' => 'test@example.com',
            'street_type' => 'shipping',
            'company' => 'Company Ltd.',
        ));

        $helper = new Convert_Porterbuddy_Helper_Data();
        $this->assertEquals(
            'HOVFARET 17B, OSLO, 0275, Norge',
            $helper->formatAddress($shippingAddress)
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
