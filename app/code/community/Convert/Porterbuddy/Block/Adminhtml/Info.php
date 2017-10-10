<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Block_Adminhtml_Info extends Mage_Adminhtml_Block_Template
{
    protected $_template = 'convert/porterbuddy/info.phtml';

    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    protected function _construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
    }

    public function setOrder(Mage_Sales_Model_Order $order)
    {
        return $this->setData('order', $order);
    }

    /**
     * @return Mage_Sales_Model_Order|null
     */
    public function getOrder()
    {
        return $this->getData('order');
    }

    protected function _toHtml()
    {
        if ($this->getOrder() && $this->getOrder()->getIsVirtual()) {
            return '';
        }
        if (!$this->getOrder()->getShippingCarrier() instanceof Convert_Porterbuddy_Model_Carrier) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return bool
     */
    public function canEditOptions()
    {
        $isOrderView = in_array('adminhtml_sales_order_view', $this->getLayout()->getUpdate()->getHandles());
        $shipmentExists = count($this->getOrder()->getShipmentsCollection());

        return $isOrderView && !$shipmentExists;
    }

    /**
     * @return string
     */
    public function getLeaveDoorstepText()
    {
        return $this->helper->getLeaveDoorstepText();
    }

    /**
     * @return bool
     */
    public function getLeaveDoorstep()
    {
        return (bool)$this->getOrder()->getPbLeaveDoorstep();
    }

    /**
     * @return string
     */
    public function getCommentText()
    {
        return $this->helper->getCommentText();
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->getOrder()->getPbComment();
    }
}
