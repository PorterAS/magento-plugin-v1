<?php

class Convert_Porterbuddy_Model_Packager_Peritem implements Convert_Porterbuddy_Model_Packager_PackagerInterface
{
    const MODE = 'per_item';

    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @param Convert_Porterbuddy_Helper_Package $packageHelper optional
     */
    public function __construct(
        $data = null,
        Convert_Porterbuddy_Helper_Data $helper = null
    ) {
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');
    }

    public function estimateParcels(Mage_Shipping_Model_Rate_Request $request)
    {
        $parcels = array();
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($request->getAllItems() as $item) {
            if (!$this->canShip($item)) {
                continue;
            }
            $product = $item->getProduct();
            $dimensions = $this->getDimensions($product);

            $qty = $item->getQty();
            if ($item->getParentItem() && $item->getParentItem()->getQty() > 0) {
                $qty *= $item->getParentItem()->getQty();
            }

            $weight = $this->helper->convertWeightToGrams(
                $item->getWeight() ?: $this->helper->getDefaultProductWeight()
            );
            for ($i = 0; $i < $qty; $i++) {
                $parcels[] = array(
                    'description' => $item->getName(),
                    'widthCm' => $dimensions['width'],
                    'heightCm' => $dimensions['height'],
                    'depthCm' => $dimensions['length'],
                    'weightGrams' => $weight,
                );
            }
        }

        $transport = new Varien_Object(array('parcels' => $parcels));
        Mage::dispatchEvent('convert_porterbuddy_estimate_parcels_per_item', array(
            'transport' => $transport,
            'request' => $request,
        ));
        $parcels = $transport->getData('parcels');

        return $parcels;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $item
     * @return bool
     *
     * @see \Mage_Sales_Model_Service_Order::_canShipItem
     * @see \Mage_Sales_Model_Order_Item::isDummy
     */
    protected function canShip(Mage_Sales_Model_Quote_Item $item)
    {
        $result = true;

        if ($item->getIsVirtual()) {
            $result = false;
        }

        $hasChildren = count($item->getChildren()) > 0;
        $isShipSeparately = $item->isShipSeparately();
        $hasParent = (bool)$item->getParentItem();

        // TODO: more beautiful and extensible
        if (Mage_Catalog_Model_Product_Type::TYPE_BUNDLE == $item->getProductType()) {
            // always use children
            $result = false;
        }

        if ($hasChildren && $isShipSeparately) {
            // ship separately - skip parent, ship children
            $result = false;
        }

        // TODO: more beautiful and extensible
        if ($hasParent && !$isShipSeparately && Mage_Catalog_Model_Product_Type::TYPE_BUNDLE !== $item->getParentItem()->getProductType()) {
            // Magento default - ship parent, skip children
            $result = false;
        }

        $transport = new Varien_Object(array('result' => $result));
        Mage::dispatchEvent('convert_porterbuddy_estimate_parcels_per_item_can_ship', array(
            'transport' => $transport,
            'quote_item' => $item,
        ));
        $result = $transport->getData('result');

        return $result;
    }

    /**
     * Creates single big package out of all items
     *
     * {@inheritdoc}
     */
    public function createPackages(Mage_Shipping_Model_Shipment_Request $request)
    {
        $packages = array();
        /** @var Mage_Sales_Model_Order_Shipment_Item $item */
        foreach ($request->getOrderShipment()->getAllItems() as $item) {
            $product = $item->getOrderItem()->getProduct();

            $parent = $item->getOrderItem()->getParentItem();
            if ($parent && Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE == $parent->getProductType()) {
                // skip child of configurable, all info in parent
                continue;
            }
            if (Mage_Catalog_Model_Product_Type::TYPE_BUNDLE == $item->getOrderItem()->getProductType()) {
                // skip parent bundle item, bundle children items are following
                continue;
            }

            $dimensions = $this->getDimensions($product);

            $weight = $this->helper->convertWeightToGrams(
                $item->getWeight() ?: $this->helper->getDefaultProductWeight()
            );
            $package = array(
                'params' => array(
                    'container' => '',
                    'weight' => $weight,
                    'weight_unit' => Convert_Porterbuddy_Model_Carrier::WEIGHT_GRAM,
                    'customs_value' => $item->getPrice(),
                    'length' => $dimensions['length'],
                    'width' => $dimensions['width'],
                    'height' => $dimensions['height'],
                    'dimension_units' => Convert_Porterbuddy_Model_Carrier::UNIT_CENTIMETER,
                    // send description via standard fields
                    'content_type' => 'OTHER',
                    'content_type_other' => $item->getName(),
                ),
                'customs_value' => $item->getPrice(),
                'items' => array(
                    $item->getOrderItemId() => array(
                        'qty' => $item->getQty(),
                        'customs_value' => $item->getPrice(),
                        'price' => $item->getPrice(),
                        'name' => $item->getName(),
                        'weight' => $item->getWeight(),
                        'product_id' => $item->getProductId(),
                        'order_item_id' => $item->getOrderItemId(),
                    )
                ),
            );

            $qty = $item->getQty();
            for ($i = 0; $i < $qty; $i++) {
                $packages[] = $package;
            }
        }

        $transport = new Varien_Object(array('packages' => $packages));
        Mage::dispatchEvent('convert_porterbuddy_prepare_create_packages_per_item', array(
            'transport' => $transport,
            'request' => $request,
        ));
        $packages = $transport->getData('packages');

        return $packages;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param bool $useDefaults
     * @return array [width, length, height]
     */
    public function getDimensions(Mage_Catalog_Model_Product $product, $useDefaults = true)
    {
        $_product = Mage::getModel('catalog/product')->load($product->getId());
        $definedDimensions = array(
            'width' => $this->getAttributeValue($_product, $this->helper->getWidthAttribute()),
            'length' => $this->getAttributeValue($_product, $this->helper->getLengthAttribute()),
            'height' => $this->getAttributeValue($_product, $this->helper->getHeightAttribute()),
        );

        $definedDimensions = array_filter($definedDimensions);

        if ($useDefaults) {
            $definedDimensions += array(
                'width' => $this->helper->getDefaultProductWidth(),
                'length' => $this->helper->getDefaultProductLength(),
                'height' => $this->helper->getDefaultProductHeight(),
            );
        }

        return $definedDimensions;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param string $attribute
     * @return bool|string
     */
    protected function getAttributeValue(Mage_Catalog_Model_Product $product, $attribute)
    {
        if ($attribute
            && $product->hasData($attribute)
            && strlen($product->getData($attribute))
            && is_numeric($product->getData($attribute))
        ) {
            return $product->getData($attribute);
        } else {
            return false;
        }
    }
}
