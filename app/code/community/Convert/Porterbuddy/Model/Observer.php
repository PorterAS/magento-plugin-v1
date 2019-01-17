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

    /**
     * @var Convert_Porterbuddy_Model_Errornotifier
     */
    protected $errorNotifier;

    /**
     * @var Convert_Porterbuddy_Model_Shipment
     */
    protected $shipment;

    public function __construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
        $this->errorNotifier = Mage::getSingleton('convert_porterbuddy/errornotifier');
        $this->shipment = Mage::getSingleton('convert_porterbuddy/shipment');
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
            && !$block instanceof Convert_Porterbuddy_Block_Checkout
        ) {
            $html = $transport->getHtml();

            /** @var Convert_Porterbuddy_Block_Checkout $widget */
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

        if (!$order->getShippingCarrier() instanceof Convert_Porterbuddy_Model_Carrier) {
            return;
        }

        $methodInfo = $this->helper->parseMethod($order->getShippingMethod());
        if (!$methodInfo['start']) {
            return;
        }

        // we only need date, but still respect timezone for border cases
        $configTimezone = Mage::app()
            ->getStore($order->getStoreId())
            ->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
        $timezone = new DateTimeZone($configTimezone);
        $start = new DateTime($methodInfo['start']);
        $start->setTimezone($timezone);

        /** @var Mage_Core_Helper_DAta $coreHelper */
        $coreHelper = Mage::helper('core');
        $date = $coreHelper->formatDate($start->format('r'), Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $order->setShippingDescription($order->getShippingDescription() . " ($date)");
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

        if (!$this->helper->getActive()) {
            return;
        }

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Abstract) {
            $this->processAdminOrderBlock($block, $transport);
        } elseif ($block instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_Packaging) {
            $this->processAdminPackagingTemplate($block, $transport);
        }
    }

    /**
     * @param Mage_Adminhtml_Block_Sales_Order_Abstract $block
     * @param $transport
     */
    protected function processAdminOrderBlock(Mage_Adminhtml_Block_Sales_Order_Abstract $block, $transport)
    {
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

    /**
     * Updates admin packaging popup template
     *
     * - package description
     * - add weight in grams
     * - default sizes in cm
     * - verification popup options
     *
     * @param Varien_Event_Observer $observer
     */
    protected function processAdminPackagingTemplate(
        Mage_Adminhtml_Block_Sales_Order_Shipment_Packaging $block,
        $transport
    ) {
        $html = $transport->getHtml();

        // add gram weight unit
        $html = $this->updateWeightUnits($html);

        $transport->setHtml($html);
    }

    protected function updateWeightUnits($html)
    {
        $start = strpos($html, '<select name="container_weight_units"');
        if (false === $start) {
            return $html;
        }
        $start = strpos($html, '<option', $start);
        if (false === $start) {
            return $html;
        }

        $end = strpos($html, '</select>', $start);
        if (false === $end) {
            return $html;
        }


        $options = substr($html, $start, $end-$start);

        // prepend grams option
        $option = sprintf(
            "<option value=\"%s\">%s</option>\n",
            Convert_Porterbuddy_Model_Carrier::WEIGHT_GRAM,
            $this->helper->__('gram')
        );
        $options = $option . $options;

        // remove default pounds selection
        $options = str_replace(' selected="selected"', '', $options);

        // set selected gram/kg depending on config
        $weightUnit = $this->helper->getWeightUnit();
        $options = str_replace(
            sprintf('value="%s"', $weightUnit),
            sprintf('value="%s" selected="selected"', $weightUnit),
            $options
        );

        // replace options html
        $html = substr($html, 0, $start) . $options . substr($html, $start + strlen($options));

        return $html;
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
     * For orders in mode "select delivery time on confirmation" - create shipment
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function checkoutSuccessCreateShipment(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getOrderIds();
        if (!$orderIds) {
            $this->helper->log('Checkout confirmation - last order ID is empty', null, Zend_Log::WARN);
            return;
        }

        $orderId = reset($orderIds);
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order');
        $order->load($orderId);
        if (!$order->getId()) {
            $this->helper->log("Checkout confirmation - cannot load last order `$orderId`", null, Zend_Log::WARN);
            return;
        }

        //$timeslotSelection = $order->getPbTimeslotSelection();
        //if (Convert_Porterbuddy_Model_Carrier::TIMESLOT_CONFIRMATION != $timeslotSelection) {
        //    $this->helper->log("Checkout confirmation - not creating shipment for order `$orderId` with timeslot selection `$timeslotSelection`");
        //    return;
        //}
        if (!$order->getShippingCarrier() instanceof Convert_Porterbuddy_Model_Carrier) {
            $this->helper->log(
              'Trying to create order for not porterbuddy order, returning',
              array('order_id' => $order->getId()),
              Zend_Log::NOTICE
          );
            return false;
        }
        try {
            $this->helper->lockShipmentCreation(
                $order,
                Convert_Porterbuddy_Helper_Data::SHIPMENT_CREATOR_CONFIRMATION,
                function ($order) {
                    $this->helper->log(
                        'Creating shipment on checkout confirmation',
                        array('order_id' => $order->getId()),
                        Zend_Log::NOTICE
                    );
                    $this->shipment->createShipment($order);
                }
            );
        } catch (\Exception $e) {
            // already logged
        }
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
            // @see Mage_Shipping_Model_Shipping::requestToShipment
            $fakeAdmin->setFirstname('Store');
            $fakeAdmin->setLastname('Admin');
            $fakeAdmin->setEmail(
                Mage::getStoreConfig('trans_email/ident_general/email', $shipment->getStoreId())
            );
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
            // Magento can throw error before carrier can intercept it, e.g. missing shipping settings
            if (!$shipment->getPorterbuddyErrorNotified()) {
                $this->errorNotifier->notify($e, $shipment);
                $shipment->setPorterbuddyErrorNotified(true);
            }

            // don't use finally{} to be compatible with PHP 5.4
            if ($fakeAdmin) {
                $adminSession->setUser(null);
            }
            throw $e;
        }

        return true;
    }

    /**
     * Preselect shipping address city and postcode from Porterbuddy location
     *
     * @param Varien_Event_Observer $observer
     */
    public function addToCartSetLocation(Varien_Event_Observer $observer)
    {
        /** @var Mage_Checkout_Model_Cart $cart */
        $cart = $observer->getCart();
        $shippingAddress = $cart->getQuote()->getShippingAddress();

        $location = Mage::app()->getRequest()->getCookie(Convert_Porterbuddy_Model_Carrier::COOKIE);
        if (!$location) {
            return;
        }

        $location = @json_decode($location, true);

        if (!$location || !$this->shouldSetLocation($location)) {
            return;
        }

        if (!$shippingAddress->getCity() && !empty($location['city'])) {
            $shippingAddress->setCity($location['city']);
        }
        if (!$shippingAddress->getPostcode() && !empty($location['postcode'])) {
            $shippingAddress->setPostcode($location['postcode']);
        }
    }


    /**
     * @param mixed $location
     * @return bool
     */
    protected function shouldSetLocation($location)
    {
        if (!empty($location['source'])) {
            $result = Convert_Porterbuddy_Model_Carrier::SOURCE_IP !== $location['source'];
        } else {
            $result = true;
        }

        $transport = new Varien_Object(array('result' => $result));
        Mage::dispatchEvent('convert_porterbuddy_should_set_shipping_location', array(
            'location' => $location,
            'transport' => $transport,
        ));
        $result = $transport->getData('result');

        return $result;
    }
}
