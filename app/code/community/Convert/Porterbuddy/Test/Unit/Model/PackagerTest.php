<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Test_Unit_Model_PackagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Convert_Porterbuddy_Helper_Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    protected $containers = array(
        // 3 kg, 30x30x30 cm
        '0' => array (
            'code' => '0',
            'name' => 'Small',
            'weight' => '3',
            'length' => '30',
            'width' => '30',
            'height' => '30',
        ),
        // 6 kg, 60x45x45 cm
        '1' => array (
            'code' => '1',
            'name' => 'Medium',
            'weight' => '6',
            'length' => '60',
            'width' => '45',
            'height' => '45',
        ),
        // 21 kg, 120x60x60 cm
        '2' => array (
            'code' => '2',
            'name' => 'Big',
            'weight' => '21',
            'length' => '120',
            'width' => '60',
            'height' => '60',
        ),
        // 100 kg, 200x200x200 cm
        '3' => array (
            'code' => '3',
            'name' => 'Special',
            'weight' => '100',
            'length' => '200',
            'width' => '200',
            'height' => '200',
        ),
    );

    /**
     * @var Convert_Porterbuddy_Model_Packager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $packager;

    public function setUp()
    {
        // FIXME: bootstrap file
        require_once realpath(__DIR__) . '/../../../../../../../../../../../app/Mage.php';
        Mage::app();

        $this->helper = $this->getMockBuilder(Convert_Porterbuddy_Helper_Data::class)
            ->setMethodsExcept(['__', 'convertWeightToKg', 'convertDimensionToCm'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper
            ->method('getDefaultProductWeight')
            ->willReturn(2);
        $this->helper
            ->method('getDefaultProductWidth')
            ->willReturn(20);
        $this->helper
            ->method('getDefaultProductLength')
            ->willReturn(40);
        $this->helper
            ->method('getDefaultProductHeight')
            ->willReturn(30);
        $this->helper
            ->method('getWidthAttribute')
            ->willReturn('width');
        $this->helper
            ->method('getLengthAttribute')
            ->willReturn('length');
        $this->helper
            ->method('getHeightAttribute')
            ->willReturn('height');

        $this->packager = new Convert_Porterbuddy_Model_Packager(null, $this->helper);

        $this->packager = $this->getMockBuilder(Convert_Porterbuddy_Model_Packager::class)
            ->setMethods(array('getDeliverableProducts'))
            ->setConstructorArgs(array(
                'data' => null,
                'helper' => $this->helper,
            ))
            ->getMock();
    }

    /**
     * @dataProvider calculateContainerSizeProvider
     */
    public function testCalculateContainerSizeReverse($weight, $volume, $expectedContainer, $message = null)
    {
        $this->helper
            ->method('getContainers')
            ->willReturn($this->containers);

        $request = new Mage_Shipping_Model_Shipment_Request();

        $request->setPackageWeight($weight); // kg
        $request->setPackageVolume($volume); // cm3

        $shipment = new Mage_Sales_Model_Order_Shipment();
        $request->setOrderShipment($shipment);

        if (false === $expectedContainer) {
            $this->expectException(Convert_Porterbuddy_Exception::class);
        }

        $container = $this->packager->calculateContainerSize($request);
        $this->assertEquals(
            $expectedContainer,
            $container['code'],
            $message
        );
    }

    /**
     * Containers are set in different order - algorithm should not change
     *
     * @param $weight
     * @param $volume
     * @param $expectedContainer
     * @param null $message
     * @dataProvider calculateContainerSizeProvider
     */
    public function testCalculateContainerSize($weight, $volume, $expectedContainer, $message = null)
    {
        $this->helper
            ->method('getContainers')
            ->willReturn(array_reverse($this->containers, true));

        $request = new Mage_Shipping_Model_Shipment_Request();

        $request->setPackageWeight($weight); // kg
        $request->setPackageVolume($volume); // cm3

        $shipment = new Mage_Sales_Model_Order_Shipment();
        $request->setOrderShipment($shipment);

        if (false === $expectedContainer) {
            $this->expectException(Convert_Porterbuddy_Exception::class);
        }

        $container = $this->packager->calculateContainerSize($request);
        $this->assertEquals(
            $expectedContainer,
            $container['code'],
            $message
        );
    }

    /**
     * Data provider: weight, volume, container|false for exception
     *
     * @return array
     */
    public function calculateContainerSizeProvider()
    {
        return array(
            array(1, 10*10*20, '0', 'small box'),
            array(3, 10*10*20, '0', 'small box 3 kg should fit in small container'),
            array(3, 30*30*30, '0', 'small box with border weight and size should fit in small contianer'),
            array(3, 10*30*31, '0', 'slight exceeding over restriction should be allowed within total volume'),
            array(3, 30*30*31, '1', 'oversize by total volume - medium'),
            array(3.5, 30*30*30, '1', 'overweight - medium'),
            array(21, 10*20*30, '2', 'small and heavy - this could be gold'),
            array(10, 300*300*300, false, 'something super big - sorry can\' take it'),
            array(150, 10*20*30, false, 'something super heavy - sorry can\' take it'),
        );
    }

    /**
     * @param array $productsData
     * @param $expectedWeight
     * @dataProvider packageWeightProvider
     */
    public function testGetPackageWeight($expectedWeight /*, $product1,  $product2 ...*/)
    {
        $products = array();
        $productsData = func_get_args();
        array_shift($productsData); // expected weight
        foreach ($productsData as $data) {
            $products[] = new Mage_Catalog_Model_Product($data);
        }

        $this->packager
            ->method('getDeliverableProducts')
            ->willReturn($products);
        $this->helper
            ->method('getWeightUnit')
            ->willReturn('KILOGRAM');

        $shipment = new Mage_Sales_Model_Order_Shipment();

        $packageWeight = $this->packager->getPackageWeight($shipment);
        $this->assertEquals(
            $expectedWeight,
            $packageWeight
        );
    }

    /**
     * Data Provider: expected package weight, product data 1, product data 2, ...
     *
     * @return array
     */
    public function packageWeightProvider()
    {
        return array(
            // plain sum
            array(
                3.05,
                array('weight' => '1.05', 'qty' => 1),
                array('weight' => '2', 'qty' => 1),
            ),
            // check default is applied when explicitly empty and not loaded
            array(
                12,
                array('weight' => null, 'qty' => 1),
                array('weight' => '', 'qty' => 2),
                array('qty' => 3)
            ),
            // ensure explicit 0 weight is respected
            array(
                3.75,
                array('weight' => '3.75', 'qty' => 1),
                array('weight' => '0', 'qty' => 2),
            ),
        );
    }
}
