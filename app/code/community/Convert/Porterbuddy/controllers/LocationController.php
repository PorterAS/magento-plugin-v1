<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_LocationController extends Mage_Checkout_Controller_Action
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @var Convert_Porterbuddy_Model_Shipment
     */
    protected $shipment;

    protected function _construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
        $this->shipment = Mage::getSingleton('convert_porterbuddy/shipment');
    }

    /**
     * {@inheritdoc}
     */
    protected function _validateFormKey()
    {
        $validated = true;
        if (method_exists($this, 'isFormkeyValidationOnCheckoutEnabled')
            && $this->isFormkeyValidationOnCheckoutEnabled()
        ) {
            $validated = parent::_validateFormKey();
        }
        return $validated;
    }

    /**
     * Saves order delivery coordinates
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function saveAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->jsonError($this->helper->__('Method not allowed.'));
        }

        if (!$this->_validateFormKey()) {
            return $this->jsonError($this->helper->__('Invalid form key.'));
        }

        $orderNumber = $this->getRequest()->getPost('order_number');
        $protectCode = $this->getRequest()->getPost('protect_code');
        $lat = $this->getRequest()->getPost('lat');
        $lng = $this->getRequest()->getPost('lng');

        if (!strlen($lat) || !strlen($lng)) {
            return $this->jsonError($this->helper->__('Please select delivery location. Lat and Lng are required.'));
        }
        if (!is_numeric($lat) || !is_numeric($lng)) {
            return $this->jsonError($this->helper->__('Lat and Lng must be numeric.'));
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order');
        $order = $order->loadByIncrementId($orderNumber);
        if (!$order->getId() || $order->getProtectCode() != $protectCode) {
            return $this->jsonError($this->helper->__('Order not found.'));
        }
        if (!$order->getShippingCarrier() instanceof Convert_Porterbuddy_Model_Carrier) {
            return $this->jsonError($this->helper->__('Cannot save delivery location for carriers other than Porterbuddy.'));
        }
        if (count($order->getShipmentsCollection())) {
            return $this->jsonError($this->helper->__('Cannot change delivery location after shipment has been created.'));
        }

        try {
            $shippingAddress = $order->getShippingAddress();
            $shippingAddress
                ->setPbLocation("$lat,$lng")
                ->setPbUserEdited(true)
                ->save();

            // save to customer address. next time customer selects this address new coordinates will copy to quote address
            if ($shippingAddress->getCustomerAddressId()) {
                /** @var Mage_Customer_Model_Address $customerAddress */
                $customerAddress = Mage::getModel('customer/address')->load($shippingAddress->getCustomerAddressId());
                if ($customerAddress->getId()) {
                    $customerAddress
                        ->setPbLocation("$lat,$lng")
                        ->save();
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->helper->log($e);
            return $this->jsonError($e->getMessage());
        } catch (Exception $e) {
            $this->helper->log($e);
            return $this->jsonError();
        }

        // auto-create shipment
        if ($order->getPbPaidAt()) {
            if ($this->helper->getAutoCreateShipment()) {
                $this->helper->log(
                    'Saved location, order is paid, creating shipment',
                    array('order_id' => $order->getId()),
                    Zend_Log::NOTICE
                );
                try {
                    $this->shipment->createShipment($order);
                } catch (Exception $e) {
                    // don't report errors to user - he's done his part
                }
            } else {
                $this->helper->log(
                    'Saved location, order is paid, shipment auto-creation disabled, skip.',
                    array('order_id' => $order->getId()),
                    Zend_Log::NOTICE
                );
            }
        }

        $result = array('success' => true);
        return $this->prepareDataJSON($result);
    }

    /**
     * Saves order comment and leave at doorstep flag
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function optionsAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->jsonError($this->helper->__('Method not allowed.'));
        }

        if (!$this->_validateFormKey()) {
            return $this->jsonError($this->helper->__('Invalid form key.'));
        }

        $leaveDoorstep = $this->getRequest()->getPost('leave_doorstep');
        $comment = $this->getRequest()->getPost('comment');

        /** @var Mage_Checkout_Model_Session $checkout */
        $checkout = Mage::getSingleton('checkout/session');
        $quote = $checkout->getQuote();
        $quote
            ->setPbLeaveDoorstep($leaveDoorstep)
            ->setPbComment($comment);

        try {
            $quote->save();
        } catch (Mage_Core_Exception $e) {
            $this->helper->log($e);
            return $this->jsonError($e->getMessage());
        } catch (Exception $e) {
            $this->helper->log($e);
            return $this->jsonError();
        }

        $result = array('success' => true);
        return $this->prepareDataJSON($result);
    }

    /**
     * @param string $message optional
     * @return Zend_Controller_Response_Abstract
     */
    protected function jsonError($message = null)
    {
        $result = array();
        $result['error'] = true;
        if ($message) {
            $result['message'] = $message;
        }
        return $this->prepareDataJSON($result);
    }

    /**
     * Prepare JSON formatted data for response to client
     *
     * @param $response
     * @return Zend_Controller_Response_Abstract
     */
    protected function prepareDataJSON($response)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }
}
