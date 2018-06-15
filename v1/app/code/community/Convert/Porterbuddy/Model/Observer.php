<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Observer
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    public function __construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
    }

    /**
     * Append widget to shipping methods
     *
     * @param Varien_Event_Observer $observer
     */
    public function shippingMethodsAvailableToHtmlAddWidget(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        $transport = $observer->getTransport();

        if (!$this->helper->getActive()) {
            return;
        }

        if (
            $block instanceof Mage_Checkout_Block_Onepage_Shipping_Method_Available
            && !$block instanceof Convert_Porterbuddy_Block_Widget
        ) {
            $html = $transport->getHtml();

            /** @var Convert_Porterbuddy_Block_Widget $widget */
            $widget = Mage::app()->getLayout()->getBlock('porterbuddy_widget');
            if ($widget) {
                $html .= $widget->toHtml();
            }

            $transport->setHtml($html);
        }
    }

    /**
     * After creating order update shipping description - include full date
     *
     * @param Varien_Event_Observer $observer
     */
    public function orderCreateChangeShippingDescription(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();

        if ($order->getShippingCarrier() instanceof Convert_Porterbuddy_Model_Carrier) {
            $methodInfo = $this->helper->parseMethod($order->getShippingMethod());

            if (Convert_Porterbuddy_Model_Carrier::METHOD_SCHEDULED === $methodInfo['type']) {
                // we only need date, but still respect timezone for border cases
                $configTimezone = Mage::app()
                    ->getStore($order->getStoreId())
                    ->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
                $timezone = new DateTimeZone($configTimezone);
                $date = new DateTime($methodInfo['date']);
                $date->setTimezone($timezone);

                /** @var Mage_Core_Helper_DAta $coreHelper */
                $coreHelper = Mage::helper('core');
                $humanDate = $coreHelper->formatDate(
                    $date->format('r'),
                    Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
                );

                $order->setShippingDescription($order->getShippingDescription() . " ($humanDate)");
            }
        }
    }

    /**
     * Append Porterbuddy details to shipping methods
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function adminOrderTabInfoToHtmlAddDetails(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        $transport = $observer->getTransport();

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Abstract) {
            $html = $transport->getHtml(); // store html before rendering next block - it will override

            /** @var Convert_Porterbuddy_Block_Adminhtml_Info $info */
            $info = Mage::app()->getLayout()->createBlock('convert_porterbuddy/adminhtml_info');
            $info->setOrder($block->getOrder());
            $infoHtml = $info->toHtml();

            if ($infoHtml) {
                // inject into shipping information fieldset
                $shippingInfoPos = strpos($html, 'head-shipping-method');
                if (false !== $shippingInfoPos) {
                    $endFieldsetPos = strpos($html, '</fieldset>', $shippingInfoPos);
                    if (false !== $endFieldsetPos) {
                        $before = substr($html, 0, $endFieldsetPos);
                        $after = substr($html, $endFieldsetPos);
                        $html = $before . $infoHtml . $after;
                    }
                }
            }

            // return original html even if not changed because rendering droppoing affected it
            $transport->setHtml($html);
        }
    }

    /**
     * Detects if order has been paid for payment methods that pay right when order is created
     *
     * E.g. PayPal Express, Klarna Checkout
     *
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function paymentPlaceAfterCheckPaid(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $observer->getPayment();
        $order = $payment->getOrder();

        if (!$order->getShippingCarrier() instanceof Convert_Porterbuddy_Model_Carrier) {
            return false;
        }
        if ($order->getPbPaidAt()) {
            return true;
        }

        $paid = false;
        if (Mage_Sales_Model_Order::STATE_PROCESSING == $order->getState()) {
            $paid = true;
        }

        $transport = new Varien_Object(array('paid' => $paid));
        Mage::dispatchEvent('convert_porterbuddy_payment_place_is_paid', array(
            'payment' => $payment,
            'transport' => $transport,
        ));
        $paid = $transport->getData('paid');

        if ($paid) {
            $this->helper->log(
                'Payment detected - order place after.',
                array('order_id' => $order->getId()),
                Zend_Log::NOTICE
            );
            $order->setPbPaidAt(Mage::getSingleton('core/date')->gmtDate());
        }

        return (bool)$paid;
    }

    /**
     * Detects if order has been paid for methods updating order state later - on server callback, when user returned
     *
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function orderSaveBeforeCheckPaid(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();

        if (!$order->getShippingCarrier() instanceof Convert_Porterbuddy_Model_Carrier) {
            return false;
        }
        if ($order->getPbPaidAt()) {
            return true;
        }

        $paid = false;
        if (!$order->isObjectNew() // skip first order save before place() is called
            && Mage_Sales_Model_Order::STATE_PROCESSING == $order->getState()
            && (!$order->getOrigData('state') || $order->dataHasChangedFor('state'))
        ) {
            $paid = true;
        }

        $transport = new Varien_Object(array('paid' => $paid));
        Mage::dispatchEvent('convert_porterbuddy_order_save_is_paid', array(
            'order' => $order,
            'transport' => $transport,
        ));
        $paid = $transport->getData('paid');

        if ($paid) {
            $this->helper->log(
                'Payment detected - order save before.',
                array('order_id' => $order->getId()),
                Zend_Log::NOTICE
            );
            $order->setPbPaidAt(Mage::getSingleton('core/date')->gmtDate());
        }

        return (bool)$paid;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return bool
     * @throws Exception
     */
    public function shipmentSaveBeforeSendShipment(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Shipment $shipment */
        $shipment = $observer->getShipment();
        $carrier = $shipment->getOrder()->getShippingCarrier();

        if (!$carrier instanceof Convert_Porterbuddy_Model_Carrier) {
            return false;
        }

        if (!$shipment->isObjectNew()) {
            return false;
        }

        // if admin created shipment and entered label params explicitly, we have already processed this shipment
        if ($shipment->getIsPorterbuddySent()) {
            return false;
        }

        /** @var Mage_Shipping_Model_Shipping $shippingService */
        $shippingService = Mage::getModel('shipping/shipping');

        // requestToShipment requires current admin user in session
        // contact person name: firstname, lastname, name, email
        $adminSession = Mage::getSingleton('admin/session');
        $fakeAdmin = null;
        if (!$adminSession->getUser()) {
            /** @var Mage_Admin_Model_User $fakeAdmin */
            $fakeAdmin = Mage::getModel('admin/user');
            $fakeAdmin->load($this->helper->getDefaultContactAdmin());
            if (!$fakeAdmin->getId()) {
                $fakeAdmin->setFirstname('Store');
                $fakeAdmin->setLastname('Admin');
                $email = Mage::getStoreConfig('trans_email/ident_general/email', $shipment->getStoreId());
                $fakeAdmin->setEmail($email);
            }
            $adminSession->setUser($fakeAdmin);
        }

        // can throw exception and abort shipment creation
        try {
            // normally errors when creating labels don't throw exceptions
            // but we want any error to prevent shipment creation, so we use exceptions
            $response = $shippingService->requestToShipment($shipment); // carrier->requestToShipment

            if ($fakeAdmin) {
                $adminSession->setUser(null);
            }
        } catch (Exception $e) {
            // don't use finally{} to be compatible with PHP 5.4
            if ($fakeAdmin) {
                $adminSession->setUser(null);
            }
            throw $e;
        }

        return true;

    }

    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function layoutGenerateAfterAddAddressLocationForm(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Address $address */
        $address = Mage::registry('order_address');

        if ('shipping' !== $address->getAddressType()) {
            return;
        }

        $layout = Mage::app()->getLayout();
        $addressFormContainer = $layout->getBlock('sales_order_address.form.container');
        if ($addressFormContainer) {
            /** @var Mage_Adminhtml_Block_Sales_Order_Address_Form $block */
            $block = $addressFormContainer->getChild('form');
            $form = $block->getForm();

            /** @var Mage_Eav_Model_Config $eavConfig */
            $eavConfig = Mage::getSingleton('eav/config');
            // @see Mage_Adminhtml_Block_Sales_Order_Create_Form_Abstract::_addAttributesToForm
            /** @var $attribute Mage_Customer_Model_Attribute */
            $attribute = $eavConfig->getAttribute('customer_address', 'pb_location');
            $attribute->setStoreId(Mage::getSingleton('adminhtml/session_quote')->getStoreId());

            $fieldset = $form->getElement('main');
            $element = $fieldset->addField($attribute->getAttributeCode(), 'text', array(
                'name'      => $attribute->getAttributeCode(),
                'label'     => $this->helper->__($attribute->getStoreLabel()),
                'class'     => $attribute->getFrontend()->getClass(),
                'required'  => $attribute->getIsRequired(),
            ));
            $element->setEntityAttribute($attribute);

            /** @var Convert_Porterbuddy_Block_Adminhtml_Frontend_Coordinates $renderer */
            $renderer = $layout->createBlock('convert_porterbuddy/adminhtml_frontend_coordinates');
            $element->setRenderer($renderer);

            $form->addValues(array(
                $attribute->getAttributeCode() => $address->getPbLocation(),
            ));
        }
    }
}
