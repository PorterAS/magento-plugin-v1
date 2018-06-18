<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Config_Source_Location
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    public function __construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
    }

    public function toOptionArray($isMultiselect=false)
    {
        $options = array();
        $options[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::LOCATION_BROWSER,
            'label' => $this->helper->__('Browser location API lookup'),
        );
        $options[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::LOCATION_IP,
            'label' => $this->helper->__('IP location lookup'),
        );

        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        }

        return $options;
    }
}
