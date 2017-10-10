<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Config_Source_Hours
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
        $options = array();

        for ($hour = 0; $hour < 24; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 15) {
                $value = sprintf('%02d:%02d', $hour, $minute);
                $options[] = array(
                    'value' => $value,
                    'label' => $value,
                );
            }
        }

        // final brush
        $value = '24:00';
        $options[] = array(
            'value' => $value,
            'label' => $value,
        );

        return $options;
    }
}
