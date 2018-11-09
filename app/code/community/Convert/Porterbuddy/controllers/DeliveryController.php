<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_DeliveryController extends Mage_Checkout_Controller_Action
{
    /**
     * @var Convert_Porterbuddy_Model_Availability
     */
    protected $availability;

    /**
     * @var Mage_Checkout_Model_Session
     */
    protected $checkoutSession;

    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @var Convert_Porterbuddy_Model_Geoip
     */
    protected $geoip;

    /**
     * @var Convert_Porterbuddy_Model_Shipment
     */
    protected $shipment;

    /**
     * @var Convert_Porterbuddy_Model_Timeslots
     */
    protected $timeslots;

    protected function _construct(
        array $data = null,
        Convert_Porterbuddy_Model_Availability $availability = null,
        Convert_Porterbuddy_Helper_Data $helper = null,
        Convert_Porterbuddy_Model_Geoip $geoip = null,
        Convert_Porterbuddy_Model_Shipment $shipment = null,
        Convert_Porterbuddy_Model_Timeslots $timeslots = null
    ) {
        $this->availability = $availability ?: Mage::getSingleton('convert_porterbuddy/availability');
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');
        $this->geoip = $geoip ?: Mage::getSingleton('convert_porterbuddy/geoip');
        $this->shipment = $shipment ?: Mage::getSingleton('convert_porterbuddy/shipment');
        $this->timeslots = $timeslots ?: Mage::getSingleton('convert_porterbuddy/timeslots');
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
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        if (null === $this->checkoutSession) {
            $this->checkoutSession = Mage::getSingleton('checkout/session');
        }
        return $this->checkoutSession;
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

        $quote = $this->getCheckout()->getQuote();
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
     * Refreshes available Porterbuddy delivery time slots
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function refreshAction()
    {
        $address = $this->getCheckout()->getQuote()->getShippingAddress();
        $address->setCollectShippingRates(true);

        $dates = $this->timeslots->getDatesTimeslots($address, false);

        return $this->prepareDataJSON($dates);
    }

    /**
     * Detects current location based on IP
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function locationAction()
    {
        if (!$this->helper->ipDiscoveryEnabled()) {
            return $this->jsonError($this->helper->__('GeoIp lookup is disabled'));
        }

        $ip = $this->getRequest()->getClientIp();

        // In case of chained IP addresses "34.242.90.202, 127.0.0.1, 127.0.0.1", use first
        $pos = strpos($ip, ',');
        if ($pos) {
            $ip = substr($ip, 0, $pos);
        }

        try {
            $info = $this->geoip->getInfo($ip);
        } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
            // don't log IP not found errors
            return $this->jsonError($this->helper->__('IP address not found in database'));
        } catch (\Exception $e) {
            $this->helper->log('Get postcode by IP error - ' . $e->getMessage(), array('ip' => $ip), Zend_Log::WARN);
            $this->helper->log($e);
            return $this->jsonError($this->helper->__('IP address lookup error'));
        }

        // while city may be found, postcode is crucial for availability
        if (!$info->postal->code) {
            // address found but no postcode
            return $this->jsonError($this->helper->__('Postcode is unknown for IP address'));
        } else {
            return $this->prepareDataJSON([
                'postcode' => $info->postal->code,
                'city' => $info->city->name,
                'country' => $info->country->name,
            ]);
        }
    }

    /**
     * Checks postcode is available, product is in stock and calculates closest deadline
     *
     * - available today before 16:00 - Want it today? order within 5 hrs 30 minutes
     * - today it's too late, can ship tomorrow - Want it tomorrow? order within 1 day 5 hrs 30 minutes
     * - today it's too late and then there's a weekend - Want it Monday? order within 3 days 5 hrs 30 minutes
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function availabilityAction()
    {
        $postcode = $this->getRequest()->getParam('postcode');
        $productId = $this->getRequest()->getParam('productId');
        $qty = $this->getRequest()->getParam('qty');

        if (!$postcode) {
            return $this->jsonError($this->helper->__('Postcode is required'));
        }
        if (!$productId) {
            return $this->jsonError($this->helper->__('Product ID is required'));
        }

        if (!$this->availability->isPostcodeSupported($postcode)) {
            return $this->jsonError(
                $this->helper->processPlaceholders(
                    $this->helper->getAvailabilityTextPostcodeError()
                )
            );
        }

        // check product is in stock
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product');
        $product->load($productId);
        if (!$product->getId()) {
            return $this->jsonError($this->helper->__('Product not found'));
        }
        if (!$product->isAvailable()) {
            // TODO: product placeholders
            return $this->jsonError(
                $this->helper->processPlaceholders(
                    $this->helper->getAvailabilityTextOutOfStock()
                )
            );
        }
        if ($product->isVirtual()) {
            return $this->jsonError($this->helper->__('Virtual products cannot be shipped'));
        }

        // check store working hours + Porterbuddy hours
        $date = $this->availability->getAvailableUntil();
        if (!$date) {
            return $this->jsonError(
                $this->helper->processPlaceholders(
                    $this->helper->getAvailabilityTextNoDate()
                )
            );
        }

        $now = $this->helper->getCurrentTime();
        // server-based countdown in case browser's clocks lie
        $timeRemaining = floor(($date->getTimestamp() - $now->getTimestamp())/60); // minutes

        // today, tomorrow, Monday, May 28
        $now = $this->helper->getCurrentTime();
        if ($now->format('Y-m-d') == $date->format('Y-m-d')) {
            $humanDate = mb_convert_case($this->helper->__('Today'), MB_CASE_LOWER);
        } elseif ($now->modify('+1 day')->format('Y-m-d') == $date->format('Y-m-d')) {
            $humanDate = mb_convert_case($this->helper->__('Tomorrow'), MB_CASE_LOWER);
        } else {
            $humanDate = $this->helper->__($date->format('l'));
        }

        $result = new Varien_Object(array(
            'available' => true,
            'date' => $date->format(DateTime::ATOM),
            'humanDate' => $humanDate,
            'timeRemaining' => $timeRemaining,
        ));
        Mage::dispatchEvent('convert_porterbuddy_availability', array(
            'postcode' => $postcode,
            'product' => $product,
            'qty' => $qty,
            'result' => $result,
        ));

        if ($result->getError()) {
            $defaultMessage = $this->helper->processPlaceholders(
                $this->helper->getAvailabilityTextOutOfStock()
            );
            return $this->jsonError($result->getMessage() ?: $defaultMessage);
        }

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
