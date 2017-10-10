<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Config_Source_Weight
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
        $weightUnits = array();
        $weightUnits[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::WEIGHT_GRAM,
            'label' => $this->helper->__('Gram'),
        );
        $weightUnits[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::WEIGHT_KILOGRAM,
            'label' => $this->helper->__('Kilogram'),
        );

        return $weightUnits;
    }
}
