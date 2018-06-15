<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Config_Source_Dimension
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    public function __construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
    }

    public function toOptionArray()
    {
        $dimensionUnits = array();
        $dimensionUnits[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::UNIT_MILLIMETER,
            'label' => $this->helper->__('Millimeter'),
        );
        $dimensionUnits[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::UNIT_CENTIMETER,
            'label' => $this->helper->__('Centimeter'),
        );

        return $dimensionUnits;
    }
}
