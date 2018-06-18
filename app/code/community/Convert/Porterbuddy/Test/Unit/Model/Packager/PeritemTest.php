<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Test_Unit_Model_Packager_PeritemTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Convert_Porterbuddy_Helper_Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var Convert_Porterbuddy_Model_Packager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $packager;

    public function setUp()
    {
        require_once realpath(__DIR__) . '/../../bootstrap.php';

        $this->helper = $this->getMockBuilder(Convert_Porterbuddy_Helper_Data::class)
            ->setMethodsExcept(['__', 'convertWeightToGrams', 'convertDimensionToCm'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper
            ->method('getDefaultProductWeight')
            ->willReturn(2);
        $this->helper
            ->method('getDefaultProductWidth')
            ->willReturn(20);
        $this->helper
            ->method('getDefaultProductHeight')
            ->willReturn(30);
        $this->helper
            ->method('getDefaultProductLength')
            ->willReturn(40);
        $this->helper
            ->method('getWidthAttribute')
            ->willReturn('width');
        $this->helper
            ->method('getLengthAttribute')
            ->willReturn('length');
        $this->helper
            ->method('getHeightAttribute')
            ->willReturn('height');
        $this->helper
            ->method('getWeightUnit')
            ->willReturn('KILOGRAM');
    }

    /**
     * Create packages with real live quote items scenarios
     *
     * @param array $quoteItems
     * @param array $expected
     * @param string $message
     * @dataProvider estimateParcelsDataProvider
     */
    public function testEstimateParcels($message, array $quoteItemsData, array $expected)
    {
        $quoteItems = array();
        // create quote items
        foreach ($quoteItemsData as $id => $quoteItemData) {
            $quoteItems[$id] = $this->createQuoteItem($quoteItemData);
        }
        // link parent-children
        foreach ($quoteItems as $id => $quoteItem) {
            if ($quoteItemsData[$id]['parent']) {
                $parentId = $quoteItemsData[$id]['parent'];
                $parentQuoteItem = $quoteItems[$parentId];
                $quoteItem
                    ->method('getParentItem')
                    ->willReturn($parentQuoteItem);
            }

            if ($quoteItemsData[$id]['children']) {
                $childrenQuoteItems = array();
                foreach ($quoteItemsData[$id]['children'] as $childId) {
                    $childrenQuoteItems[] = $quoteItems[$childId];
                }
                $quoteItem
                    ->method('getChildren')
                    ->willReturn($childrenQuoteItems);
            }
        }

        $request = new Mage_Shipping_Model_Rate_Request([
            'all_items' => $quoteItems,
        ]);

        $packager = new Convert_Porterbuddy_Model_Packager_Peritem(null, $this->helper);
        $result = $packager->estimateParcels($request);

        $this->assertCount(count($expected), $result, "Package count - $message");

        foreach ($expected as $i => $expectedPackage) {
            $this->assertArraySubset($expectedPackage, $result[$i], false, "Package #$i - $message");
        }
    }

    public function estimateParcelsDataProvider()
    {
        return array(
            array(
                'Default weight',
                // quote items
                array(
                    'item' => array(
                        'price' => '28.00',
                        'qty' => 1,
                        'name' => 'Body Wash with Lemon Flower Extract and Aloe Vera',
                        'sku' => 'hdb000',
                        'weight' => null,
                        'product_type' => 'simple',
                        'product' => array(
                            'type_id' => 'simple',
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'children' => null,
                        'parent' => null,
                    ),
                ),
                // expected
                array(
                    array(
                        'description' => 'Body Wash with Lemon Flower Extract and Aloe Vera',
                        'widthCm' => 50,
                        'heightCm' => 60,
                        'depthCm' => 70,
                        'weightGrams' => 2000,
                    ),
                )
            ),
            array(
                'Simple product',
                // quote items
                array(
                    'item' => array(
                        'price' => '28.00',
                        'qty' => 1,
                        'name' => 'Body Wash with Lemon Flower Extract and Aloe Vera',
                        'sku' => 'hdb000',
                        'weight' => '1.0',
                        'product_type' => 'simple',
                        'product' => array(
                            'type_id' => 'simple',
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'children' => null,
                        'parent' => null,
                    ),
                ),
                // expected
                array(
                    array(
                        'description' => 'Body Wash with Lemon Flower Extract and Aloe Vera',
                        'widthCm' => 50,
                        'heightCm' => 60,
                        'depthCm' => 70,
                        'weightGrams' => 1000,
                    ),
                )
            ),
            array(
                '2 same simple products',
                // quote items
                array(
                    'item' => array(
                        'price' => '28.00',
                        'qty' => 2,
                        'name' => 'Body Wash with Lemon Flower Extract and Aloe Vera',
                        'sku' => 'hdb000',
                        'weight' => '1.0',
                        'product_type' => 'simple',
                        'product' => array(
                            'type_id' => 'simple',
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'children' => null,
                        'parent' => null,
                    ),
                ),
                // expected
                array(
                    array(
                        'description' => 'Body Wash with Lemon Flower Extract and Aloe Vera',
                        'widthCm' => 50,
                        'heightCm' => 60,
                        'depthCm' => 70,
                        'weightGrams' => 1000,
                    ),
                    array(
                        'description' => 'Body Wash with Lemon Flower Extract and Aloe Vera',
                        'widthCm' => 50,
                        'heightCm' => 60,
                        'depthCm' => 70,
                        'weightGrams' => 1000,
                    ),
                )
            ),
            array(
                'Configurable product, dimensions in parent',
                // quote items
                array(
                    // parent item
                    'parent' => array(
                        'price' => '340.00',
                        'qty' => 1,
                        'name' => 'Lafayette Convertible Dress',
                        'sku' => 'wsd013',
                        'weight' => '1.0',
                        'product_type' => 'configurable',
                        'product' => array(
                            'type_id' => 'configurable',
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'children' => array('child'),
                        'parent' => null,
                    ),
                    // child item
                    'child' => array(
                        'price' => '0.00',
                        'qty' => 1,
                        'name' => 'Convertible Dress',
                        'sku' => 'wsd013',
                        'weight' => '1.0',
                        'product_type' => 'simple',
                        'product' => array(
                            'type_id' => 'simple',
                            'width' => null,
                            'height' => null,
                            'length' => null,
                        ),
                        'children' => null,
                        'parent' => 'parent',
                    ),
                ),
                // expected
                array(
                    array(
                        'description' => 'Lafayette Convertible Dress',
                        'widthCm' => 50,
                        'heightCm' => 60,
                        'depthCm' => 70,
                        'weightGrams' => 1000,
                    ),
                )
            ),
            array(
                'Configurable product, dimensions in child - this is not supported and defaults should apply',
                // quote items
                array(
                    // parent item
                    'parent' => array(
                        'price' => '340.00',
                        'qty' => 1,
                        'name' => 'Lafayette Convertible Dress',
                        'sku' => 'wsd013',
                        'weight' => '1.0',
                        'product_type' => 'configurable',
                        'product' => array(
                            'type_id' => 'configurable',
                            'width' => null,
                            'height' => null,
                            'length' => null,
                        ),
                        'children' => array('child'),
                        'parent' => null,
                    ),
                    // child item
                    'child' => array(
                        'price' => '0.00',
                        'qty' => 1,
                        'name' => 'Convertible Dress',
                        'sku' => 'wsd013',
                        'weight' => '1.0',
                        'product_type' => 'simple',
                        'product' => array(
                            'type_id' => 'simple',
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'children' => null,
                        'parent' => 'parent',
                    ),
                ),
                // expected
                array(
                    array(
                        'description' => 'Lafayette Convertible Dress',
                        'widthCm' => 20,
                        'heightCm' => 30,
                        'depthCm' => 40,
                        'weightGrams' => 1000,
                    ),
                )
            ),
            array(
                '2 same configurable products',
                // quote items
                array(
                    // parent item
                    'parent' => array(
                        'price' => '340.00',
                        'qty' => 2,
                        'name' => 'Lafayette Convertible Dress',
                        'sku' => 'wsd013',
                        'weight' => '1.0',
                        'product_type' => 'configurable',
                        'product' => array(
                            'type_id' => 'configurable',
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'children' => array('child'),
                        'parent' => null,
                    ),
                    // child item
                    'child' => array(
                        'price' => '0.00',
                        'qty' => 1,
                        'name' => 'Convertible Dress',
                        'sku' => 'wsd013',
                        'weight' => '1.0',
                        'product_type' => 'simple',
                        'product' => array(
                            'type_id' => 'simple',
                            'width' => null,
                            'height' => null,
                            'length' => null,
                        ),
                        'children' => null,
                        'parent' => 'parent',
                    ),
                ),
                // expected
                array(
                    array(
                        'description' => 'Lafayette Convertible Dress',
                        'widthCm' => 50,
                        'heightCm' => 60,
                        'depthCm' => 70,
                        'weightGrams' => 1000,
                    ),
                    array(
                        'description' => 'Lafayette Convertible Dress',
                        'widthCm' => 50,
                        'heightCm' => 60,
                        'depthCm' => 70,
                        'weightGrams' => 1000,
                    ),
                ),
            ),
            array(
                'Bundle product - send each component product',
                // quote items
                array(
                    // parent item
                    'parent' => array(
                        'price' => '400.00',
                        'qty' => 1,
                        'name' => 'Pillow and Throw Set',
                        'sku' => 'hdb010-hdb005-hdb009',
                        'weight' => '2.0',
                        'product_type' => 'bundle',
                        'product' => array(
                            'type_id' => 'bundle',
                            'width' => null,
                            'height' => null,
                            'length' => null,
                        ),
                        'children' => array('child_1', 'child_2'),
                        'parent' => null,
                    ),
                    // child item
                    'child_1' => array(
                        'price' => '125.00',
                        'qty' => 1,
                        'name' => 'Titian Raw Silk Pillow',
                        'sku' => 'hdb005',
                        'weight' => '1.0',
                        'product_type' => 'simple',
                        'product' => array(
                            'type_id' => 'simple',
                            'width' => 100,
                            'height' => 110,
                            'length' => 120,
                        ),
                        'children' => null,
                        'parent' => 'parent',
                    ),
                    'child_2' => array(
                        'price' => '275.00',
                        'qty' => 1,
                        'name' => 'Gramercy Throw',
                        'sku' => 'hdb009',
                        'weight' => '1.0',
                        'product_type' => 'simple',
                        'product' => array(
                            'type_id' => 'simple',
                            'width' => 90,
                            'height' => 60,
                            'length' => 90,
                        ),
                        'children' => null,
                        'parent' => 'parent',
                    ),
                ),
                // expected
                array(
                    array(
                        'description' => 'Titian Raw Silk Pillow',
                        'widthCm' => 100,
                        'heightCm' => 110,
                        'depthCm' => 120,
                        'weightGrams' => 1000,
                    ),
                    array(
                        'description' => 'Gramercy Throw',
                        'widthCm' => 90,
                        'heightCm' => 60,
                        'depthCm' => 90,
                        'weightGrams' => 1000,
                    ),
                ),
            ),
        );
    }

    /**
     * Create packages with real live shipment items scenarios
     *
     * @param string $message
     * @param array $shipmentItemsData
     * @param array $orderItemsData
     * @param array $expected
     * @dataProvider getDeliverableProductsShipmentDataProvider
     */
    public function testCreatePackages($message, array $shipmentItemsData, array $orderItemsData, array $expected)
    {
        // create order items
        $orderItems = array();
        foreach ($orderItemsData as $id => $orderItemData) {
            $orderItem = $this->createOrderItem($orderItemData);
            $orderItems[$id] = $orderItem;
        }
        // link order items parent-child
        foreach ($orderItems as $id => $orderItem) {
            if ($orderItemsData[$id]['parent']) {
                $parentId = $orderItemsData[$id]['parent'];
                $orderItem
                    ->method('getParentItem')
                    ->willReturn($orderItems[$parentId]);
            }

            if ($orderItemsData[$id]['children']) {
                $childItems = array();
                foreach ($orderItemsData[$id]['children'] as $childId) {
                    $childItems[] = $orderItems[$childId];
                }
                $orderItem
                    ->method('getChildrenItems')
                    ->willReturn($childItems);
            }
        }

        $shipmentItems = array();
        foreach ($shipmentItemsData as $shipmentItemData) {
            $shipmentItem = $this->getMockBuilder(Mage_Sales_Model_Order_Shipment_Item::class)
                ->setConstructorArgs(array('data' => $shipmentItemData))
                ->setMethods(['getOrderItem'])
                ->getMock();

            if ($shipmentItemData['order_item']) {
                $shipmentItem
                    ->method('getOrderItem')
                    ->willReturn($orderItems[$shipmentItemData['order_item']]);
                $shipmentItem->setOrderItemId($orderItems[$shipmentItemData['order_item']]->getData('item_id'));
            }

            $shipmentItems[] = $shipmentItem;
        }

        $shipment = $this->getMockBuilder(Mage_Sales_Model_Order_Shipment::class)
            ->getMock();
        $shipment
            ->method('getAllItems')
            ->willReturn($shipmentItems);

        $request = new Mage_Shipping_Model_Shipment_Request(array(
            'order_shipment' => $shipment,
        ));

        $packager = new Convert_Porterbuddy_Model_Packager_Peritem(null, $this->helper);
        $result = $packager->createPackages($request);

        $this->assertCount(count($expected), $result, "Package count - $message");

        foreach ($expected as $i => $expectedPackage) {
            $this->assertArraySubset($expectedPackage, $result[$i], false, "Package #$i - $message");
        }
    }

    public function getDeliverableProductsShipmentDataProvider()
    {
        return array(
            array(
                'Default weight',
                // shipment items
                array(
                    'shipment_item_1' => array(
                        'name' => 'Body Wash with Lemon Flower Extract and Aloe Vera',
                        'sku' => 'hdb000',
                        'qty' => 1,
                        'price' => '28.00',
                        'weight' => null,
                        'order_item' => 'order_item_simple',
                    ),
                ),
                // order items
                array(
                    'order_item_simple' => array(
                        'item_id' => 1,
                        'product_type' => 'simple',
                        'price' => '28.00',
                        'name' => 'Body Wash with Lemon Flower Extract and Aloe Vera',
                        'sku' => 'hdb000',
                        'product' => array(
                            'type_id' => 'simple',
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'parent' => null,
                        'children' => array(),
                    ),
                ),
                // expected
                array(
                    array(
                        'params' => array(
                            'weight' => 2000,
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'items' => array(
                            1 => array(
                                'name' => 'Body Wash with Lemon Flower Extract and Aloe Vera',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'Ship simple product',
                // shipment items
                array(
                    'shipment_item_1' => array(
                        'name' => 'Body Wash with Lemon Flower Extract and Aloe Vera',
                        'sku' => 'hdb000',
                        'qty' => 1,
                        'price' => '28.00',
                        'weight' => '1.0',
                        'order_item' => 'order_item_simple',
                    ),
                ),
                // order items
                array(
                    'order_item_simple' => array(
                        'item_id' => 1,
                        'product_type' => 'simple',
                        'price' => '28.00',
                        'name' => 'Body Wash with Lemon Flower Extract and Aloe Vera',
                        'sku' => 'hdb000',
                        'product' => array(
                            'type_id' => 'simple',
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'parent' => null,
                        'children' => array(),
                    ),
                ),
                // expected
                array(
                    array(
                        'params' => array(
                            'weight' => 1000,
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'items' => array(
                            1 => array(
                                'name' => 'Body Wash with Lemon Flower Extract and Aloe Vera',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'Ship configurable product, dimensions in parent',
                // shipment items
                array(
                    'shipment_item_1' => array(
                        'name' => 'Lafayette Convertible Dress',
                        'sku' => 'wsd013',
                        'qty' => 1,
                        'price' => '340.00',
                        'weight' => '1.0',
                        'order_item' => 'parent_order_item',
                    ),
                    'shipment_item_2' => array(
                        'name' => 'Convertible Dress',
                        'sku' => 'wsd013',
                        'qty' => 1,
                        'price' => '340.00',
                        'weight' => '1.0',
                        'order_item' => 'child_order_item',
                    ),
                ),
                // order items
                array(
                    'parent_order_item' => array(
                        'item_id' => 2,
                        'product_type' => 'configurable',
                        'sku' => 'wsd013', // parent configurable order item sku is copied from simple one
                        'name' => 'Lafayette Convertible Dress',
                        'product' => array(
                            'type_id' => 'configurable',
                            'sku' => 'wsd013c', // this is original product
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'parent' => null,
                        'children' => array('child_order_item'),
                    ),
                    'child_order_item' => array(
                        'item_id' => 3,
                        'product_type' => 'simple',
                        'sku' => 'wsd013',
                        'name' => 'Convertible Dress',
                        'product' => array(
                            'type_id' => 'simple',
                            'sku' => 'wsd013',
                            'width' => null,
                            'height' => null,
                            'length' => null,
                        ),
                        'parent' => 'parent_order_item',
                        'children' => array(),
                    ),
                ),
                // expected
                array(
                    array(
                        'params' => array(
                            'weight' => 1000,
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'items' => array(
                            2 => array(
                                'name' => 'Lafayette Convertible Dress',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'Ship configurable product, dimensions in child - defaults should apply',
                // shipment items
                array(
                    'shipment_item_1' => array(
                        'name' => 'Lafayette Convertible Dress',
                        'sku' => 'wsd013',
                        'qty' => 1,
                        'price' => '340.00',
                        'weight' => '1.0',
                        'order_item' => 'parent_order_item',
                    ),
                ),
                // order items
                array(
                    'parent_order_item' => array(
                        'item_id' => 4,
                        'product_type' => 'configurable',
                        'sku' => 'wsd013', // parent configurable order item sku is copied from simple one
                        'name' => 'Lafayette Convertible Dress',
                        'product' => array(
                            'type_id' => 'configurable',
                            'sku' => 'wsd013c', // this is original product
                            'width' => null,
                            'height' => null,
                            'length' => null,
                        ),
                        'parent' => null,
                        'children' => array('child_order_item'),
                    ),
                    'child_order_item' => array(
                        'item_id' => 5,
                        'product_type' => 'simple',
                        'sku' => 'wsd013',
                        'name' => 'Convertible Dress',
                        'product' => array(
                            'type_id' => 'simple',
                            'sku' => 'wsd013',
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'parent' => 'parent_order_item',
                        'children' => array(),
                    ),
                ),
                // expected
                array(
                    array(
                        'params' => array(
                            'weight' => 1000,
                            'width' => 20,
                            'height' => 30,
                            'length' => 40,
                        ),
                        'items' => array(
                            4 => array(
                                'name' => 'Lafayette Convertible Dress',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'Bundle, ship together',
                // shipment items
                array(
                    'parent_shipment_item' => array(
                        'name' => 'Pillow and Throw Set',
                        'sku' => 'hdb010-hdb005-hdb009',
                        'qty' => 1,
                        'price' => '400.00',
                        'weight' => '2.0',
                        'order_item' => 'parent_order_item',
                    ),
                    'child_shipment_item_1' => array(
                        'name' => 'Titian Raw Silk Pillow',
                        'sku' => 'hdb005',
                        'qty' => 1,
                        'price' => '125.00',
                        'weight' => '1.0',
                        'order_item' => 'child_order_item_1',
                    ),
                    'child_shipment_item_2' => array(
                        'name' => 'Gramercy Throw',
                        'sku' => 'hdb009',
                        'qty' => 1,
                        'price' => '275.00',
                        'weight' => '1.0',
                        'order_item' => 'child_order_item_2',
                    ),
                ),
                // order items
                array(
                    'parent_order_item' => array(
                        'item_id' => 6,
                        'product_type' => 'bundle',
                        'sku' => 'hdb010-hdb005-hdb009',
                        'name' => 'Pillow and Throw Set',
                        'product' => array(
                            'type_id' => 'bundle',
                            'sku' => 'hdb010',
                            'width' => null,
                            'height' => null,
                            'length' => null,
                        ),
                        'parent' => null,
                        'children' => array('child_order_item_1', 'child_order_item_2'),
                    ),
                    'child_order_item_1' => array(
                        'item_id' => 7,
                        'product_type' => 'simple',
                        'sku' => 'hdb005',
                        'name' => 'Titian Raw Silk Pillow',
                        'product' => array(
                            'type_id' => 'simple',
                            'sku' => 'hdb005',
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'parent' => 'parent_order_item',
                        'children' => array(),
                    ),
                    'child_order_item_2' => array(
                        'item_id' => 8,
                        'product_type' => 'simple',
                        'sku' => 'hdb009',
                        'name' => 'Gramercy Throw',
                        'product' => array(
                            'type_id' => 'simple',
                            'sku' => 'hdb009',
                            'width' => null,
                            'height' => null,
                            'length' => null,
                        ),
                        'parent' => 'parent_order_item',
                        'children' => array(),
                    ),
                ),
                // expected
                array(
                    array(
                        'params' => array(
                            'weight' => 1000,
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'items' => array(
                            7 => array(
                                'name' => 'Titian Raw Silk Pillow',
                            ),
                        ),
                    ),
                    array(
                        'params' => array(
                            'weight' => 1000,
                            // default sizes
                            'width' => 20,
                            'height' => 30,
                            'length' => 40,
                        ),
                        'items' => array(
                            8 => array(
                                'name' => 'Gramercy Throw',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'Bundle, 2 sets of (2 pillows and 1 throw) - should be 4 pillows and 2 throws',
                // shipment items
                array(
                    'parent_shipment_item' => array(
                        'name' => 'Pillow and Throw Set',
                        'sku' => 'hdb010-hdb005-hdb009',
                        'qty' => 2,
                        'price' => '400.00',
                        'weight' => '2.0',
                        'order_item' => 'parent_order_item',
                    ),
                    'child_shipment_item_1' => array(
                        'name' => 'Titian Raw Silk Pillow',
                        'sku' => 'hdb005',
                        'qty' => 4,
                        'price' => '125.00',
                        'weight' => '1.0',
                        'order_item' => 'child_order_item_1',
                    ),
                    'child_shipment_item_2' => array(
                        'name' => 'Gramercy Throw',
                        'sku' => 'hdb009',
                        'qty' => 2,
                        'price' => '275.00',
                        'weight' => '1.0',
                        'order_item' => 'child_order_item_2',
                    ),
                ),
                // order items
                array(
                    'parent_order_item' => array(
                        'item_id' => 9,
                        'product_type' => 'bundle',
                        'sku' => 'hdb010-hdb005-hdb009',
                        'name' => 'Pillow and Throw Set',
                        'product' => array(
                            'type_id' => 'bundle',
                            'sku' => 'hdb010',
                            'width' => null,
                            'height' => null,
                            'length' => null,
                        ),
                        'parent' => null,
                        'children' => array('child_order_item_1', 'child_order_item_2'),
                    ),
                    'child_order_item_1' => array(
                        'item_id' => 10,
                        'product_type' => 'simple',
                        'sku' => 'hdb005',
                        'name' => 'Titian Raw Silk Pillow',
                        'product' => array(
                            'type_id' => 'simple',
                            'sku' => 'hdb005',
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'parent' => 'parent_order_item',
                        'children' => array(),
                    ),
                    'child_order_item_2' => array(
                        'item_id' => 11,
                        'product_type' => 'simple',
                        'sku' => 'hdb009',
                        'name' => 'Gramercy Throw',
                        'product' => array(
                            'type_id' => 'simple',
                            'sku' => 'hdb009',
                            'width' => null,
                            'height' => null,
                            'length' => null,
                        ),
                        'parent' => 'parent_order_item',
                        'children' => array(),
                    ),
                ),
                // expected
                array(
                    array(
                        'params' => array(
                            'weight' => 1000,
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'items' => array(
                            10 => array(
                                'name' => 'Titian Raw Silk Pillow',
                            ),
                        ),
                    ),
                    array(
                        'params' => array(
                            'weight' => 1000,
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'items' => array(
                            10 => array(
                                'name' => 'Titian Raw Silk Pillow',
                            ),
                        ),
                    ),
                    array(
                        'params' => array(
                            'weight' => 1000,
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'items' => array(
                            10 => array(
                                'name' => 'Titian Raw Silk Pillow',
                            ),
                        ),
                    ),
                    array(
                        'params' => array(
                            'weight' => 1000,
                            'width' => 50,
                            'height' => 60,
                            'length' => 70,
                        ),
                        'items' => array(
                            10 => array(
                                'name' => 'Titian Raw Silk Pillow',
                            ),
                        ),
                    ),
                    array(
                        'params' => array(
                            'weight' => 1000,
                            // default sizes
                            'width' => 20,
                            'height' => 30,
                            'length' => 40,
                        ),
                        'items' => array(
                            11 => array(
                                'name' => 'Gramercy Throw',
                            ),
                        ),
                    ),
                    array(
                        'params' => array(
                            'weight' => 1000,
                            // default sizes
                            'width' => 20,
                            'height' => 30,
                            'length' => 40,
                        ),
                        'items' => array(
                            11 => array(
                                'name' => 'Gramercy Throw',
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @param array $data
     * @return PHPUnit_Framework_MockObject_MockObject|Mage_Sales_Model_Quote_Item
     */
    protected function createQuoteItem(array $data)
    {
        $quoteItem = $this->getMockBuilder(Mage_Sales_Model_Quote_Item::class)
            ->setConstructorArgs(array('data' => $data))
            ->setMethods(['getProduct', 'getParentItem', 'getChildren'])
            ->getMock();
        $quoteItem
            ->method('getProduct')
            ->willReturn(new Mage_Catalog_Model_Product($data['product']));

        return $quoteItem;
    }

    /**
     * @param array $data
     * @return PHPUnit_Framework_MockObject_MockObject|Mage_Sales_Model_Order_Item
     */
    protected function createOrderItem(array $data)
    {
        $orderItem = $this->getMockBuilder(Mage_Sales_Model_Order_Item::class)
            ->setConstructorArgs(array('data' => $data))
            ->setMethods(['getProduct', 'getParentItem', 'getChildrenItems'])
            ->getMock();
        $orderItem
            ->method('getProduct')
            ->willReturn(new Mage_Catalog_Model_Product($data['product']));

        return $orderItem;
    }
}
