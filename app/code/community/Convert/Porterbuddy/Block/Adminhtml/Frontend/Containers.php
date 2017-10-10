<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Block_Adminhtml_Frontend_Containers
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function _prepareToRender()
    {
        $helper = Mage::helper('convert_porterbuddy');
        $this->addColumn('name', array(
            'label' => $helper->__('Name'),
            'style' => 'width:100px',
        ));
        $this->addColumn('code', array(
            'label' => $helper->__('Code'),
            'style' => 'width:100px',
        ));
        $this->addColumn('weight', array(
            'label' => $helper->__('Max. Weight (kg)'),
            'style' => 'width:100px',
        ));
        $this->addColumn('length', array(
            'label' => $helper->__('Max. Length (cm)'),
            'style' => 'width:100px',
        ));
        $this->addColumn('width', array(
            'label' => $helper->__('Max. Width (cm)'),
            'style' => 'width:100px',
        ));
        $this->addColumn('height', array(
            'label' => $helper->__('Max. Height (cm)'),
            'style' => 'width:100px',
        ));
 
        $this->_addAfter = false;
        $this->_addButtonLabel = $helper->__('Add');
    }
}
