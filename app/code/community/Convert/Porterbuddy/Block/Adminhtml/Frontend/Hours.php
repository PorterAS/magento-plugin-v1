<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Block_Adminhtml_Frontend_Hours extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setStyle('width:70px;')
            ->setName($element->getName() . '[]');

        if ($element->getValue()) {
            $values = explode(',', $element->getValue());
        } else {
            $values = array();
        }

        $from = $element->setValue(isset($values[0]) ? $values[0] : null)->getElementHtml();
        $to = $element->setValue(isset($values[1]) ? $values[1] : null)->getElementHtml();
        return $from . ' - ' . $to;
    }
}
