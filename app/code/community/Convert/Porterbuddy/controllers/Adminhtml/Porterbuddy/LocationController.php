<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
$a = 1;
class Convert_Porterbuddy_Adminhtml_Porterbuddy_LocationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    protected function _construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order');
    }

    /**
     * @return Mage_Adminhtml_Controller_Action
     */
    public function optionsAction()
    {
        /** @var Mage_Adminhtml_Model_Session $session */
        $session = Mage::getSingleton('adminhtml/session');

        if (!$this->getRequest()->isPost()) {
            $session->addError($this->helper->__('Method not allowed.'));
            return $this->_redirectReferer();
        }

        $orderId = $this->getRequest()->getParam('order_id');
        $leaveDoorstep = $this->getRequest()->getPost('leave_doorstep');
        $comment = $this->getRequest()->getPost('comment');

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getId()) {
            $session->addError($this->helper->__('Order not found.'));
            return $this->_redirectReferer();
        }

        if (!$order->getShippingCarrier() instanceof Convert_Porterbuddy_Model_Carrier) {
            $session->addError($this->helper->__('This is not a valid order.'));
            return $this->_redirectReferer();
        }

        if (count($order->getShipmentsCollection())) {
            $session->addError($this->helper->__('This order has already been shipped.'));
            return $this->_redirectReferer();
        }

        $order
            ->setPbLeaveDoorstep($leaveDoorstep)
            ->setPbComment($comment);

        try {
            $order->save();
        } catch (Exception $e) {
            $this->helper->log($e);
            $session->addError($e->getMessage());
            return $this->_redirectReferer();
        }

        $session->addSuccess($this->helper->__('Order has been updated.'));
        return $this->_redirectReferer();
    }
}
