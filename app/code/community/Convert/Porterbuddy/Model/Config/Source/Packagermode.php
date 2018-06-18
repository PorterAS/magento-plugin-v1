<?php

class Convert_Porterbuddy_Model_Config_Source_Packagermode
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @var Convert_Porterbuddy_Model_Packager
     */
    protected $packager;

    /**
     * @param Convert_Porterbuddy_Helper_Data $helper optional
     */
    public function __construct(
        $data = null,
        Convert_Porterbuddy_Helper_Data $helper = null,
        Convert_Porterbuddy_Model_Packager $packager = null

    ) {
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');
        $this->packager = $packager ?: Mage::getSingleton('convert_porterbuddy/packager');
    }

    public function toOptionArray()
    {
        $options = array();

        foreach ($this->packager->getModes() as $code => $config) {
            $options[] = array(
                'value' => $code,
                'label' => $this->helper->__($config['label']),
            );
        }

        return $options;
    }
}
