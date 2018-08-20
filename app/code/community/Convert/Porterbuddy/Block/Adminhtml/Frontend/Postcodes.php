<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2018 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Block_Adminhtml_Frontend_Postcodes extends Mage_Adminhtml_Block_System_Config_Form_Field
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
        $this->availability = Mage::getSingleton('convert_porterbuddy/availability');
        $this->coreHelper = Mage::helper('core');
        $this->helper = Mage::helper('convert_porterbuddy');
        parent::__construct($args);
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $updated = $this->availability->getPostcodesUpdated();

        $comment = $this->helper->__('Last Update') . ': ';
        if ($updated) {
            $comment .= $this->coreHelper->formatDate($updated->format('r'), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM)
                . ' ' . $this->coreHelper->formatTime($updated->format('r'));
        } else {
            $comment .= $this->helper->__('Never');
        }

        $element->setComment($comment);

        return parent::render($element);
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setStyle('width:70px;');
        $element->setValue($this->helper->__('Update'));

        $url = $this->getUrl('adminhtml/porterbuddy_postcodes/update');
        $element->setOnclick("location.href = '$url'; this.disabled = true");

        return $element->getElementHtml();
    }
}
