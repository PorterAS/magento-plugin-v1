<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2018 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Block_Adminhtml_Frontend_Cargonizerprinterrefresh extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @var Convert_Porterbuddy_Model_Availability
     */
    protected $availability;

    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $coreHelper;

    public function __construct(array $args = array())
    {
        $this->printers = Mage::getSingleton('convert_porterbuddy/cargonizer');
        $this->coreHelper = Mage::helper('core');
        $this->helper = Mage::helper('convert_porterbuddy');
        parent::__construct($args);
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setStyle('width:70px;');
        $element->setValue($this->helper->__('Update'));

        $url = $this->getUrl('adminhtml/porterbuddy_cargonizer/update');
        $element->setOnclick("location.href = '$url'; this.disabled = true");

        return $element->getElementHtml();
    }
}
