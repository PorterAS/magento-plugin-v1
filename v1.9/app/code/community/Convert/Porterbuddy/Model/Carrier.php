<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    const CODE = 'cnvporterbuddy';

    const METHOD_ASAP = 'asap';
    const METHOD_SCHEDULED = 'scheduled';

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

    protected $_code = self::CODE;

    /**
     * @var Convert_Porterbuddy_Model_Api
     */
    protected $api;

    /**
     * @var Convert_Porterbuddy_Model_Geocoder
     */
    protected $geocoder;

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
     * @param array|null $data
     * @param Convert_Porterbuddy_Model_Api|null $api
     * @param Convert_Porterbuddy_Model_Geocoder|null $geocoder
     * @param Convert_Porterbuddy_Helper_Data|null $helper
     * @param Convert_Porterbuddy_Model_Packager|null $packager
     * @param Convert_Porterbuddy_Model_Timeslots|null $timeslots
     */
    public function __construct(
        array $data = null, // for getModel to work
        Convert_Porterbuddy_Model_Api $api = null,
        Convert_Porterbuddy_Model_Geocoder $geocoder = null,
        Convert_Porterbuddy_Helper_Data $helper = null,
        Convert_Porterbuddy_Model_Packager $packager = null,
        Convert_Porterbuddy_Model_Timeslots $timeslots = null
    ) {
        parent::__construct($data);

        $this->api = $api ?: Mage::getSingleton('convert_porterbuddy/api');
        $this->geocoder = $geocoder ?: Mage::getSingleton('convert_porterbuddy/geocoder');
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');
        $this->packager = $packager ?: Mage::getSingleton('convert_porterbuddy/packager');
        $this->timeslots = $timeslots ?: Mage::getSingleton('convert_porterbuddy/timeslots');
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

        // Temporary postcode filtering until API supports this
        $supportedPostcodes = preg_split('/(\r\n|\n)/', Mage::getStoreConfig('carriers/cnvporterbuddy/postcodes'));
        $supportedPostcodes = array_filter(array_map('trim', $supportedPostcodes));
        if (!$supportedPostcodes) {
            // not filtered
            return $this;
        }

        $postcode = ltrim(trim($request->getDestPostcode()), '0');
        if (!in_array($postcode, $supportedPostcodes)) {
            $this->helper->log("Postcode `{$request->getDestPostcode()}` is not supported.", null, Zend_Log::WARN);
            return false;
        }

        return $this;
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
            $parameters = $this->prepareDeliveryOptionsData($request);
            $options = $this->api->getDeliveryOptions($parameters);
        } catch (Convert_Porterbuddy_Exception $e) {
            // details logged
            return $result;
        } catch (Exception $e) {
            // other unexpected errors
            $this->helper->log($e);
            return $result;
        }

        $asapOption = null;
        $scheduledOptions = array();

        foreach ($options as $option) {
            if (self::METHOD_ASAP == $option['delivery_type']) {
                if ($this->timeslots->canUseAsap()) {
                    $asapOption = $option;
                }
            } elseif (self::METHOD_SCHEDULED == $option['delivery_type']) {
                $scheduledOptions[] = $option;
            }
        }

        if ($asapOption) {
            $result = $this->addAsapMethod($request, $asapOption, $result);
        }

        foreach ($scheduledOptions as $option) {
            $from = new DateTime($option['deliver_from']);
            $until = new DateTime($option['deliver_until']);
            $timeslots = $this->timeslots->getTimeslots($from, $until);
            foreach ($timeslots as $timeslot) {
                $result = $this->addScheduledMethod($request, $option, $timeslot, $result);
            }
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

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @param array $option
     * @param Convert_Porterbuddy_Model_Rate_Result $result
     * @return Convert_Porterbuddy_Model_Rate_Result
     */
    public function addAsapMethod(
        Mage_Shipping_Model_Rate_Request $request,
        array $option,
        Convert_Porterbuddy_Model_Rate_Result $result
    ) {
        /** @var Mage_Shipping_Model_Rate_Result_Method $method */
        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier(self::CODE);
        $method->setCarrierTitle($this->helper->getTitle());

        $method->setMethod(self::METHOD_ASAP);
        $method->setMethodTitle($this->helper->getAsapName());
        //$method->setMethodDescription($this->helper->getAsapDescription());

        if ($request->getFreeShipping() === true) {
            $shippingPrice = '0.00';
        } else {
            $apiPrice = Mage::app()->getLocale()->getNumber($option['price']); // '10,15 kr' => 10.15
            $rate = $this->getBaseCurrencyRate($request);
            $shippingPrice = $apiPrice * $rate;
        }

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        $result->append($method);

        return $result;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @param array $option
     * @param array $timeslot
     * @param Convert_Porterbuddy_Model_Rate_Result $result
     * @return Convert_Porterbuddy_Model_Rate_Result
     */
    public function addScheduledMethod(
        Mage_Shipping_Model_Rate_Request $request,
        array $option,
        array $timeslot,
        Convert_Porterbuddy_Model_Rate_Result $result
    ) {
        /** @var DateTime $timeslotStart */
        /** @var DateTime $timeslotEnd */
        list($timeslotStart, $timeslotEnd) = $timeslot;

        $timeslotLength = $timeslotStart->diff($timeslotEnd)->h; // last timeslot may be shortened, e.g. 23:00-00:00
        $methodCode = self::METHOD_SCHEDULED . '_' . $timeslotStart->format(DateTime::ATOM) . '_' . $timeslotLength;
        // Local timezone
        $methodTitle = $this->timeslots->formatTimeslot($timeslotStart, $timeslotEnd);

        /** @var Mage_Shipping_Model_Rate_Result_Method $method */
        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier(self::CODE);
        $method->setCarrierTitle($this->helper->getTitle());

        $method->setMethod($methodCode);
        $method->setMethodTitle($methodTitle); // $this->helper->getScheduledName()
        //$method->setMethodDescription($this->helper->getScheduledDescription());

        if ($request->getFreeShipping() === true) {
            $shippingPrice = '0.00';
        } else {
            $apiPrice = Mage::app()->getLocale()->getNumber($option['price']);
            $rate = $this->getBaseCurrencyRate($request);
            $shippingPrice = $apiPrice * $rate;
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
    public function getBaseCurrencyRate(Mage_Shipping_Model_Rate_Request $request)
    {
        if (null === $this->baseCurrencyRate) {
            // TODO: throw error if base currency rate is not defined
            $responseCurrencyCode = 'NOK';
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
        if ($request->getBaseSubtotalInclTax() < $discountSubtotal) {
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
                    $this->helper->log(
                        sprintf(
                            'Applying fixed discount amount to `%s`: `%s` - `%s` = `%s`.',
                            $method->getMethod(),
                            $method->getPrice(),
                            $discountAmount,
                            $price
                        ),
                        null,
                        Zend_Log::NOTICE
                    );
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
                    $this->helper->log(
                        sprintf(
                            'Applying discount percent to `%s`: `%s` - `%s`%% = `%s`.',
                            $method->getMethod(),
                            $method->getPrice(),
                            $discountPercent,
                            $price
                        ),
                        null,
                        Zend_Log::NOTICE
                    );
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
     * @throws Mage_Core_Exception
     */
    public function requestToShipment(Mage_Shipping_Model_Shipment_Request $request)
    {
        $result = new Varien_Object();

        $shipment = $request->getOrderShipment();

        // @see Mage_Shipping_Model_Shipping::requestToShipment - abort if missing required params
        $storeLocation = $this->helper->getStoreLocation($shipment);
        if (!$storeLocation) {
            $message = $this->helper->__('Insufficient information to create shipping label(s). Please verify your Shipping Settings - store location must be set.');
            throw new Convert_Porterbuddy_Exception($message);
        }

        $this->helper->log('requestToShipment - start.', array(
            'shipment_id' => $shipment->getId(),
        ), Zend_Log::NOTICE);

        // mark it processed to disable auto creating label, we already processed it
        $shipment->setIsPorterbuddySent(true);

        if (!$shipment->getPackages() || !is_array($shipment->getPackages())) {
            $this->packager->createPackages($request);
            $this->helper->log('Automatically created packages.', null, Zend_Log::NOTICE);
        } else {
            $this->helper->log('Packages already created.', null, Zend_Log::NOTICE);
        }

        $packages = $shipment->getPackages();

        if (count($packages) > 1) {
            $error = $this->helper->__('Only one package must be created.');
            $result->setErrors($error);
            $this->helper->log("requestToShipment error - $error", array(
                'shipment_id' => $shipment->getId(),
                'packages' => $packages,
            ), Zend_Log::ERR);
            //return $result;
            throw new Convert_Porterbuddy_Exception($error);
        }

        // when packages are created in observer, auto serialization is already missed. serialize manually
        $shipment->setPackages(serialize($packages));

        try {
            $parameters = $this->prepareCreateOrderData($request);
            $response = $this->api->createOrder($parameters);
        } catch (Convert_Porterbuddy_Exception $e) {
            // details logged
            $result->setErrors($e->getMessage());
            //return $result;
            throw $e;
        } catch (Exception $e) {
            // other unexpected errors
            $this->helper->log($e);
            $result->setErrors($e->getMessage());
            //return $result;
            throw $e;
        }

        $shipment->addComment($this->helper->__('Porterbuddy shipment has been ordered.'));

        // Magento requires returning pairs shipping label-tracking number. As we don't support actual labels yet,
        // we can't provide tracking numbers by standard mechanism either. So we will assign them manually to shipment

        $trackingNumber = $response['parcels_attributes'][0]['id'];
        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
        $track = Mage::getModel('sales/order_shipment_track');
        $track
            ->setNumber($trackingNumber)
            ->setCarrierCode($this->getCarrierCode())
            ->setTitle($this->helper->getTitle());
        $shipment->addTrack($track);

        // TODO: store response details somewhere

        $result->setInfo(array()); // mark as success

        $this->helper->log('requestToShipment - success.', array(
            'shipment_id' => $shipment->getId(),
        ), Zend_Log::NOTICE);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedMethods()
    {
        return array(
            self::METHOD_ASAP => $this->helper->getAsapName(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerTypes(Varien_Object $params = null)
    {   
        $containerTypes = array();
        foreach ($this->helper->getContainers() as $code => $container) {
            $containerTypes[$code] = $container['name'] . ' - ' . $this->helper->__(
                'max %s kg, %s cm',
                $container['weight'],
                $container['length'] . 'x' . $container['width'] . 'x' . $container['height']
            );
        }

        return $containerTypes;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return array
     */
    public function prepareDeliveryOptionsData(Mage_Shipping_Model_Rate_Request $request)
    {
        // TODO: sender and receiver addresses, packages number and dimensions
        // TODO: don't send non-working days
        $params = array();

        $daysAhead = $this->helper->getDaysAhead();
        if ($daysAhead) {
            $date = new DateTime('today');
            do {
                $params['dates'][] = $date->format('Y-m-d');
                $date->modify('+1 day');
            } while ($daysAhead-- > 0);
        }

        $transport = new Varien_Object(array('params' => $params));
        Mage::dispatchEvent('convert_porterbuddy_delivery_options_data', array(
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
     * @throws Exception
     */
    protected function prepareCreateOrderData(Mage_Shipping_Model_Shipment_Request $request)
    {
        $shipment = $request->getOrderShipment();
        $order = $shipment->getOrder();
        $shippingAddress = $order->getShippingAddress();

        if (!$request->getRecipientEmail()) {
            $request->setRecipientEmail($order->getCustomerEmail());
        }

        // only single non-empty package is assumed
        $packages = $shipment->getPackages();
        if ($packages && is_scalar($packages)) {
            $packages = unserialize($packages);
        }
        $package = new Varien_Object(reset($packages));

        $defaultPhoneCode = $this->helper->getDefaultPhoneCode();
        $pickupPhone = $this->helper->splitPhoneCodeNumber($request->getShipperContactPhoneNumber());
        $deliveryPhone = $this->helper->splitPhoneCodeNumber($request->getRecipientContactPhoneNumber());

        // ensure coordinates are set
        $deliveryLocation = $this->helper->formatLocation($shippingAddress->getPbLocation());
        if (!$deliveryLocation) {
            // server-side geocoding fallback
            $this->helper->log(
                'Coordinates are empty, falling back to server-side geocoding.',
                array(),
                Zend_Log::WARN
            );
            try {
                $addressLine = $this->helper->formatAddress($shippingAddress);
                $coords = $this->geocoder->geocode($addressLine);
                $shippingAddress
                    ->setPbLocation("{$coords['lat']},{$coords['lng']}")
                    ->setPbUserEdited(false);
                $deliveryLocation = $this->helper->formatLocation($shippingAddress->getPbLocation());
                // TODO: persist in DB separately in case API call fails and shipment doesn't get saved
                $this->helper->log('Successfully obtained coordinates.', array(), Zend_Log::WARN);
            } catch (Exception $e) {
                $this->helper->log($e);
                // cannot proceed without coordinates
                throw $e;
            }
        }

        // ensured non-emptiness earlier
        $storeLocation = $this->helper->getStoreLocation($shipment);
        $methodInfo = $this->helper->parseMethod($request->getShippingMethod());

        // for passed dates, make it ASAP
        if (Convert_Porterbuddy_Model_Carrier::METHOD_SCHEDULED === $methodInfo['type']) {
             $scheduledDate = new DateTime($methodInfo['date']);
             $currentTime = $this->helper->getCurrentTime();
             if ($currentTime > $scheduledDate) {
                 $this->helper->log("Sending shipment for passed scheduled date `{$methodInfo['date']}` as ASAP.");
                 $methodInfo['type'] = Convert_Porterbuddy_Model_Carrier::METHOD_ASAP;
                 $methodInfo['date'] = null;
                 $methodInfo['timeslotLength'] = null;
             }
        }

        $parameters = array(
            'note_for_courier' => $order->getPbComment(),
            'notes' => $this->packager->prepareCourierNote($shipment),
            'leave_at_doorstep' => $order->getPbLeaveDoorstep(),
            'should_generate_voucher_code' => $methodInfo['return'],
            'sender_email' => $request->getShipperEmail(), // admin email
            'recipient_email' => $request->getRecipientEmail(),
            'parcel_type' => $package->getParams('container'),
            'pickup_location_attributes' => array(
                'name' => $request->getShipperContactPersonName(),
                'address1' => $request->getShipperAddressStreet(),
                'postal_code' => $request->getShipperAddressPostalCode(),
                'city' => $request->getShipperAddressCity(),
                'phone_code' => $pickupPhone[0] ?: $defaultPhoneCode,
                'phone_number' => $pickupPhone[1],
                'latitude' => $storeLocation['lat'],
                'longitude' => $storeLocation['lng'],
                'description' => '',
            ),
            'delivery_location_attributes' => array(
                'name' => $request->getRecipientContactPersonName(),
                'address1' => $request->getRecipientAddressStreet(),
                'postal_code' => $request->getRecipientAddressPostalCode(),
                'city' => $request->getRecipientAddressCity(),
                'phone_code' => $deliveryPhone[0] ?: $defaultPhoneCode,
                'phone_number' => $deliveryPhone[1],
                'latitude' => $deliveryLocation['lat'],
                'longitude' => $deliveryLocation['lng'],
                'description' => '',
            ),
            'delivery_type' => $methodInfo['type'],
            'deliver_from' => $methodInfo['date'],
            'delivery_window_length' => $methodInfo['timeslotLength'] ? $methodInfo['timeslotLength'] * 60 : null, // hours -> minutes
        );

        $transport = new Varien_Object(array('parameters' => $parameters));
        Mage::dispatchEvent('convert_porterbuddy_create_order_data', array(
            'transport' => $transport,
            'request' => $request,
        ));
        $parameters = $transport->getData('parameters');

        return $parameters;
    }
}
