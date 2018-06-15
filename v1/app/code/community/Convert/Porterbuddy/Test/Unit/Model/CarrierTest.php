<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Test_Unit_Model_CarrierTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Convert_Porterbuddy_Model_Carrier|PHPUnit_Framework_MockObject_MockObject
     */
    protected $carrier;

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

        $this->carrier = $this->getMockBuilder(Convert_Porterbuddy_Model_Carrier::class)
            ->setMethods(array('getNextOrder', 'getCurrentTime', 'getBaseCurrencyRate'))
            ->setConstructorArgs(array(
                'data' => null,
                'api' => null,
                'geocoder' => null,
                'helper' => $this->helper,
                'packager' => null,
                'timeslots' => null,
            ))
            ->getMock();
    }

    public function testAddAsapMethod()
    {
        $title = 'Porterbuddy';
        $asapName = 'Express Delivery';
        $this->helper
            ->method('getTitle')
            ->willReturn($title);
        $this->helper
            ->method('getAsapName')
            ->willReturn($asapName);
        $this->carrier
            ->method('getBaseCurrencyRate')
            ->willReturn(1.00);

        $request = new Mage_Shipping_Model_Rate_Request();
        $option = array(
            'delivery_type' => 'asap',
            'delivery_from' => null,
            'deliver_until' => null,
            'price' => '10,15 kr',
        );
        $result = new Convert_Porterbuddy_Model_Rate_Result();

        $output = $this->carrier->addAsapMethod($request, $option, $result);
        $methods = $output->getAllRates();

        $this->assertCount(1, $methods);
        $method = $methods[0];
        $this->assertEquals(array(
            'carrier' => 'cnvporterbuddy',
            'carrier_title' => $title,
            'method' => 'asap',
            'method_title' => $asapName,
            'price' => 10.15,
            'cost' => 10.15,
        ), $method->getData());
    }

    /**
     * @param string $discountType
     * @param float $discountSubtotal
     * @param float|null $discountAmount
     * @param float|null $discountPercent
     * @param float|null $cartSubtotal
     * @param array $beforeRates
     * @param array $expectedRates
     * @param string|null $message
     *
     * @dataProvider applyDiscountsProvider
     */
    public function testApplyDiscounts(
        $discountType,
        $discountSubtotal,
        $discountAmount,
        $discountPercent,
        $cartSubtotal,
        array $beforeRates,
        array $expectedRates,
        $message = null
    ) {
        $this->helper
            ->method('getDiscountType')
            ->willReturn($discountType);
        $this->helper
            ->method('getDiscountSubtotal')
            ->willReturn($discountSubtotal);
        $this->helper
            ->method('getDiscountAmount')
            ->willReturn($discountAmount);
        $this->helper
            ->method('getDiscountPercent')
            ->willReturn($discountPercent);

        $request = new Mage_Shipping_Model_Rate_Request();
        $request->setBaseSubtotalInclTax($cartSubtotal);
        $result = new Convert_Porterbuddy_Model_Rate_Result();
        foreach ($beforeRates as $price) {
            $method = new Mage_Shipping_Model_Rate_Result_Method(array(
                'price' => $price,
                'cost' => $price,
            ));
            $result->append($method);
        }

        $output = $this->carrier->applyDiscounts($request, $result);

        $this->assertSame($output, $result, $message);
        $methods = $result->getAllRates();
        $this->assertCount(count($expectedRates), $methods, $message);

        foreach ($methods as $i => $method) {
            // price might get lower
            $this->assertEquals($expectedRates[$i], $method->getPrice(), $message);
            // cost should not change
            $this->assertEquals($beforeRates[$i], $method->getCost(), $message);
        }
    }

    public function applyDiscountsProvider()
    {
        return array(
            // fixed
            array(
                // discount config - type, cart subtotal, discount amount, discount percent
                'fixed', 1500.00, 99.00, null,
                // cart subtotal
                1499.00,
                // shipping rates before discount
                array(180.00, 149.00, 149.00),
                // shipping rates after discount
                array(180.00, 149.00, 149.00),
                'fixed discount - not applying'
            ),
            array(
                'fixed', 1500.00, -99.00, null,
                1499.00,
                array(180.00, 149.00, 149.00),
                array(180.00, 149.00, 149.00),
                'fixed discount - negative values are ignored'
            ),
            array(
                'fixed', 1500.00, 99.00, null,
                1500.00,
                array(180.00, 149.00, 149.00),
                array( 81.00,  50.00,  50.00),
                'fixed discount - border case'
            ),
            array(
                'fixed', 1500.00, 99.00, null,
                2500.00,
                array(180.00, 149.00, 149.00),
                array( 81.00,  50.00,  50.00),
                'fixed discount amount - normal case'
            ),
            array(
                'fixed', 1500.00, 150.00, null,
                2500.00,
                array(180.00, 149.00, 149.00),
                array( 30.00,   0.00,  0.00),
                'too big fixed discount - stay positive, free shipping'
            ),
            // percent
            array(
                'percent', 1500.00, null, 25,
                500.00,
                array(180.00, 149.00, 149.00),
                array(180.00, 149.00, 149.00),
                'discount percent - not applying'
            ),
            array(
                'percent', 1500.00, null, 25,
                1499.99,
                array(180.00, 149.00, 149.00),
                array(180.00, 149.00, 149.00),
                'discount percent - border case'
            ),
            array(
                'percent', 1500.00, null, 25,
                1500.00,
                array(180.00, 149.00, 149.00),
                array(135.00, 111.75, 111.75),
                'discount percent - normal case'
            ),
            array(
                'percent', 1500.00, null, 100,
                2500.00,
                array(180.00, 149.00, 149.00),
                array(  0.00,   0.00,   0.00),
                'discount percent - 100% is allowed'
            ),
            array(
                'percent', 1500.00, null, 150,
                2500.00,
                array(180.00, 149.00, 149.00),
                array(180.00, 149.00, 149.00),
                'discount percent - invalid discount percent is completely ignored'
            ),
            array(
                'percent', 1500.00, null, -10,
                2500.00,
                array(180.00, 149.00, 149.00),
                array(180.00, 149.00, 149.00),
                'discount percent - invalid discount percent is completely ignored'
            ),
            // discount disabled
            array(
                'none', 1500.00, 99, 25,
                2500.00,
                array(180.00, 149.00, 149.00),
                array(180.00, 149.00, 149.00),
                'discount disabled'
            ),
        );
    }
}
