<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Packager
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @param array|null $data
     * @param Convert_Porterbuddy_Helper_Data|null $helper
     */
    public function __construct(
        array $data = null, // for getModel to work
        Convert_Porterbuddy_Helper_Data $helper = null
    ) {
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');
    }

    /**
     * Create package automatically if not assigned manually by admin
     *
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return $this
     * @throws Convert_Porterbuddy_Exception
     */
    public function createPackages(Mage_Shipping_Model_Shipment_Request $request)
    {
        $shipment = $request->getOrderShipment();

        // don't trust request->getPackageWeight as some products may have it empty and we need to apply default weight
        $request->setPackageWeight($this->getPackageWeight($shipment)); // kg
        $request->setPackageVolume($this->getPackageVolume($shipment)); // cm

        $container = $this->calculateContainerSize($request);

        $package = array(
            'params' => array(
                'container' => $container['code'],
                'weight' => $request->getPackageWeight(),
                'weight_unit' => Convert_Porterbuddy_Model_Carrier::WEIGHT_KILOGRAM,
                'customs_value' => 0.00,
                'length' => $container['length'],
                'width' => $container['width'],
                'height' => $container['height'],
                'dimension_units' => Convert_Porterbuddy_Model_Carrier::UNIT_CENTIMETER,
                'content_type' => '', // doesn't matter
                'content_type_other' => '',
            ),
            'items' => array(),
        );
        $totalPrice = 0.00;
        /** @var Mage_Sales_Model_Order_Shipment_Item $item */
        foreach ($shipment->getAllItems() as $item) {
            $package['items'][$item->getOrderItemId()] = array(
                'qty' => $item->getQty(),
                'customs_value' => $item->getPrice(),
                'price' => $item->getPrice(),
                'name' => $item->getName(),
                'weight' => $item->getWeight(),
                'product_id' => $item->getProductId(),
                'order_item_id' => $item->getOrderItemId(),
            );
            $totalPrice += $item->getPrice();
        }
        $package['customs_value'] = $totalPrice;

        $packages = array($package);

        $transport = new Varien_Object(array('packages' => $packages));
        Mage::dispatchEvent('convert_porterbuddy_prepare_create_packages', array(
            'transport' => $transport,
            'request' => $request,
        ));
        $packages = $transport->getData('packages');

        $shipment->setPackages($packages);

        return $this;
    }

    /**
     * Get product height
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getProductHeight(Mage_Catalog_Model_Product $product)
    {
        $heightAttribute = $this->helper->getHeightAttribute();
        $defaultHeight = $this->helper->getDefaultProductHeight();

        return $this->getDimensionValue($product, $heightAttribute, $defaultHeight);
    }

    /**
     * Get product width
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getProductWidth(Mage_Catalog_Model_Product $product)
    {
        $widthAttribute = $this->helper->getWidthAttribute();
        $defaultWidth = $this->helper->getDefaultProductWidth();
        return $this->getDimensionValue($product, $widthAttribute, $defaultWidth);
    }

    /**
     * Get product length
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getProductLength(Mage_Catalog_Model_Product $product)
    {
        $lengthAttribute = $this->helper->getLengthAttribute();
        $defaultLength = $this->helper->getDefaultProductLength();
        return $this->getDimensionValue($product, $lengthAttribute, $defaultLength);
    }

    /**
     * Get product dimension value (height, width or length)
     *
     * Returns default value if dimension attribute is not selected or value for Product is not set
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $attribute
     * @param float $default
     * @return float|string
     */
    public function getDimensionValue(Mage_Catalog_Model_Product $product, $attribute, $default = null)
    {
        if ($attribute
            && $product->hasData($attribute)
            && strlen($product->getData($attribute))
            && is_numeric($product->getData($attribute))
        ) {
            return $product->getData($attribute);
        } else {
            return $default;
        }
    }

    /**
     * Get product weight. Returns default weight if Product weight is not set
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getProductWeight(Mage_Catalog_Model_Product $product)
    {
        $defaultWeight = $this->helper->getDefaultProductWeight();
        return $this->getDimensionValue($product, 'weight', $defaultWeight);
    }

    /**
     * Get shipment Deliverable Products (their weight and dimensions influences on package container calculations)
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return Mage_Catalog_Model_Product[]
     */
    public function getDeliverableProducts(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $deliverableProducts = array();

        /** @var Mage_Sales_Model_Order_Shipment_Item $item */
        foreach ($shipment->getAllItems() as $item) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')
                ->setStoreId($shipment->getStoreId())
                ->load($item->getProductId());

            // don't calculate volume for bundle and virtual products
            if (!$product->getId()
                || $product->isVirtual()
                || Mage_Catalog_Model_Product_Type::TYPE_BUNDLE == $product->getTypeId()
            ) {
                continue;
            }

            //use simple product of configurable to calculate configurable volume and weight
            if (Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE == $product->getTypeId()) {
                $childrenItems = $item->getOrderItem()->getChildrenItems();
                /** @var Mage_Sales_Model_Order_Item|false $orderItem */
                $orderItem = reset($childrenItems);
                if ($orderItem && $orderItem->getProduct()) {
                    $product = $orderItem->getProduct();
                }
            }

            $product->setQty($item->getQty()); // custom place

            $deliverableProducts[] = $product;
        }

        return $deliverableProducts;
    }

    /**
     * Get total package Weight in kg
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return float
     */
    public function getPackageWeight(Mage_Sales_Model_Order_Shipment $shipment)
    {
        //return round($this->helper->convertWeightToKg((float)$request->getPackageWeight()), 2);
        // TODO: handle bundle products
        $deliverableProducts = $this->getDeliverableProducts($shipment);
        $packageWeight = 0.0;

        foreach($deliverableProducts as $product) {
            $weight = $this->helper->convertWeightToKg($this->getProductWeight($product));
            $packageWeight += round($weight * $product->getQty(), 2);
        }

        $transport = new Varien_Object(array('weight' => $packageWeight));
        Mage::dispatchEvent('convert_porterbuddy_get_package_weight', array(
            'transport' => $transport,
            'shipment' => $shipment,
        ));
        $packageWeight = $transport->getData('weight');

        return $packageWeight;
    }

    /**
     * Get total package volume in cm3
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return float
     */
    public function getPackageVolume(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $deliverableProducts = $this->getDeliverableProducts($shipment);
        $packageVolume = 0.0;

        foreach ($deliverableProducts as $product) {
            $height = $this->helper->convertDimensionToCm($this->getProductHeight($product));
            $width = $this->helper->convertDimensionToCm($this->getProductWidth($product));
            $length = $this->helper->convertDimensionToCm($this->getProductLength($product));

            $packageVolume += round($height * $width * $length * $product->getQty(), 2);
        }

        $transport = new Varien_Object(array('volume' => $packageVolume));
        Mage::dispatchEvent('convert_porterbuddy_get_package_volume', array(
            'transport' => $transport,
            'shipment' => $shipment,
        ));
        $packageVolume = $transport->getData('volume');

        return $packageVolume;
    }

    /**
     * Prepare Note for Courier
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return string
     */
    public function prepareCourierNote(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $packages = $shipment->getPackages();
        if ($packages && is_scalar($packages)) {
            $packages = unserialize($packages);
        }
        $package = new Varien_Object(reset($packages));

        $volume = $this->getPackageVolume($shipment);
        // weight either entered by admin manually, or saved when auto creating packages
        $weight = $this->helper->convertWeightToKg($package->getParams('weight'), $package->getParams('weight_unit'));

        return $this->helper->__('Weight %s kg, volume %s cm³', $weight, $volume);
    }

    /**
     * Calculate min container size for the shipment
     *
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return array
     * @throws Convert_Porterbuddy_Exception
     */
    public function calculateContainerSize(Mage_Shipping_Model_Shipment_Request $request)
    {
        if (!$this->helper->getDefaultProductWidth()) {
            throw new Convert_Porterbuddy_Exception(
                $this->helper->__('%s must be set.'), $this->helper->__('Default product width')
            );
        }
        if (!$this->helper->getDefaultProductLength()) {
            throw new Convert_Porterbuddy_Exception(
                $this->helper->__('%s must be set.'), $this->helper->__('Default product length')
            );
        }
        if (!$this->helper->getDefaultProductHeight()) {
            throw new Convert_Porterbuddy_Exception(
                $this->helper->__('%s must be set.'), $this->helper->__('Default product height')
            );
        }

        $shipment = $request->getOrderShipment();
        $packageWeight = $request->getPackageWeight();
        $packageVolume = $request->getPackageVolume();

        $minContainer = null;
        $minWeight = null;
        $minVolume = null;

        $containers = $this->helper->getContainers();
        foreach ($containers as $code => $container) {
            $weight = $container['weight'];
            $height = $container['height'];
            $width = $container['width'];
            $length = $container['length'];

            if (is_numeric($weight) && is_numeric($height) && is_numeric($width) && is_numeric($length)) {
                $weight = round($weight, 2);
                $volume = round($height * $width * $length, 2);

                if ($packageWeight > $weight || $packageVolume > $volume) {
                    // exceeds restriction
                    continue;
                }

                // first matching container for starters
                if (!$minContainer) {
                    $minContainer = $container;
                    $minWeight = $weight;
                    $minVolume = $volume;
                    continue;
                }

                // try to find smaller container
                if (($weight < $minWeight || $volume < $minVolume)
                    && $weight * $volume < $minWeight * $minVolume
                ) {
                    $minContainer = $container;
                    $minWeight = $weight;
                    $minVolume = $volume;
                }
            }
        }

        $transport = new Varien_Object(array('min_container' => $minContainer));
        Mage::dispatchEvent('convert_porterbuddy_calculate_container_size', array(
            'transport' => $transport,
            'request' => $request,
        ));
        $minContainer = $transport->getData('min_container');

        if (!$minContainer) {
            $this->helper->log(
                'Could not find matching container.',
                array(
                    'shipment' => $shipment->getId(),
                    'weight' => $packageWeight,
                    'volume' =>$packageVolume,
                ),
                Zend_Log::WARN
            );
            throw new Convert_Porterbuddy_Exception($this->helper->__(
                'Could not find matching container, weight %s kg, volume %s cm³.',
                $packageWeight,
                $packageVolume
            ));
        }

        $this->helper->log(
            'Found matching container.',
            array(
                'shipment' => $shipment->getId(),
                'weight' => $packageWeight,
                'volume' =>$packageVolume,
                'container' => $minContainer['code'],
            )
        );

        return $minContainer;
    }
}
