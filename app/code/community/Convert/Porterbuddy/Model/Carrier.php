<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
    const CODE = 'cnvporterbuddy';

    const METHOD_EXPRESS = 'express';
    const METHOD_DELIVERY = 'delivery';

    const MODE_PRODUCTION = 'production';
    const MODE_TESTING = 'testing';
    const MODE_DEVELOPMENT = 'development';

    const WEIGHT_GRAM = 'GRAM';
    const WEIGHT_KILOGRAM = 'KILOGRAM';

    const UNIT_MILLIMETER = 'MILLIMETER';
    const UNIT_CENTIMETER = 'CENTIMETER';

    const DISCOUNT_TYPE_NONE = 'none';
    const DISCOUNT_TYPE_FIXED = 'fixed';
    const DISCOUNT_TYPE_PERCENT = 'percent';

    const TIMESLOT_CHECKOUT = 'checkout';
    const TIMESLOT_CONFIRMATION = 'confirmation';

    const AVAILABILITY_HIDE = 'hide';
    const AVAILABILITY_ONLY_AVAILABLE = 'only_available';
    const AVAILABILITY_ALWAYS = 'always';

    const LOCATION_BROWSER = 'browser';
    const LOCATION_IP = 'ip';

    const COOKIE = 'porterbuddy_location';

    const SOURCE_BROWSER = 'browser';
    const SOURCE_IP = 'ip';
    const SOURCE_USER = 'user';

    protected $_code = self::CODE;

    /**
     * @var Convert_Porterbuddy_Model_Api
     */
    protected $api;

    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @var Convert_Porterbuddy_Model_Packager
     */
    protected $packager;

    /**
     * @var Convert_Porterbuddy_Model_Timeslots
     */
    protected $timeslots;

    /**
     * @var float
     */
    protected $baseCurrencyRate;

    /**
     * @var Convert_Porterbuddy_Model_Errornotifier
     */
    protected $errorNotifier;

    /**
     * @param array|null $data
     * @param Convert_Porterbuddy_Model_Api|null $api
     * @param Convert_Porterbuddy_Helper_Data|null $helper
     * @param Convert_Porterbuddy_Model_Packager|null $packager
     * @param Convert_Porterbuddy_Model_Timeslots|null $timeslots
     */
    public function __construct(
        array $data = null, // for getModel to work
        Convert_Porterbuddy_Model_Api $api = null,
        Convert_Porterbuddy_Helper_Data $helper = null,
        Convert_Porterbuddy_Model_Packager $packager = null,
        Convert_Porterbuddy_Model_Timeslots $timeslots = null,
        Convert_Porterbuddy_Model_Errornotifier $errorNotifier = null
    ) {
        parent::__construct($data);

        $this->api = $api ?: Mage::getSingleton('convert_porterbuddy/api');
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');
        $this->packager = $packager ?: Mage::getSingleton('convert_porterbuddy/packager');
        $this->timeslots = $timeslots ?: Mage::getSingleton('convert_porterbuddy/timeslots');
        $this->errorNotifier = $errorNotifier ?: Mage::getSingleton('convert_porterbuddy/errornotifier');
    }

    /**
     * {@inheritdoc}
     */
    public function proccessAdditionalValidation(Mage_Shipping_Model_Rate_Request $request)
    {
        if ('NO' !== $request->getDestCountryId()) {
            $this->helper->log("Destination country `{$request->getDestCountryId()}` is not supported.", null, Zend_Log::WARN);
            return false;
        }
        if (!strlen($request->getDestPostcode())) {
            $this->helper->log("Empty postcode ignored.", null, Zend_Log::WARN);
            return false;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeliveryConfirmationTypes(Varien_Object $params = null)
    {
        // make default option preselected as it goes first
        if ($this->helper->isRequireSignatureDefault()) {
            return array(
                1 => $this->helper->__('Signature Required'),
                0 => $this->helper->__('Not Required'),
            );
        } else {
            return array(
                0 => $this->helper->__('Not Required'),
                1 => $this->helper->__('Signature Required'),
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContentTypes(Varien_Object $params)
    {
        return array(
            '' => $this->helper->__('-- Product names --'),
            'OTHER' => $this->helper->__('Other'),
        );
    }

    /**
     * Interface method used to get additional tracking info in tracking popup
     *
     * @param string $number
     * @return Varien_Object|array
     */
    public function getTrackingInfo($number)
    {
        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
        $track = Mage::getModel('sales/order_shipment_track')->load($number, 'track_number');

        return array(
            'title' => $track->getTitle(),
            'number' => $number,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        /** @var Convert_Porterbuddy_Model_Rate_Result $result */
        $result = Mage::getModel('convert_porterbuddy/rate_result');

        // multi warehousing may disable this method if items are physically located in different places
        Mage::dispatchEvent('convert_porterbuddy_collect_rates', array(
            'request' => $request,
            'result' => $result,
        ));
        if ($result->getError()) {
            return $result;
        }

        try {
            $parameters = $this->prepareAvailabilityData($request);
            $options = $this->api->getAvailability($parameters);
        } catch (Convert_Porterbuddy_Exception $e) {
            // details logged
            return $result;
        } catch (Exception $e) {
            // other unexpected errors
            $this->helper->log($e);
            return $result;
        }

        $expressOption = null;
        $scheduledOptions = array();

        foreach ($options as $option) {
            if (self::METHOD_EXPRESS == $option['product']) {
                $expressOption = $option;
            } elseif (self::METHOD_DELIVERY == $option['product']) {
                $scheduledOptions[] = $option;
            }
        }

        if ($expressOption) {
            $result = $this->addRateResult($request, $expressOption, $result);
        }

        foreach ($scheduledOptions as $option) {
            $result = $this->addRateResult($request, $option, $result);
        }


        $result = $this->addReturnMethods($request, $result);
        $result = $this->applyDiscounts($request, $result);

        // enable to construct new result object
        $transport = new Varien_Object(array('result' => $result));
        Mage::dispatchEvent('convert_porterbuddy_collect_rates_after', array(
            'request' => $request,
            'transport' => $transport,
        ));
        $result = $transport->getData('result');

        return $result;
    }

    public function addDeliveryOnConfirmationResult(
        Mage_Shipping_Model_Rate_Request $request,
        array $option,
        Convert_Porterbuddy_Model_Rate_Result $result
    ) {
        // Local timezone
        /** @var Mage_Shipping_Model_Rate_Result_Method $method */
        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier(self::CODE);
        $method->setCarrierTitle($this->helper->getTitle());

        $method->setMethod($option['product']); // no start-end dates
        $method->setMethodTitle($this->helper->__('Select specific time after checkout')); // $this->helper->getScheduledName()
        //$method->setMethodDescription($this->helper->getScheduledDescription());

        if ($request->getFreeShipping() === true) {
            $shippingPrice = '0.00';
        } else {
            $shippingPrice = $this->helper->getPriceOverrideDelivery();

            if (null === $shippingPrice
                && isset($option['price']['fractionalDenomination'], $option['price']['currency'])
            ) {
                $apiPrice = Mage::app()->getLocale()->getNumber($option['price']['fractionalDenomination']) / 100;
                $rate = $this->getBaseCurrencyRate($request, $option['price']['currency']);
                $shippingPrice = $apiPrice * $rate;
            }
            if (null === $shippingPrice) {
                $this->helper->log('Skip option with undefined price', array('option' => $option), Zend_Log::WARN);
                return $result;
            }
        }

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        $result->append($method);

        return $result;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @param array $option
     * @param Convert_Porterbuddy_Model_Rate_Result $result
     * @return Convert_Porterbuddy_Model_Rate_Result
     */
    public function addRateResult(
        Mage_Shipping_Model_Rate_Request $request,
        array $option,
        Convert_Porterbuddy_Model_Rate_Result $result
    ) {
        $type = $option['product'];
        $start = new DateTime($option['start']);
        $end = new DateTime($option['end']);

        $methodCode = implode(
            '_',
            array(
                $type,
                $start->format(DateTime::ATOM),
                $end->format(DateTime::ATOM)
            )
        );
        // Local timezone
        $methodTitle = $this->timeslots->formatTimeslot($start, $end);

        /** @var Mage_Shipping_Model_Rate_Result_Method $method */
        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier(self::CODE);
        $method->setCarrierTitle($this->helper->getTitle());

        $method->setMethod($methodCode);
        $method->setMethodTitle($methodTitle); // $this->helper->getScheduledName()
        //$method->setMethodDescription($this->helper->getScheduledDescription());
        $method->setMethodDescription($option['expiresAt']);

        if ($request->getFreeShipping() === true) {
            $shippingPrice = '0.00';
        } else {
            $shippingPrice = self::METHOD_EXPRESS == $type ?
                $this->helper->getPriceOverrideExpress() : $this->helper->getPriceOverrideDelivery();

            if (null === $shippingPrice
                && isset($option['price']['fractionalDenomination'], $option['price']['currency'])
            ) {
                $apiPrice = Mage::app()->getLocale()->getNumber($option['price']['fractionalDenomination']) / 100;
                $rate = $this->getBaseCurrencyRate($request, $option['price']['currency']);
                $shippingPrice = $apiPrice * $rate;
            }
            if (null === $shippingPrice) {
                $this->helper->log('Skip option with undefined price', array('option' => $option), Zend_Log::WARN);
                return $result;
            }
        }

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        $result->append($method);

        return $result;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return float
     */
    public function getBaseCurrencyRate(Mage_Shipping_Model_Rate_Request $request, $responseCurrencyCode = 'NOK')
    {
        if (null === $this->baseCurrencyRate) {
            // TODO: throw error if base currency rate is not defined
            $this->baseCurrencyRate = Mage::getModel('directory/currency')
                ->load($responseCurrencyCode)
                ->getAnyRate($request->getBaseCurrency()->getCode());
        }
        return $this->baseCurrencyRate;
    }

    /**
     * Adds return timeslots with added price and title
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @param Convert_Porterbuddy_Model_Rate_Result $origResult
     * @return Convert_Porterbuddy_Model_Rate_Result
     */
    public function addReturnMethods(
        Mage_Shipping_Model_Rate_Request $request,
        Convert_Porterbuddy_Model_Rate_Result $origResult
    ) {
        $returnPrice = $this->helper->getReturnPrice();

        if (!$this->helper->getReturnEnabled() || !is_numeric($returnPrice) || !$returnPrice) {
            return $origResult;
        }

        $returnPrice = (float)$returnPrice * $this->getBaseCurrencyRate($request);

        /** @var Convert_Porterbuddy_Model_Rate_Result $result */
        $result = Mage::getModel('convert_porterbuddy/rate_result');
        foreach ($origResult->getAllRates() as $method) {
            $result->append($method);

            $returnMethod = clone $method;

            $returnMethod->setMethod($method->getMethod() . '_return');
            $returnMethod->setMethodTitle($method->getMethodTitle() . ' ' . $this->helper->getReturnShortText());

            $price = $method->getPrice() + $returnPrice;
            $returnMethod->setPrice($price);
            $returnMethod->setCost($price);

            $result->append($returnMethod);
        }

        return $result;
    }

    /**
     * Applies discounts based on cart subtotal threshold
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @param Convert_Porterbuddy_Model_Rate_Result $result
     * @return Convert_Porterbuddy_Model_Rate_Result
     */
    public function applyDiscounts(
        Mage_Shipping_Model_Rate_Request $request,
        Convert_Porterbuddy_Model_Rate_Result $result
    ) {
        $discountType = $this->helper->getDiscountType();
        $discountSubtotal = $this->helper->getDiscountSubtotal();

        // known possible problem: $request->getBaseSubtotalInclTax can be empty in some cases, same problem with
        // free shipping. This is because shipping total collector is called before tax subtotal collector, and so
        // BaseSubtotalInclTax is not updated yet.
        $basketValue = $request->getBaseSubtotalInclTax();
        if ($basketValue == 0 && $request->getPackageValueWithDiscount()>0) {
            $basketValue = $request->getPackageValueWithDiscount() * 1.25;
        }

        if ($basketValue < $discountSubtotal) {
            // we need more gold
            return $result;
        }

        if (self::DISCOUNT_TYPE_FIXED === $discountType) {
            $discountAmount = $this->helper->getDiscountAmount();
            if ($discountAmount <= 0) {
                $this->helper->log("Invalid discount amount `$discountAmount`.", null, Zend_Log::WARN);
                return $result;
            }

            /** @var Mage_Shipping_Model_Rate_Result_Method  $method */
            foreach ($result->getAllRates() as $method) {
                $price = $method->getPrice();
                if ($price > 0) {
                    $price -= $discountAmount;
                    $price = max($price, 0.00);
                    $method->setPrice(max($price, 0.00));
                }
            }
        } elseif (self::DISCOUNT_TYPE_PERCENT === $discountType) {
            $discountPercent = $this->helper->getDiscountPercent();
            if ($discountPercent <= 0 || $discountPercent > 100) {
                $this->helper->log("Invalid discount percent `$discountPercent`.", null, Zend_Log::WARN);
                return $result;
            }

            /** @var Mage_Shipping_Model_Rate_Result_Method  $method */
            foreach ($result->getAllRates() as $method) {
                $price = $method->getPrice();
                if ($price > 0) {
                    $price -= $price * $discountPercent / 100;
                    $price = max($price, 0.00);
                    $method->setPrice($price);
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Convert_Porterbuddy_Exception
     * @throws Varien_Exception
     */
    public function requestToShipment(Mage_Shipping_Model_Shipment_Request $request)
    {
        $result = new Varien_Object();

        $shipment = $request->getOrderShipment();

        $this->helper->log('requestToShipment - start.', array(
            'shipment_id' => $shipment->getId(),
        ), Zend_Log::NOTICE);

        // mark it processed to disable auto creating label, we already processed it
        $shipment->setIsPorterbuddySent(true);

        try {
            $parameters = $this->prepareCreateOrderData($request);
            $idempotencyKey = $this->getShipmentIdempotencyKey($request);
            $orderDetails = $this->api->createOrder($parameters, $idempotencyKey);
        } catch (Convert_Porterbuddy_Exception $e) {
            $this->errorNotifier->notify($e, $shipment, $request);
            $shipment->setPorterbuddyErrorNotified(true);

            // details logged
            $result->setErrors($e->getMessage());
            //return $result;
            throw $e;
        } catch (Exception $e) {
            $this->errorNotifier->notify($e, $shipment, $request);
            $shipment->setPorterbuddyErrorNotified(true);

            // other unexpected errors
            $this->helper->log($e);
            $result->setErrors($e->getMessage());
            //return $result;
            throw $e;
        }

        // TODO: save order iframe URL for timeslot selection on confirmation page

        $comment = $this->helper->__('Porterbuddy shipment has been ordered.');
        if (!empty($orderDetails['deliveryReference'])) {
            $comment .= ' ' . $this->helper->__('Delivery reference %s', $orderDetails['deliveryReference']);
        }
        $shipment->addComment($comment);

        // Magento requires returning pairs shipping label-tracking number. As we don't support actual labels yet,
        // we can't provide tracking numbers by standard mechanism either. So we will assign them manually to shipment

        $trackingNumber = $orderDetails['orderId'];
        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
        $track = Mage::getModel('sales/order_shipment_track');
        $track
            ->setNumber($trackingNumber)
            ->setCarrierCode($this->getCarrierCode())
            ->setTitle($this->helper->__('Order ID'));
        $shipment->addTrack($track);

        if (!empty($orderDetails['deliveryReference'])) {
            $trackingNumber = $orderDetails['deliveryReference'];
            /** @var Mage_Sales_Model_Order_Shipment_Track $track */
            $track = Mage::getModel('sales/order_shipment_track');
            $track
                ->setNumber($trackingNumber)
                ->setCarrierCode($this->getCarrierCode())
                ->setTitle($this->helper->__('Delivery Reference'));
            $shipment->addTrack($track);
        }

        $result->setInfo(array()); // mark as success

        $this->helper->log('requestToShipment - success.', array(
            'shipment_id' => $shipment->getId(),
        ), Zend_Log::NOTICE);

        return $result;
    }

    /**
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return string|null
     */
    public function getShipmentIdempotencyKey(Mage_Shipping_Model_Shipment_Request $request)
    {
        $shipment = $request->getOrderShipment();
        $order = $shipment->getOrder();

        // TODO: for part shipping, include items
        return $order->getIncrementId();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedMethods()
    {
        return array(
            self::METHOD_EXPRESS => $this->helper->getAsapName(),
            self::METHOD_DELIVERY => $this->helper->__('Delivery'),
        );
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return array
     * @throws Convert_Porterbuddy_Exception
     */
    public function prepareAvailabilityData(Mage_Shipping_Model_Rate_Request $request)
    {
        $params = array();

        $params['pickupWindows'] = $this->timeslots->getAvailabilityPickupWindows();

        $originStreet1 = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS1);
        $originStreet2 = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS2);
        $params['originAddress'] = [
            'streetName' => trim("$originStreet1 $originStreet2"),
            'streetNumber' => ',', // FIXME: set empty when API is updated
            'postalCode' => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ZIP),
            'city' => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_CITY),
            'country' => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID),
        ];

        $params['destinationAddress'] = [
            'streetName' => $request->getDestStreet(),
            //'streetNumber' => '',
            'postalCode' => $request->getDestPostcode(),
            'city' => $request->getDestCity(),
            'country' => $request->getDestCountryId(),
        ];

        // create availability check context
        $params['parcels'] = $this->packager->estimateParcels($request);
        $params['products'] = array(self::METHOD_DELIVERY, self::METHOD_EXPRESS);

        $transport = new Varien_Object(array('params' => $params));
        Mage::dispatchEvent('convert_porterbuddy_availability_data', array(
            'request' => $request,
            'transport' => $transport,
        ));
        $params = $transport->getData('params');

        return $params;
    }

    /**
     * Prepares request payload for create order API call
     *
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return array
     * @throws Convert_Porterbuddy_Exception
     */
    protected function prepareCreateOrderData(Mage_Shipping_Model_Shipment_Request $request)
    {
        $shipment = $request->getOrderShipment();
        $order = $shipment->getOrder();
        $methodInfo = $this->helper->parseMethod($request->getShippingMethod());

        $methodInfo = $this->checkExpiredTimeslot($methodInfo);
        $parcels = $this->getParcels($request, $shipment);

        if (!$request->getRecipientEmail()) {
            $request->setRecipientEmail($order->getCustomerEmail());
        }

        $defaultPhoneCode = $this->helper->getDefaultPhoneCode();
        $pickupPhone = $this->helper->splitPhoneCodeNumber($request->getShipperContactPhoneNumber());
        $deliveryPhone = $this->helper->splitPhoneCodeNumber($request->getRecipientContactPhoneNumber());

        $deliveryTimeslotIsKnown = ($methodInfo['start'] && $methodInfo['end']);
        $parameters = array(
            'origin' => [
                'name' => Mage::getStoreConfig('trans_email/ident_general/name', $shipment->getStoreId()),
                'address' => [
                    'streetName' => $request->getShipperAddressStreet(),
                    'streetNumber' => ',', // TODO: remove when API ready
                    'postalCode' => $request->getShipperAddressPostalCode(),
                    'city' => $request->getShipperAddressCity(),
                    'country' => $request->getShipperAddressCountryCode(),
                ],
                'email' => Mage::getStoreConfig('trans_email/ident_general/email', $shipment->getStoreId()),
                'phoneCountryCode' => $pickupPhone[0] ?: $defaultPhoneCode,
                'phoneNumber' => $pickupPhone[1],
                'pickupWindows' => $this->timeslots->getPickupWindows($methodInfo),
            ],
            'destination' => [
                'name' => $request->getRecipientContactPersonName(),
                'address' => [
                    'streetName' => $request->getRecipientAddressStreet(),
                    'streetNumber' => ',', // TODO: remove when API ready
                    'postalCode' => $request->getRecipientAddressPostalCode(),
                    'city' => $request->getRecipientAddressCity(),
                    'country' => $request->getRecipientAddressCountryCode(),
                ],
                'email' => $request->getRecipientEmail(),
                'phoneCountryCode' => $deliveryPhone[0] ?: $defaultPhoneCode,
                'phoneNumber' => $deliveryPhone[1],
                'deliveryWindow' => $deliveryTimeslotIsKnown ? [
                    'start' => $this->helper->formatApiDateTime($methodInfo['start']),
                    'end' => $this->helper->formatApiDateTime($methodInfo['end']),
                ] : null,
                'bestAvailableWindow' => !$deliveryTimeslotIsKnown,
                'verifications' => $this->getVerifications($shipment),
            ],
            'parcels' => $parcels,
            'product' => $methodInfo['type'] . ($methodInfo['return'] ? '-with-return' : ''),
            'orderReference' => $order->getIncrementId(),
            'courierInstructions' => $order->getPbComment() ?: '',
        );

        $transport = new Varien_Object(array('parameters' => $parameters));
        Mage::dispatchEvent('convert_porterbuddy_create_order_data', array(
            'transport' => $transport,
            'request' => $request,
        ));
        $parameters = $transport->getData('parameters');

        return $parameters;
    }

    /**
     * For passed dates, get new closest timeslot
     *
     * @param array $methodInfo
     * @return array
     * @throws Convert_Porterbuddy_Exception
     */
    public function checkExpiredTimeslot($methodInfo)
    {
        $scheduledDate = new DateTime($methodInfo['start']);
        $currentTime = $this->helper->getCurrentTime();
        if ($currentTime > $scheduledDate) {
            $this->helper->log("Delivery timeslot `{$methodInfo['start']}` expired.", $methodInfo, Zend_Log::ERR);
            // FIXME
            // throw new Convert_Porterbuddy_Exception($this->helper->__('Delivery timeslot %s expired', $methodInfo['start']));
        }
        return $methodInfo;
    }

    /**
     * Creates shipment packages if needed, exports to Porterbuddy API format
     *
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     * @throws Convert_Porterbuddy_Exception
     * @throws Varien_Exception
     */
    public function getParcels(
        Mage_Shipping_Model_Shipment_Request $request,
        Mage_Sales_Model_Order_Shipment $shipment
    ) {
        if (!$shipment->getPackages() || !is_array($shipment->getPackages())) {
            $packages = $this->packager->createPackages($request);
            $shipment->setPackages($packages);
            $this->helper->log('Automatically created packages.', null, Zend_Log::NOTICE);
        } else {
            $this->helper->log('Packages already created.', null, Zend_Log::NOTICE);
        }

        // when packages are created in observer, auto serialization is already missed. serialize manually
        $shipment->setPackages(serialize($shipment->getPackages()));

        $parcels = $this->packager->getParcelsFromPackages($shipment);
        if (!$parcels) {
            $this->helper->log(
                "Error preparing order data for shipment `{$shipment->getId()}`, empty parcels",
                [
                    'packages' => $shipment->getPackages()
                ],
                Zend_Log::ERR
            );
            throw new Convert_Porterbuddy_Exception($this->helper->__('There was an error preparing parcels.'));
        }
        return $parcels;
    }

    /**
     * Returns verification options based on shipment packages params, products and default settings
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     */
    public function getVerifications(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $order = $shipment->getOrder();
        $packages = $shipment->getPackages();
        if ($packages && is_scalar($packages)) {
            $packages = unserialize($packages);
        }

        $verifications = array();
        $verifications['leaveAtDoorstep'] = (bool)$order->getPbLeaveDoorstep();

        // when creating packages manually, admin selects signature confirmation.
        // if at least one package requires signature. treat whole order requiring as well
        $requireSignature = null;
        foreach ($packages as $package) {
            $package = new Varien_Object($package);
            $value = $package->getData('params/delivery_confirmation');
            if (null !== $value) {
                $requireSignature = $requireSignature || $value;
            }
        }

        if (null !== $requireSignature) {
            $verifications['requireSignature'] = $requireSignature;
        } else {
            // signature requirement not set explicitly in packages, check products
            $verifications['requireSignature'] = $this->isVerificationRequired(
                $shipment,
                $this->helper->isRequireSignatureDefault(),
                $this->helper->getRequireSignatureAttr()
            );
        }

        $minAge = $this->helper->getMinAgeCheckDefault();
        $minAgeAttr = $this->helper->getMinAgeCheckAttr();
        if ($minAgeAttr) {
            /** @var Mage_Sales_Model_Order_Shipment_Item $item */
            foreach ($shipment->getAllItems() as $item) {
                $product = $item->getOrderItem()->getProduct();
                $value = $product->getData($minAgeAttr);
                if ($product->hasData($minAgeAttr) && is_numeric($value) && $value > 0) {
                    $minAge = $minAge ? min($minAge, $value) : $value;
                }
            }
        }
        if ($minAge) {
            $verifications['minimumAgeCheck'] = $minAge;
        }

        $verifications['idCheck'] = $this->isVerificationRequired(
            $shipment,
            $this->helper->isIdCheckDefault(),
            $this->helper->getIdCheckAttr()
        );

        $verifications['onlyToRecipient'] = $this->isVerificationRequired(
            $shipment,
            $this->helper->isOnlyToRecipientDefault(),
            $this->helper->getOnlyToRecipientAttr()
        );

        return $verifications;
    }

    /**
     * Checks if verification is required
     *
     * - always required if set by default
     * - required if product attribute is set and at least one product in order is marked true
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param int $default
     * @param string|null $attributeCode
     * @return bool
     */
    protected function isVerificationRequired(Mage_Sales_Model_Order_Shipment $shipment, $default, $attributeCode)
    {
        $result = $default;

        if (!$result && $attributeCode) {
            // true if at least one product is true
            /** @var Mage_Sales_Model_Order_Shipment_Item $item */
            foreach ($shipment->getAllItems() as $item) {
                $product = $item->getOrderItem()->getProduct();
                if ($product->getData($attributeCode)) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }
}
