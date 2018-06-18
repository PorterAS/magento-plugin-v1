<?php

class Convert_Porterbuddy_Block_Adminhtml_Frontend_Emailrecipients
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function _prepareToRender()
    {
        $helper = Mage::helper('convert_porterbuddy');
        $this->addColumn('email', array(
            'label' => $helper->__('Email'),
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = $helper->__('Add');
    }
}
