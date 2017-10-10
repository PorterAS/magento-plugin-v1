<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Config_Source_DiscountType
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    public function __construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $types = array();
        $types[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::DISCOUNT_TYPE_NONE,
            'label' => $this->helper->__('None'),
        );
        $types[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::DISCOUNT_TYPE_FIXED,
            'label' => $this->helper->__('Fixed'),
        );
        $types[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::DISCOUNT_TYPE_PERCENT,
            'label' => $this->helper->__('Percent'),
        );

        return $types;
    }
}
