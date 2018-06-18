<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Config_Source_Mode
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
        $modes = array();
        $modes[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::MODE_DEVELOPMENT,
            'label' => $this->helper->__('Development'),
        );
        $modes[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::MODE_TESTING,
            'label' => $this->helper->__('Testing'),
        );
        $modes[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::MODE_PRODUCTION,
            'label' => $this->helper->__('Production'),
        );

        return $modes;
    }
}
