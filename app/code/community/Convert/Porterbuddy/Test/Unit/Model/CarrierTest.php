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
        require_once realpath(__DIR__) . '/../bootstrap.php';

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
                'helper' => $this->helper,
                'packager' => null,
                'timeslots' => null,
            ))
            ->getMock();
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

    /**
     * @param string $message
     * @pram array $packages
     * @param array $defaults
     * @param array $attrs
     * @param array $products
     * @param array $expected
     * @throws Varien_Exception
     *
     * @dataProvider getVerificationsProvider
     */
    public function testGetVerifications($message, array $packages, array $defaults, array $attrs, array $products, array $expected)
    {
        // defaults
        $this->helper
            ->method('isRequireSignatureDefault')
            ->willReturn($defaults['requireSignature']);
        $this->helper
            ->method('getMinAgeCheckDefault')
            ->willReturn($defaults['minimumAgeCheck']);
        $this->helper
            ->method('isIdCheckDefault')
            ->willReturn($defaults['idCheck']);
        $this->helper
            ->method('isOnlyToRecipientDefault')
            ->willReturn($defaults['onlyToRecipient']);

        // attrs
        $this->helper
            ->method('getRequireSignatureAttr')
            ->willReturn($attrs['requireSignature']);
        $this->helper
            ->method('getMinAgeCheckAttr')
            ->willReturn($attrs['minimumAgeCheck']);
        $this->helper
            ->method('getIdCheckAttr')
            ->willReturn($attrs['idCheck']);
        $this->helper
            ->method('getOnlyToRecipientAttr')
            ->willReturn($attrs['onlyToRecipient']);

        $shipmentItems = array();
        foreach ($products as $productData) {
            $product = $this->getMockBuilder(Mage_Sales_Model_Order::class)
                ->setConstructorArgs(array('data' => $productData))
                ->setMethodsExcept(array('hasData', 'getData'))
                ->getMock();

            $orderItem = $this->getMockBuilder(Mage_Sales_Model_Order_Item::class)
                ->disableOriginalConstructor()
                ->getMock();
            $orderItem
                ->method('getProduct')
                ->willReturn($product);

            $shipmentItem = $this->getMockBuilder(Mage_Sales_Model_Order_Shipment_Item::class)
                ->disableOriginalConstructor()
                ->getMock();
            $shipmentItem
                ->method('getOrderItem')
                ->willreturn($orderItem);

            $shipmentItems[] = $shipmentItem;
        }

        $order = $this->getMockBuilder(Mage_Sales_Model_Order::class)
            ->setConstructorArgs(array('data' => array('pb_leave_doorstep' => false))) // TODO: test
            ->setMethods(array('getAllItems'))
            ->getMock();

        /** @var Mage_Sales_Model_Order_Shipment|PHPUnit_Framework_MockObject_MockObject $shipment */
        $shipment = $this->getMockBuilder(Mage_Sales_Model_Order_Shipment::class)
            ->setConstructorArgs(array('data' => array('packages' => serialize($packages))))
            ->setMethods(array('getOrder', 'getAllItems'))
            ->getMock();
        $shipment
            ->method('getOrder')
            ->willReturn($order);
        $shipment
            ->method('getAllItems')
            ->willReturn($shipmentItems);

        $result = $this->carrier->getVerifications($shipment);
        $this->assertEquals($expected, $result, $message);
    }

    public function getVerificationsProvider()
    {
        return array(
            array(
                // message
                'Product attributes are not mapped, use defaults',
                // packages,
                array(),
                // defaults
                array(
                    'requireSignature' => true,
                    'minimumAgeCheck' => 18,
                    'idCheck' => true,
                    'onlyToRecipient' => true,
                ),
                // product attribute mapping
                array(
                    'requireSignature' => null,
                    'minimumAgeCheck' => null,
                    'idCheck' => null,
                    'onlyToRecipient' => null,
                ),
                // products
                array(
                    array('some' => 'value'),
                    array('some' => 'thing'),
                ),
                // expected
                array(
                    'leaveAtDoorstep' => false,
                    'requireSignature' => true,
                    'minimumAgeCheck' => 18,
                    'idCheck' => true,
                    'onlyToRecipient' => true,
                ),
            ),
            array(
                // message
                'Required items are always present even if false',
                // packages,
                array(),
                // defaults
                array(
                    'requireSignature' => false,
                    'minimumAgeCheck' => null,
                    'idCheck' => false,
                    'onlyToRecipient' => false,
                ),
                // product attribute mapping
                array(
                    'requireSignature' => 'sign',
                    'minimumAgeCheck' => 'age',
                    'idCheck' => 'idchk',
                    'onlyToRecipient' => 'rcp',
                ),
                // products
                array(
                    array('sign' => false, 'age' => null, 'idchk' => false, 'rcp' => false),
                    array('sign' => false, 'age' => null, 'idchk' => false, 'rcp' => false),
                ),
                // expected
                array(
                    'leaveAtDoorstep' => false,
                    'requireSignature' => false,
                    'idCheck' => false,
                    'onlyToRecipient' => false,
                ),
            ),
            array(
                // message
                'Default true requirements always win',
                // packages,
                array(),
                // defaults
                array(
                    'requireSignature' => true,
                    'minimumAgeCheck' => 16,
                    'idCheck' => true,
                    'onlyToRecipient' => true,
                ),
                // product attribute mapping
                array(
                    'requireSignature' => 'sign',
                    'minimumAgeCheck' => 'age',
                    'idCheck' => 'idchk',
                    'onlyToRecipient' => 'rcp',
                ),
                // products
                array(
                    array('sign' => false, 'age' => null, 'idchk' => false, 'rcp' => false),
                    array('sign' => false, 'age' => null, 'idchk' => false, 'rcp' => false),
                ),
                // expected
                'expected' => array(
                    'leaveAtDoorstep' => false,
                    'requireSignature' => true,
                    'minimumAgeCheck' => 16,
                    'idCheck' => true,
                    'onlyToRecipient' => true,
                ),
            ),
            array(
                // message
                'Defaults not require verifications, verification is set from some products',
                // packages,
                array(),
                // defaults
                array(
                    'requireSignature' => false,
                    'minimumAgeCheck' => null,
                    'idCheck' => false,
                    'onlyToRecipient' => false,
                ),
                // product attribute mapping
                array(
                    'requireSignature' => 'sign',
                    'minimumAgeCheck' => 'age',
                    'idCheck' => 'idchk',
                    'onlyToRecipient' => 'rcp',
                ),
                // products
                array(
                    array('sign' => true, 'age' => null, 'idchk' => false, 'rcp' => false),
                    array('sign' => false, 'age' => 16, 'idchk' => true, 'rcp' => false),
                ),
                // expected
                array(
                    'leaveAtDoorstep' => false,
                    'requireSignature' => true,
                    'minimumAgeCheck' => 16,
                    'idCheck' => true,
                    'onlyToRecipient' => false,
                ),
            ),
            array(
                // message
                'Default min age',
                // packages,
                array(),
                // defaults
                array(
                    'requireSignature' => false,
                    'minimumAgeCheck' => 16,
                    'idCheck' => false,
                    'onlyToRecipient' => false,
                ),
                // product attribute mapping
                array(
                    'requireSignature' => 'sign',
                    'minimumAgeCheck' => 'age',
                    'idCheck' => 'idchk',
                    'onlyToRecipient' => 'rcp',
                ),
                // products
                array(
                    array('sign' => true, 'age' => null, 'idchk' => false, 'rcp' => false),
                    array('sign' => false, 'age' => null, 'idchk' => true, 'rcp' => false),
                ),
                // expected
                array(
                    'leaveAtDoorstep' => false,
                    'requireSignature' => true,
                    'minimumAgeCheck' => 16,
                    'idCheck' => true,
                    'onlyToRecipient' => false,
                ),
            ),
            array(
                // message
                'Min age smallest from products',
                // packages,
                array(),
                // defaults
                array(
                    'requireSignature' => false,
                    'minimumAgeCheck' => null,
                    'idCheck' => false,
                    'onlyToRecipient' => false,
                ),
                // product attribute mapping
                array(
                    'requireSignature' => 'sign',
                    'minimumAgeCheck' => 'age',
                    'idCheck' => 'idchk',
                    'onlyToRecipient' => 'rcp',
                ),
                // products
                array(
                    array('sign' => true, 'age' => 12, 'idchk' => false, 'rcp' => false),
                    array('sign' => false, 'age' => 16, 'idchk' => true, 'rcp' => false),
                ),
                // expected
                array(
                    'leaveAtDoorstep' => false,
                    'requireSignature' => true,
                    'minimumAgeCheck' => 12,
                    'idCheck' => true,
                    'onlyToRecipient' => false,
                ),
            ),
            array(
                // message
                'Signature confirmation - explicitly disabled in packages wins even if enabled by default',
                // packages,
                array(
                    array(
                        'params' => array('delivery_confirmation' => 0),
                    ),
                    array(
                        'params' => array('delivery_confirmation' => 0),
                    ),
                ),
                // defaults
                array(
                    'requireSignature' => true,
                    'minimumAgeCheck' => null,
                    'idCheck' => false,
                    'onlyToRecipient' => false,
                ),
                // product attribute mapping
                array(
                    'requireSignature' => null,
                    'minimumAgeCheck' => null,
                    'idCheck' => null,
                    'onlyToRecipient' => null,
                ),
                // products
                array(
                    array(/*whatever*/),
                    array(/*whatever*/),
                ),
                // expected
                'expected' => array(
                    'leaveAtDoorstep' => false,
                    'requireSignature' => false,
                    'idCheck' => false,
                    'onlyToRecipient' => false,
                ),
            ),
            array(
                // message
                'Signature confirmation - required if at least one package requires',
                // packages,
                array(
                    array(
                        'params' => array('delivery_confirmation' => 0),
                    ),
                    array(
                        'params' => array('delivery_confirmation' => 1),
                    ),
                ),
                // defaults
                array(
                    'requireSignature' => true,
                    'minimumAgeCheck' => null,
                    'idCheck' => false,
                    'onlyToRecipient' => false,
                ),
                // product attribute mapping
                array(
                    'requireSignature' => null,
                    'minimumAgeCheck' => null,
                    'idCheck' => null,
                    'onlyToRecipient' => null,
                ),
                // products
                array(
                    array(/*whatever*/),
                    array(/*whatever*/),
                ),
                // expected
                'expected' => array(
                    'leaveAtDoorstep' => false,
                    'requireSignature' => true,
                    'idCheck' => false,
                    'onlyToRecipient' => false,
                ),
            ),
        );
    }
}
