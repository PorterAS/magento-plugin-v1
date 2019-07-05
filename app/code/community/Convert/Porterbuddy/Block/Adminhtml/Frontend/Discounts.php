<?php

class Convert_Porterbuddy_Block_Adminhtml_Frontend_Discounts
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function _prepareToRender()
    {
        $helper = Mage::helper('convert_porterbuddy');
        $this->addColumn('discount', array(
            'label' => $helper->__('discount'),
        ));
        $this->addColumn('minimumbasket', array(
            'label' => $helper->__('minimumbasket'),
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = $helper->__('Add');
    }
}
