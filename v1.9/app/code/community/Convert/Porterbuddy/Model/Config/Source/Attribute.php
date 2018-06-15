<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Config_Source_Attribute
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
        $array = array();

        $array[] = array(
            'label' => $this->helper->__('-- Please Select --'),
            'value' => '',
        );

        $collection = Mage::getResourceModel('catalog/product_attribute_collection');
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
        foreach ($collection as $attribute) {
            if ($attribute['frontned_input'] == 'hidden') {
                continue;
            }
            if (empty($attribute['frontend_label'])) {
                $attribute['frontend_label'] = $attribute['attribute_code'];
            }
            $array[] = array(
                'value' => $attribute['attribute_code'],
                'label' => $attribute['frontend_label'],
            );
        }

        return $array;
    }
}
