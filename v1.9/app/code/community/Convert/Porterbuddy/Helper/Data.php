<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ACTIVE = 'carriers/cnvporterbuddy/active';
    const XML_PATH_TITLE = 'carriers/cnvporterbuddy/title';
    const XML_PATH_DESCRIPTION = 'carriers/cnvporterbuddy/description';
    const XML_PATH_ASAP_NAME = 'carriers/cnvporterbuddy/asap_name';
    const XML_PATH_AUTO_CREATE_SHIPMENT = 'carriers/cnvporterbuddy/auto_create_shipment';
    const XML_PATH_CREATE_SHIPMENT_TIMEOUT = 'carriers/cnvporterbuddy/create_shipment_timeout';
    const XML_PATH_DEFAULT_CONTACT_ADMIN = 'carriers/cnvporterbuddy/default_contact_admin';
    const XML_PATH_API_MODE = 'carriers/cnvporterbuddy/api_mode';
    const XML_PATH_DEVELOPMENT_API_URL = 'carriers/cnvporterbuddy/development_api_url';
    const XML_PATH_DEVELOPMENT_API_KEY = 'carriers/cnvporterbuddy/development_api_key';
    const XML_PATH_DEVELOPMENT_API_SECRET = 'carriers/cnvporterbuddy/development_api_secret';
    const XML_PATH_TESTING_API_URL = 'carriers/cnvporterbuddy/testing_api_url';
    const XML_PATH_TESTING_API_KEY = 'carriers/cnvporterbuddy/testing_api_key';
    const XML_PATH_TESTING_API_SECRET = 'carriers/cnvporterbuddy/testing_api_secret';
    const XML_PATH_PRODUCTION_API_URL = 'carriers/cnvporterbuddy/production_api_url';
    const XML_PATH_PRODUCTION_API_KEY = 'carriers/cnvporterbuddy/production_api_key';
    const XML_PATH_PRODUCTION_API_SECRET = 'carriers/cnvporterbuddy/production_api_secret';
    const XML_PATH_INBOUND_TOKEN = 'carriers/cnvporterbuddy/inbound_token';
    const XML_PATH_MAPS_API_KEY = 'carriers/cnvporterbuddy/maps_api_key';
    const XML_PATH_MAPS_ZOOM = 'carriers/cnvporterbuddy/maps_zoom';
    const XML_PATH_POPUP_TITLE = 'carriers/cnvporterbuddy/popup_title';
    const XML_PATH_POPUP_DESCRIPTION = 'carriers/cnvporterbuddy/popup_description';
    const XML_PATH_POPUP_ADDRESS_TEXT = 'carriers/cnvporterbuddy/popup_address_text';
    const XML_PATH_DEFAULT_PHONE_CODE = 'carriers/cnvporterbuddy/default_phone_code';
    const XML_PATH_REFRESH_TOKEN_TIME = 'carriers/cnvporterbuddy/refresh_token_before_expiration_time';
    const XML_PATH_RETURN_ENABLED = 'carriers/cnvporterbuddy/return_enabled';
    const XML_PATH_DAYS_AHEAD = 'carriers/cnvporterbuddy/days_ahead';
    const XML_PATH_TIMESLOT_CUTOFF = 'carriers/cnvporterbuddy/timeslot_cutoff';
    const XML_PATH_TIMESLOT_WINDOW = 'carriers/cnvporterbuddy/timeslot_window';
    const XML_PATH_ASAP_CUTOFF = 'carriers/cnvporterbuddy/asap_cutoff';

    const XML_PATH_DISCOUNT_TYPE = 'carriers/cnvporterbuddy/discount_type';
    const XML_PATH_DISCOUNT_SUBTOTAL = 'carriers/cnvporterbuddy/discount_subtotal';
    const XML_PATH_DISCOUNT_AMOUNT = 'carriers/cnvporterbuddy/discount_amount';
    const XML_PATH_DISCOUNT_PERCENT = 'carriers/cnvporterbuddy/discount_percent';

    const XML_PATH_HOURS_MON = 'carriers/cnvporterbuddy/hours_mon';
    const XML_PATH_HOURS_TUE = 'carriers/cnvporterbuddy/hours_tue';
    const XML_PATH_HOURS_WED = 'carriers/cnvporterbuddy/hours_wed';
    const XML_PATH_HOURS_THU = 'carriers/cnvporterbuddy/hours_thu';
    const XML_PATH_HOURS_FRI = 'carriers/cnvporterbuddy/hours_fri';
    const XML_PATH_HOURS_SAT = 'carriers/cnvporterbuddy/hours_sat';
    const XML_PATH_HOURS_SUN = 'carriers/cnvporterbuddy/hours_sun';

    const XML_PATH_RETURN_TEXT = 'carriers/cnvporterbuddy/return_text';
    const XML_PATH_RETURN_SHORT_TEXT = 'carriers/cnvporterbuddy/return_short_text';
    const XML_PATH_RETURN_PRICE = 'carriers/cnvporterbuddy/return_price';
    const XML_PATH_LEAVE_DOORSTEP_TEXT = 'carriers/cnvporterbuddy/leave_doorstep_text';
    const XML_PATH_COMMENT_TEXT = 'carriers/cnvporterbuddy/comment_text';
    const XML_PATH_CONTAINERS = 'carriers/cnvporterbuddy/containers';
    const XML_PATH_WEIGHT_UNIT = 'carriers/cnvporterbuddy/weight_unit';
    const XML_PATH_DIMENSION_UNIT = 'carriers/cnvporterbuddy/dimension_unit';
    const XML_PATH_DEFAULT_PRODUCT_WEIGHT = 'carriers/cnvporterbuddy/default_product_weight';
    const XML_PATH_HEIGHT_ATTRIBUTE = 'carriers/cnvporterbuddy/height_attribute';
    const XML_PATH_WIDTH_ATTRIBUTE = 'carriers/cnvporterbuddy/width_attribute';
    const XML_PATH_LENGTH_ATTRIBUTE = 'carriers/cnvporterbuddy/length_attribute';
    const XML_PATH_DEFAULT_PRODUCT_HEIGHT = 'carriers/cnvporterbuddy/default_product_height';
    const XML_PATH_DEFAULT_PRODUCT_WIDTH = 'carriers/cnvporterbuddy/default_product_width';
    const XML_PATH_DEFAULT_PRODUCT_LENGTH = 'carriers/cnvporterbuddy/default_product_length';
    const XML_PATH_DEFAULT_LOCATION = 'carriers/cnvporterbuddy/default_location';
    const XML_PATH_DEFAULT_ZOOM = 'carriers/cnvporterbuddy/default_zoom';
    const XML_PATH_DEBUG = 'carriers/cnvporterbuddy/debug';
    const XML_PATH_MAPS_STORE_LOCATION = 'shipping/origin/location';

    /**
     * @return bool
     */
    public function getActive()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ACTIVE);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return Mage::getStoreConfig(self::XML_PATH_TITLE);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return Mage::getStoreConfig(self::XML_PATH_DESCRIPTION);
    }

    /**
     * @return string
     */
    public function getAsapName()
    {
        return Mage::getStoreConfig(self::XML_PATH_ASAP_NAME);
    }

    /**
     * @return string
     */
    public function getDefaultContactAdmin()
    {
        return Mage::getStoreConfig(self::XML_PATH_DEFAULT_CONTACT_ADMIN);
    }

    /**
     * @return bool
     */
    public function getAutoCreateShipment()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_AUTO_CREATE_SHIPMENT);
    }

    /**
     * @return int
     */
    public function getCreateShipmentTimeout()
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_CREATE_SHIPMENT_TIMEOUT);
    }

    /**
     * @return STRING
     */
    public function getApiMode()
    {
        return Mage::getStoreConfig(self::XML_PATH_API_MODE);
    }

    /**
     * Porterbuddy API URL with regard to selected API mode
     *
     * @return string
     */
    public function getApiUrl()
    {
        switch ($this->getApiMode()) {
            case Convert_Porterbuddy_Model_Carrier::MODE_PRODUCTION:
                return Mage::getStoreConfig(self::XML_PATH_PRODUCTION_API_URL);
            case Convert_Porterbuddy_Model_Carrier::MODE_TESTING:
                return Mage::getStoreConfig(self::XML_PATH_TESTING_API_URL);
            default:
                return Mage::getStoreConfig(self::XML_PATH_DEVELOPMENT_API_URL);
        }
    }

    /**
     * Porterbuddy API key with regard to selected API mode
     *
     * @return string
     */
    public function getApiKey()
    {
        switch ($this->getApiMode()) {
            case Convert_Porterbuddy_Model_Carrier::MODE_PRODUCTION:
                return Mage::getStoreConfig(self::XML_PATH_PRODUCTION_API_KEY);
            case Convert_Porterbuddy_Model_Carrier::MODE_TESTING:
                return Mage::getStoreConfig(self::XML_PATH_TESTING_API_KEY);
            default:
                return Mage::getStoreConfig(self::XML_PATH_DEVELOPMENT_API_KEY);
        }
    }

    /**
     * Porterbuddy API secret with regard to selected API mode
     *
     * @return string
     */
    public function getApiSecret()
    {
        switch ($this->getApiMode()) {
            case Convert_Porterbuddy_Model_Carrier::MODE_PRODUCTION:
                return Mage::getStoreConfig(self::XML_PATH_PRODUCTION_API_SECRET);
            case Convert_Porterbuddy_Model_Carrier::MODE_TESTING:
                return Mage::getStoreConfig(self::XML_PATH_TESTING_API_SECRET);
            default:
                return Mage::getStoreConfig(self::XML_PATH_DEVELOPMENT_API_SECRET);
        }
    }

    /**
     * Maps API Key
     *
     * @return string
     */
    public function getMapsApiKey()
    {
        return Mage::getStoreConfig(self::XML_PATH_MAPS_API_KEY);
    }

    /**
     * Initial maps zoom
     *
     * @return int
     */
    public function getMapsZoom()
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_MAPS_ZOOM);
    }

    /**
     * Location popup title
     *
     * @return string
     */
    public function getPopupTitle()
    {
        return Mage::getStoreConfig(self::XML_PATH_POPUP_TITLE);
    }

    /**
     * Location popup description
     *
     * @return string
     */
    public function getPopupDescription()
    {
        return Mage::getStoreConfig(self::XML_PATH_POPUP_DESCRIPTION);
    }

    /**
     * Location popup description
     *
     * @return string
     */
    public function getPopupAddressText()
    {
        return Mage::getStoreConfig(self::XML_PATH_POPUP_ADDRESS_TEXT);
    }

    /**
     * Default phone code
     *
     * @return string
     */
    public function getDefaultPhoneCode()
    {
        return Mage::getStoreConfig(self::XML_PATH_DEFAULT_PHONE_CODE);
    }

    /**
     * Inbound access token for requests from Porterbuddy
     *
     * @return string
     */
    public function getInboundToken()
    {
        return Mage::getStoreConfig(self::XML_PATH_INBOUND_TOKEN);
    }

    /**
     * Refresh token when time to expiration is less than this value (about to expire)
     *
     * @return string
     */
    public function getRefreshTokenTime()
    {
        return Mage::getStoreConfig(self::XML_PATH_REFRESH_TOKEN_TIME);
    }

    /**
     * Returns default map location
     *
     * Used to center map when setting store location initially when we have no info at all
     *
     * @return array|null lat,lng
     */
    public function getDefaultLocation()
    {
        return $this->formatLocation(Mage::getStoreConfig(self::XML_PATH_DEFAULT_LOCATION));
    }

    /**
     * Returns default map zoom
     *
     * Used to zoom map when setting store location initially when we have no info at all
     *
     * @return int
     */
    public function getDefaultZoom()
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_DEFAULT_ZOOM);
    }

    /**
     * Whether verbose log is enabled
     *
     * @return bool
     */
    public function getDebug()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_DEBUG);
    }

    /**
     * @param string $dayOfWeek
     * @return string[]
     * @throws Convert_Porterbuddy_Exception
     */
    public function getOpenHours($dayOfWeek)
    {
        $map = array(
            'mon' => self::XML_PATH_HOURS_MON,
            'tue' => self::XML_PATH_HOURS_TUE,
            'wed' => self::XML_PATH_HOURS_WED,
            'thu' => self::XML_PATH_HOURS_THU,
            'fri' => self::XML_PATH_HOURS_FRI,
            'sat' => self::XML_PATH_HOURS_SAT,
            'sun' => self::XML_PATH_HOURS_SUN,
        );
        if (!isset($map[$dayOfWeek])) {
            throw new Convert_Porterbuddy_Exception($this->__('Incorrect day of week `%s`.', $dayOfWeek));
        }

        $value = Mage::getStoreConfig($map[$dayOfWeek]);
        $parts = explode(',', $value);

        if (2 !== count($parts)) {
            // no restriction
            return array('00:00', '24:00');
        }
        return $parts;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address|Mage_Sales_Model_Order_Address $shippingAddress
     * @return string
     */
    public function formatAddress(Mage_Customer_Model_Address_Abstract $shippingAddress)
    {
        // format address without customer name part
        /** @var Mage_Sales_Model_Quote_Address $address */
        $address = Mage::getModel('sales/quote_address');
        $address
            ->setStreetFull($shippingAddress->getStreet1()) // only first line, second usually has Company/Name
            ->setCity($shippingAddress->getCity())
            ->setPostcode($shippingAddress->getPostcode())
            ->setRegionId($shippingAddress->getRegionId())
            ->setCountryId($shippingAddress->getCountryId());

        $result = $address->format('oneline');
        $result = strtr($result, array(
            "\n" => '',
            "\r" => '',
            '  ' => ' ',
        ));
        $result = trim($result, ', ');

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order|Mage_Sales_Model_Order_Shipment|null $context
     * @return array|null lat,lng
     */
    public function getStoreLocation(
        Mage_Core_Model_Abstract $context = null
    ) {
        $storeId = null; // current
        if ($context) {
            $storeId = $context->getStoreId();
        }
        $location = Mage::getStoreConfig(self::XML_PATH_MAPS_STORE_LOCATION, $storeId);

        // multi warehousing may want to choose between multiple locations
        $transport = new Varien_Object(array('location' => $location));
        Mage::dispatchEvent('convert_porterbuddy_store_location', array(
            'context' => $context,
            'transport' => $transport,
        ));
        $location = $transport->getData('location');

        return $this->formatLocation($location);
    }

    /**
     * Performs string comparison using timing attack resistant approach.
     *
     * @see http://codereview.stackexchange.com/questions/13512
     * @param string $expected string to compare.
     * @param string $actual user-supplied string.
     * @return bool whether strings are equal.
     */
    public function compareString($expected, $actual)
    {
        $expected .= "\0";
        $actual .= "\0";
        $expectedLength = mb_strlen($expected, '8bit');
        $actualLength = mb_strlen($actual, '8bit');
        $diff = $expectedLength - $actualLength;
        for ($i = 0; $i < $actualLength; $i++) {
            $diff |= (ord($actual[$i]) ^ ord($expected[$i % $expectedLength]));
        }
        return $diff === 0;
    }

    /**
     * Generates random string for token
     *
     * @return string
     */
    public function generateToken()
    {
        $length = Mage_Oauth_Model_Token::LENGTH_TOKEN;
        if (function_exists('openssl_random_pseudo_bytes')) {
            // use openssl lib if it is install. It provides a better randomness
            $bytes = openssl_random_pseudo_bytes(ceil($length/2), $strong);
            $hex = bin2hex($bytes); // hex() doubles the length of the string
            $randomString = substr($hex, 0, $length); // we truncate at most 1 char if length parameter is an odd number
        } else {
            // fallback to mt_rand() if openssl is not installed
            /** @var $helper Mage_Core_Helper_Data */
            $helper = Mage::helper('core');
            $randomString = $helper->getRandomString(
                $length, Mage_Core_Helper_Data::CHARS_DIGITS . Mage_Core_Helper_Data::CHARS_LOWERS
            );
        }

        return $randomString;
    }

    /**
     * @param string $location lat,lng
     * @return array|null lat,lng
     */
    public function formatLocation($location)
    {
        $parts = explode(',', $location);
        $parts = array_map('trim', $parts);
        if (2 == count($parts) && is_numeric($parts[0]) && is_numeric($parts[1])) {
            return array(
                'lat' => (float)$parts[0],
                'lng' => (float)$parts[1],
            );
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getContainers() 
    {
        $containers = array();
        foreach (Mage::getStoreConfig(self::XML_PATH_CONTAINERS) as $row) {
            if (isset($row['code']) && isset($row['name']) && strlen($row['code']) && strlen($row['name'])) {
                $containers[$row['code']] = array(
                    'code' => trim($row['code']),
                    'name' => trim($row['name']),
                    'weight' => trim($row['weight']),
                    'length' => trim($row['length']),
                    'width' => trim($row['width']),
                    'height' => trim($row['height']),
                );
            }
        }
        return $containers;
    }
    
    /**
     * @return string
     */
    public function getWeightUnit() 
    {
        return Mage::getStoreConfig(self::XML_PATH_WEIGHT_UNIT);
    }
    
    /**
     * @return string
     */
    public function getDimensionUnit() 
    {
        return Mage::getStoreConfig(self::XML_PATH_DIMENSION_UNIT);
    }

    /**
     * @return float
     */
    public function getDefaultProductWeight()
    {
        return (float)trim(Mage::getStoreConfig(self::XML_PATH_DEFAULT_PRODUCT_WEIGHT));
    }

    /**
     * @return float
     */
    public function getDefaultProductHeight() 
    {
        return (float)trim(Mage::getStoreConfig(self::XML_PATH_DEFAULT_PRODUCT_HEIGHT));
    }
    
    /**
     * @return float
     */
    public function getDefaultProductWidth() 
    {
        return (float)trim(Mage::getStoreConfig(self::XML_PATH_DEFAULT_PRODUCT_WIDTH));
    }

    /**
     * @return float
     */
    public function getDefaultProductLength() 
    {
        return (float)trim(Mage::getStoreConfig(self::XML_PATH_DEFAULT_PRODUCT_LENGTH));
    }

    /**
     * @return string
     */
    public function getHeightAttribute() 
    {
        return Mage::getStoreConfig(self::XML_PATH_HEIGHT_ATTRIBUTE);
    }
    
    /**
     * @return string
     */
    public function getWidthAttribute() 
    {
        return Mage::getStoreConfig(self::XML_PATH_WIDTH_ATTRIBUTE);
    }
    
    /**
     * @return string
     */
    public function getLengthAttribute() 
    {
        return Mage::getStoreConfig(self::XML_PATH_LENGTH_ATTRIBUTE);
    }

    /**
     * @return int
     */
    public function getDaysAhead()
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_DAYS_AHEAD);
    }

    /**
     * Timeslot cutoff, hours
     *
     * @return int
     */
    public function getTimeslotCutoff()
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_TIMESLOT_CUTOFF);
    }

    /**
     * ASAP cutoff, hours
     *
     * @return float
     */
    public function getAsapCutoff()
    {
        $value = Mage::getStoreConfig(self::XML_PATH_ASAP_CUTOFF);
        return Mage::app()->getLocale()->getNumber($value);
    }

    /**
     * Discount type as defined in Convert_Porterbuddy_Model_Carrier::DISCOUNT_TYPE_*
     *
     * @return string
     */
    public function getDiscountType()
    {
        return Mage::getStoreConfig(self::XML_PATH_DISCOUNT_TYPE);
    }

    /**
     * Min order subtotal to apply discount, in base currency
     *
     * @return float
     */
    public function getDiscountSubtotal()
    {
        $value = Mage::getStoreConfig(self::XML_PATH_DISCOUNT_SUBTOTAL);
        return Mage::app()->getLocale()->getNumber($value);
    }

    /**
     * Fixed discount amount, in base currency
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        $value = Mage::getStoreConfig(self::XML_PATH_DISCOUNT_AMOUNT);
        return Mage::app()->getLocale()->getNumber($value);
    }

    /**
     * Discount percent
     *
     * @return int
     */
    public function getDiscountPercent()
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_DISCOUNT_PERCENT);
    }

    /**
     * @return int
     */
    public function getTimeslotWindow()
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_TIMESLOT_WINDOW);
    }

    /**
     * @return string
     */
    public function getReturnEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_RETURN_ENABLED);
    }

    /**
     * @return string
     */
    public function getReturnText()
    {
        return Mage::getStoreConfig(self::XML_PATH_RETURN_TEXT);
    }

    /**
     * @return string
     */
    public function getReturnShortText()
    {
        return Mage::getStoreConfig(self::XML_PATH_RETURN_SHORT_TEXT);
    }

    /**
     * @return string
     */
    public function getReturnPrice()
    {
        return Mage::getStoreConfig(self::XML_PATH_RETURN_PRICE);
    }

    /**
     * @return string
     */
    public function getLeaveDoorstepText()
    {
        return Mage::getStoreConfig(self::XML_PATH_LEAVE_DOORSTEP_TEXT);
    }

    /**
     * @return string
     */
    public function getCommentText()
    {
        return Mage::getStoreConfig(self::XML_PATH_COMMENT_TEXT);
    }

    /**
     * Convert Weight to KILOGRAM
     *
     * @param float $weight
     * @param string|null $weightUnit optional, by default from config
     * @return float
     * @throws Convert_Porterbuddy_Exception
     */
    public function convertWeightToKg($weight, $weightUnit = null)
    {
        if (!is_numeric($weight)) {
            $this->log(
                "Weight must be numeric `$weight`.",
                null,
                Zend_Log::ERR
            );
            throw new Convert_Porterbuddy_Exception($this->__('Weight convertation error.'));
        }

        if (!$weightUnit) {
            $weightUnit = $this->getWeightUnit();
        }

        switch ($weightUnit) {
            case Convert_Porterbuddy_Model_Carrier::WEIGHT_KILOGRAM:
                return $weight;
            case Convert_Porterbuddy_Model_Carrier::WEIGHT_GRAM:
                return $weight*1000;
            default:
                $this->log(
                    "Invalid weight unit`$weightUnit`.",
                    null,
                    Zend_Log::ERR
                );
                throw new Convert_Porterbuddy_Exception($this->__('Invalid weight unit.'));
        }
    }

    /**
     * Convert Dimension to MILLIMETER
     *
     * @param float $dimension
     * @return float
     * @throws Convert_Porterbuddy_Exception
     */
    public function convertDimensionToCm($dimension)
    {
        if (!is_numeric($dimension)) {
            $this->log(
                "Dimension must be numeric `$dimension`.",
                null,
                Zend_Log::ERR
            );
            throw new Convert_Porterbuddy_Exception($this->__('Dimension convertation error.'));
        }

        $dimensionUnit = $this->getDimensionUnit();
        switch ($dimensionUnit) {
            case Convert_Porterbuddy_Model_Carrier::UNIT_MILLIMETER:
                return $dimension/10;
            case Convert_Porterbuddy_Model_Carrier::UNIT_CENTIMETER:
                return $dimension;
            default:
                $this->log(
                    "Invalid dimension unit`$dimensionUnit`.",
                    null,
                    Zend_Log::ERR
                );
                throw new Convert_Porterbuddy_Exception($this->__('Invalid dimension unit.'));
        }
    }

    /**
     * Formats errors with field names, adds hints to help fix them
     *
     * @param array $responseErrors
     * @return array
     */
    public function formatErrors(array $responseErrors)
    {
        /** @var Mage_Adminhtml_Model_Url $urlModel */
        $urlModel = Mage::getModel('adminhtml/url');

        $generalUrl = $urlModel->getUrl(
            'adminhtml/system_config/edit',
            array('section' => 'general', '_fragment' => 'general_store_information-head')
        );
        $shippingUriginUrl = $urlModel->getUrl(
            'adminhtml/system_config/edit',
            array('section' => 'shipping', '_fragment' => 'shipping_origin-head')
        );

        $errorDetails = array(
            //'leave_at_doorstep' => 'Just an example',
            'pickup_location_attributes.phone_code' => $this->__(
                'Check %sStore Contact Phone%s',
                '<a href="' . $generalUrl . '" title="' . $this->__('System > Configuration > General > Store Information') . '">',
                '</a>'
            ),
            'pickup_location_attributes.latitude' => $this->__(
                'Check %sShipping Origin Location%s',
                '<a href="' . $shippingUriginUrl . '" title="' . $this->__('System > Configuration > Shipping Settings > Origin > Location') . '">',
                '</a>'
            ),
            'pickup_location_attributes.longitude' => $this->__(
                'Check %sShipping Origin Location%s',
                '<a href="' . $shippingUriginUrl . '" title="' . $this->__('System > Configuration > Shipping Settings > Origin > Location') . '">',
                '</a>'
            ),
        );
        $transport = new Varien_Object(array('error_details' => $errorDetails));
        Mage::dispatchEvent('convert_porterbuddy_format_create_shipment_error_details', array(
            'transport' => $transport,
            'response_errors' => $responseErrors,
        ));
        $errorDetails = $transport->getData('error_details');

        $result = array();

        $flatErrors = $this->flatten($responseErrors);
        foreach ($flatErrors as $path => $messages) {
            $label = $this->formatLabel($path);
            $result[$path] = $label . ': ' . implode('; ', $messages);
        }

        // explain tricky parts
        $additionalErrors = array();
        foreach ($flatErrors as $path => $messages) {
            if (isset($errorDetails[$path])) {
                $additionalErrors["details.$path"] = $errorDetails[$path];
            }
        }
        $additionalErrors = array_unique($additionalErrors); // e.g. same errors for missing latitude and longitude

        $result = array_merge($result, $additionalErrors);

        return $result;
    }

    /**
     * Recursively flattens multidimensional array joining nested keys
     *
     * @param array $data
     * @param array $errors
     * @param string $prefix
     * @return array
     */
    public function flatten(array $data, &$errors = array(), $prefix = '')
    {
        foreach ($data as $section => $values) {
            if ($this->isAssoc($values)) {
                $this->flatten($values, $errors, "$section.");
            } else {
                $errors[$prefix . $section] = $values;
            }
        }
        return $errors;
    }

    /**
     * Checks if all keys in array are numeric or it's an associative array
     *
     * @param array $array
     * @return bool
     */
    public function isAssoc(array $array)
    {
        $keys = array_keys($array);
        return $keys !== array_keys($keys);
    }

    /**
     * Formats human label from path like field_one.field_two -> Field One - Field Two, translates each part
     *
     * @param $path
     * @return string
     */
    public function formatLabel($path)
    {
        // make label, translate each part
        $parts = explode('.', $path);
        $label = implode(' - ', array_map(function($label) {
            $label = str_replace('_', ' ', $label);
            $label = str_replace('.', ' - ', $label);
            $label = ucfirst($label);
            return $this->__($label);
        }, $parts));

        return $label;
    }

    /**
     * Separates out phone code from number
     *
     * @param string $phone
     * @return array
     */
    public function splitPhoneCodeNumber($phone)
    {
        $phone = str_replace(' ', '', $phone);
        $phone = trim($phone);

        if (!strlen($phone)) {
            return array('', '');
        }

        // +47 12 34 56 => (+47) (123456)
        if (preg_match('/^\+(\d{2})(.+)/', $phone, $matches)) {
            $code = $matches[1];
            $number = trim($matches[2]);
            return array("+$code", $number);
        }

        // nothing matched
        return array('', $phone);
    }

    /**
     * @param string $methodCode
     * @return array
     */
    public function parseMethod($methodCode)
    {
        $result = array(
            'type' => null,
            'date' => null,
            'timeslotLength' => null,
            'return' => false,
        );

        $parts = explode('_', $methodCode);
        $type = array_shift($parts);
        if (Convert_Porterbuddy_Model_Carrier::CODE == $type) {
            // skip carrier code if present
            $type = array_shift($parts);
        }

        $result['type'] = $type;
        if (Convert_Porterbuddy_Model_Carrier::METHOD_ASAP == $type) {
            $result['return'] = ('return' === array_shift($parts));
        } elseif (Convert_Porterbuddy_Model_Carrier::METHOD_SCHEDULED == $type) {
            $result['date'] = array_shift($parts);
            $result['timeslotLength'] = array_shift($parts);
            $result['return'] = ('return' === array_shift($parts));
        }

        if (is_numeric($result['timeslotLength'])) {
            $result['timeslotLength'] = (int)$result['timeslotLength'];
        }

        return $result;
    }

    /**
     * Current time in UTC timezone
     *
     * @return DateTime
     */
    public function getCurrentTime()
    {
        return new DateTime();
    }

    /**
     * Returns local timezone
     *
     * @return DateTimeZone
     */
    public function getTimezone()
    {
        $configTimezone = Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
        return new DateTimeZone($configTimezone);
    }

    /**
     * Logs to own file
     *
     * Exception stack trace is saved separately. Log is forced for all exceptions and if level is at least NOTICE
     * When debug flat is enabled, everything is always logged.
     *
     * @param string|Exception $message
     * @param array|null $data optional
     * @param int $level optional
     */
    public function log($message, array $data = null, $level = Zend_Log::DEBUG)
    {
        $forceLog = $this->getDebug() || ($level <= Zend_Log::NOTICE);
        if ($message instanceof Exception) {
            $forceLog = true;
            $level = Zend_Log::ERR;

            Mage::logException($message);
            Mage::log($message->__toString(), $level, 'shipping_porterbuddy_exception.log', $forceLog);
            // log only message to main log
            $message = $message->getMessage();
        }

        if ($data && $data = array_filter($data)) {
            $message .= ' ' . print_r($data, true);
        }

        Mage::log($message, $level, 'shipping_porterbuddy.log', $forceLog);
    }
}
