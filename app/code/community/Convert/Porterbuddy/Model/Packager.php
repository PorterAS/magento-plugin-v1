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
     * @var array
     */
    protected $packagers;

    /**
     * @param array|null $data
     * @param Convert_Porterbuddy_Helper_Data|null $helper
     */
    public function __construct(
        array $data = null, // for getModel to work
        Convert_Porterbuddy_Helper_Data $helper = null
    ) {
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');

        $packagers = Mage::getConfig()->getNode("global/convert_porterbuddy/packagers")->asCanonicalArray();
        foreach ($packagers as $code => $config) {
            $this->packagers[$code] = $config;
        }
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return array
     * @throws Convert_Porterbuddy_Exception
     */
    public function estimateParcels(Mage_Shipping_Model_Rate_Request $request)
    {
        $mode = $this->helper->getPackagerMode();
        if (!isset($this->packagers[$mode])) {
            $mode = Convert_Porterbuddy_Model_Packager_Peritem::MODE;
        }

        return $this->getPackager($mode)
            ->estimateParcels($request);
    }

    /**
     * Create package automatically when estimating availability and submitting shipment
     *
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return array Packages in Magento format
     * @throws Convert_Porterbuddy_Exception
     */
    public function createPackages(Mage_Shipping_Model_Shipment_Request $request)
    {
        $mode = $this->helper->getPackagerMode();
        if (!isset($this->packagers[$mode])) {
            $mode = Convert_Porterbuddy_Model_Packager_Peritem::MODE;
        }

        return $this->getPackager($mode)
            ->createPackages($request);
    }

    /**
     * @return array code => configuration
     */
    public function getModes()
    {
        return $this->packagers;
    }

    /**
     * @param string $mode
     * @return Convert_Porterbuddy_Model_Packager_PackagerInterface
     * @throws Convert_Porterbuddy_Exception
     */
    public function getPackager($mode)
    {
        $packager = Mage::getSingleton($this->packagers[$mode]['model']);
        if (!$packager instanceof Convert_Porterbuddy_Model_Packager_PackagerInterface) {
            throw new Convert_Porterbuddy_Exception($this->helper->__(
                'Packager for strategy `%s` must implement class `%s`.',
                $mode,
                'Convert_Porterbuddy_Model_Packager_PackagerInterface'
            ));
        }

        return $packager;
    }

    /**
     * Converts Magento shipment packages to Porterbuddy parcels format
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     * @throws Varien_Exception
     */
    public function getParcelsFromPackages(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $packages = $shipment->getPackages();
        if ($packages && is_scalar($packages)) {
            $packages = unserialize($packages);
        }

        $parcels = array();
        foreach ($packages as $package) {
            $package = new Varien_Object($package);
            $description = $package->getData('params/content_type_other');
            if (!$description && is_array($package->getData('items'))) {
                $lines = array();
                foreach ($package->getData('items') as $item) {
                    if (isset($item['qty'], $item['name'])) {
                        $qty = $item['qty'];
                        $name = $item['name'];
                        $lines[] = $qty > 1 ? "$qty x $name" : $name;
                    }
                }
                $description = implode(', ', $lines);
            }
            if (!$description) {
                $description = $this->helper->__('%s products', count($shipment->getAllItems()));
            }

            $nextParcel = array(
                'description' => $description,
                'widthCm' => $this->helper->convertDimensionToCm(
                    $package->getData('params/width'),
                    $package->getData('params/dimension_units')
                ) ?: $this->helper->getDefaultProductWidth(),
                'heightCm' => $this->helper->convertDimensionToCm(
                    $package->getData('params/height'),
                    $package->getData('params/dimension_units')
                ) ?: $this->helper->getDefaultProductHeight(),
                'depthCm' => $this->helper->convertDimensionToCm(
                    $package->getData('params/length'),
                    $package->getData('params/dimension_units')
                ) ?: $this->helper->getDefaultProductLength(),
                'weightGrams' => $this->helper->convertWeightToGrams(
                    $package->getData('params/weight'),
                    $package->getData('params/weight_unit')
                ) ?: $this->helper->getDefaultProductWeight(),
            );
            $nextParcel['isLarge'] = ($this->helper->getEnableLarge() &&
                $nextParcel['widthCm'] > 50 &&
                $nextParcel['heightCm'] > 50 &&
                $nextParcel['depthCm'] > 50);
            $parcels[] = $nextParcel;
        }

        return $parcels;
    }
}
