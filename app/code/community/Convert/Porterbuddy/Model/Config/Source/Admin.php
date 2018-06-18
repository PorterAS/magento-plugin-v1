<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Config_Source_Admin
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

        if(!$isMultiselect){
            $options[] = array(
                'value' => '',
                'label' => $this->helper->__('-- Please Select --'),
            );
        }

        /** @var Mage_Admin_Model_Resource_User_Collection $collection */
        $collection = Mage::getResourceModel('admin/user_collection');
        foreach ($collection as $user) {
            $options[] = array(
                'value' => $user->getId(),
                'label' => $user->getName(),
            );
        }

        return $options;
    }
}
