<?php

class Convert_Porterbuddy_Model_Config_Source_Timeslot
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
        $result = array();
        $result[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::TIMESLOT_CHECKOUT,
            'label' => $this->helper->__('In checkout'),
        );
        $result[] = array(
            'value' => Convert_Porterbuddy_Model_Carrier::TIMESLOT_CONFIRMATION,
            'label' => $this->helper->__('On confirmation page'),
        );

        return $result;
    }
}
