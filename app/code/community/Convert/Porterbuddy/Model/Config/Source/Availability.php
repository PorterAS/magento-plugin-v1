<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Config_Source_Availability
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
        $options = array();
        $options[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::AVAILABILITY_HIDE,
            'label' => $this->helper->__('Hide'),
        );
        $options[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::AVAILABILITY_ONLY_AVAILABLE,
            'label' => $this->helper->__('Show only when available'),
        );
        $options[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::AVAILABILITY_ALWAYS,
            'label' => $this->helper->__('Always show'),
        );

        return $options;
    }
}
