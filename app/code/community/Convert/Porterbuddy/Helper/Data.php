<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Helper_Data extends Mage_Core_Helper_Abstract
{
    const API_DATE_FORMAT = \DateTime::ATOM;

    const XML_PATH_ACTIVE = 'carriers/cnvporterbuddy/active';
    const XML_PATH_TITLE = 'carriers/cnvporterbuddy/title';
    const XML_PATH_DESCRIPTION = 'carriers/cnvporterbuddy/description';
    const XML_PATH_ASAP_NAME = 'carriers/cnvporterbuddy/asap_name';
    const XML_PATH_CHOOSE_LATER_NAME = 'carriers/cnvporterbuddy/choose_later_name';
    const XML_PATH_AUTO_CREATE_SHIPMENT = 'carriers/cnvporterbuddy/auto_create_shipment';
    const XML_PATH_API_MODE = 'carriers/cnvporterbuddy/api_mode';
    const XML_PATH_DEVELOPMENT_API_URL = 'carriers/cnvporterbuddy/development_api_url';
    const XML_PATH_DEVELOPMENT_API_KEY = 'carriers/cnvporterbuddy/development_api_key';
    const XML_PATH_TESTING_API_URL = 'carriers/cnvporterbuddy/testing_api_url';
    const XML_PATH_TESTING_API_KEY = 'carriers/cnvporterbuddy/testing_api_key';
    const XML_PATH_PRODUCTION_API_URL = 'carriers/cnvporterbuddy/production_api_url';
    const XML_PATH_PRODUCTION_API_KEY = 'carriers/cnvporterbuddy/production_api_key';
    const XML_PATH_INBOUND_TOKEN = 'carriers/cnvporterbuddy/inbound_token';

    const XML_PATH_POSTCODES = 'carriers/cnvporterbuddy/postcodes';
    const XML_PATH_SHOW_AVAILABILITY = 'carriers/cnvporterbuddy/show_availability';
    const XML_PATH_LOCATION_DISCOVERY = 'carriers/cnvporterbuddy/location_discovery';
    const XML_PATH_LOCATION_LINK_TEMPLATE = 'carriers/cnvporterbuddy/location_link_template';
    const XML_PATH_AVAILABILITY_TEMPLATE = 'carriers/cnvporterbuddy/availability_template';
    const XML_PATH_AVAILABILITY_CHOOSE_POPUP_TITLE = 'carriers/cnvporterbuddy/availability_choose_popup_title';
    const XML_PATH_AVAILABILITY_CHOOSE_POPUP_DESCRIPTION = 'carriers/cnvporterbuddy/availability_choose_popup_description';
    const XML_PATH_AVAILABILITY_TEXT_FETCHING = 'carriers/cnvporterbuddy/availability_text_fetching';
    const XML_PATH_AVAILABILITY_TEXT_CLICK_TO_SEE = 'carriers/cnvporterbuddy/availability_text_click_to_see';
    const XML_PATH_AVAILABILITY_TEXT_POSTCODE_ERROR = 'carriers/cnvporterbuddy/availability_text_postcode_error';
    const XML_PATH_AVAILABILITY_TEXT_OUT_OF_STOCK = 'carriers/cnvporterbuddy/availability_text_delivery_out_of_stock';
    const XML_PATH_AVAILABILITY_TEXT_NO_DATE = 'carriers/cnvporterbuddy/availability_text_delivery_no_date';
    const XML_PATH_AVAILABILITY_AUTO_UPDATE_COMPOSITE = 'carriers/cnvporterbuddy/availability_auto_update_composite';

    const XML_PATH_DEFAULT_PHONE_CODE = 'carriers/cnvporterbuddy/default_phone_code';
    const XML_PATH_PACKAGER_MODE = 'carriers/cnvporterbuddy/packager_mode';
    const XML_PATH_PACKING_TIME = 'carriers/cnvporterbuddy/packing_time';
    const XML_PATH_RETURN_ENABLED = 'carriers/cnvporterbuddy/return_enabled';
    const XML_PATH_TIMESLOT_SELECTION = 'carriers/cnvporterbuddy/timeslot_selection';
    const XML_PATH_DAYS_AHEAD = 'carriers/cnvporterbuddy/days_ahead';
    const XML_PATH_EXTRA_PICKUP_WINDOWS = 'carriers/cnvporterbuddy/pickup_windows_extra';
    const XML_PATH_TIMESLOT_WINDOW = 'carriers/cnvporterbuddy/timeslot_window';

    const XML_PATH_PRICE_OVERRIDE_EXPRESS = 'carriers/cnvporterbuddy/price_override_express';
    const XML_PATH_PRICE_OVERRIDE_DELIVERY = 'carriers/cnvporterbuddy/price_override_delivery';

    const XML_PATH_DISCOUNT_TYPE = 'carriers/cnvporterbuddy/discount_type';
    const XML_PATH_DISCOUNT_SUBTOTAL = 'carriers/cnvporterbuddy/discount_subtotal';
    const XML_PATH_DISCOUNT_AMOUNT = 'carriers/cnvporterbuddy/discount_amount';
    const XML_PATH_DISCOUNT_PERCENT = 'carriers/cnvporterbuddy/discount_percent';

    const XML_PATH_PORTERBUDDY_UNTIL = 'carriers/cnvporterbuddy/porterbuddy_until';
    const XML_PATH_HOURS_MON = 'carriers/cnvporterbuddy/hours_mon';
    const XML_PATH_HOURS_TUE = 'carriers/cnvporterbuddy/hours_tue';
    const XML_PATH_HOURS_WED = 'carriers/cnvporterbuddy/hours_wed';
    const XML_PATH_HOURS_THU = 'carriers/cnvporterbuddy/hours_thu';
    const XML_PATH_HOURS_FRI = 'carriers/cnvporterbuddy/hours_fri';
    const XML_PATH_HOURS_SAT = 'carriers/cnvporterbuddy/hours_sat';
    const XML_PATH_HOURS_SUN = 'carriers/cnvporterbuddy/hours_sun';

    const XML_PATH_REQUIRE_SIGNATURE_DEFAULT = 'carriers/cnvporterbuddy/require_signature_default';
    const XML_PATH_MIN_AGE_CHECK_DEFAULT = 'carriers/cnvporterbuddy/min_age_check_default';
    const XML_PATH_ID_CHECK_DEFAULT = 'carriers/cnvporterbuddy/id_check_default';
    const XML_PATH_ONLY_RECIPIENT_DEFAULT = 'carriers/cnvporterbuddy/only_to_recipient_default';

    const XML_PATH_REQUIRE_SIGNATURE_ATTR = 'carriers/cnvporterbuddy/require_signature_attr';
    const XML_PATH_MIN_AGE_CHECK_ATTR = 'carriers/cnvporterbuddy/min_age_check_attr';
    const XML_PATH_ID_CHECK_ATTR = 'carriers/cnvporterbuddy/id_check_attr';
    const XML_PATH_ONLY_RECIPIENT_ATTR = 'carriers/cnvporterbuddy/only_to_recipient_attr';

    const XML_PATH_RETURN_TEXT = 'carriers/cnvporterbuddy/return_text';
    const XML_PATH_RETURN_SHORT_TEXT = 'carriers/cnvporterbuddy/return_short_text';
    const XML_PATH_RETURN_PRICE = 'carriers/cnvporterbuddy/return_price';
    const XML_PATH_REFRESH_OPTIONS_TIMEOUT = 'carriers/cnvporterbuddy/refresh_options_timeout';
    const XML_PATH_LEAVE_DOORSTEP_TEXT = 'carriers/cnvporterbuddy/leave_doorstep_text';
    const XML_PATH_COMMENT_TEXT = 'carriers/cnvporterbuddy/comment_text';
    const XML_PATH_WEIGHT_UNIT = 'carriers/cnvporterbuddy/weight_unit';
    const XML_PATH_DIMENSION_UNIT = 'carriers/cnvporterbuddy/dimension_unit';
    const XML_PATH_DEFAULT_PRODUCT_WEIGHT = 'carriers/cnvporterbuddy/default_product_weight';
    const XML_PATH_HEIGHT_ATTRIBUTE = 'carriers/cnvporterbuddy/height_attribute';
    const XML_PATH_WIDTH_ATTRIBUTE = 'carriers/cnvporterbuddy/width_attribute';
    const XML_PATH_LENGTH_ATTRIBUTE = 'carriers/cnvporterbuddy/length_attribute';
    const XML_PATH_DEFAULT_PRODUCT_HEIGHT = 'carriers/cnvporterbuddy/default_product_height';
    const XML_PATH_DEFAULT_PRODUCT_WIDTH = 'carriers/cnvporterbuddy/default_product_width';
    const XML_PATH_DEFAULT_PRODUCT_LENGTH = 'carriers/cnvporterbuddy/default_product_length';

    const XML_PATH_ERROR_EMAIL_ENABLED = 'carriers/cnvporterbuddy/error_email_enabled';
    const XML_PATH_ERROR_EMAIL_IDENTITY = 'carriers/cnvporterbuddy/error_email_identity';
    const XML_PATH_ERROR_EMAIL_TEMPLATE = 'carriers/cnvporterbuddy/error_email_template';
    const XML_PATH_ERROR_EMAIL_RECIPIENTS = 'carriers/cnvporterbuddy/error_email_recipients';
    const XML_PATH_ERROR_EMAIL_RECIPIENTS_PORTERBUDDY = 'carriers/cnvporterbuddy/error_email_recipients_porterbuddy';

    const XML_PATH_MAPS_API_KEY = 'carriers/cnvporterbuddy/maps_api_key';
    const XML_PATH_DEBUG = 'carriers/cnvporterbuddy/debug';

    const SHIPMENT_CREATOR_CRON = 'CRON';
    const SHIPMENT_CREATOR_CONFIRMATION = 'CONFIRMATION';

    /**
     * @var Mage_Tax_Helper_Data
     */
    protected $taxHelper;

    public function __construct()
    {
        $this->taxHelper = Mage::helper('tax');
    }

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
    public function getChooseLaterName()
    {
        return Mage::getStoreConfig(self::XML_PATH_CHOOSE_LATER_NAME);
    }

    /**
     * @return bool
     */
    public function getAutoCreateShipment()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_AUTO_CREATE_SHIPMENT);
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
     * Default phone code
     *
     * @return string
     */
    public function getDefaultPhoneCode()
    {
        return Mage::getStoreConfig(self::XML_PATH_DEFAULT_PHONE_CODE);
    }

    /**
     * Packager mode
     *
     * @return string
     */
    public function getPackagerMode()
    {
        return Mage::getStoreConfig(self::XML_PATH_PACKAGER_MODE);
    }

    /**
     * Returns packing time
     *
     * @return float
     */
    public function getPackingTime()
    {
        return (float)Mage::getStoreConfig(self::XML_PATH_PACKING_TIME);
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
     * @return string
     */
    public function showAvailability()
    {
        return Mage::getStoreConfig(self::XML_PATH_SHOW_AVAILABILITY);
    }

    /**
     * @return array
     */
    public function getLocationDiscovery()
    {
        $options = Mage::getStoreConfig(self::XML_PATH_LOCATION_DISCOVERY);
        if ($options) {
            $options = explode(',', $options);
            $options = array_map('trim', $options);
        } else {
            $options = array();
        }
        return $options;
    }

    public function ipDiscoveryEnabled()
    {
        return in_array('ip', $this->getLocationDiscovery());
    }

    /**
     * @return array
     */
    public function getPostcodes()
    {
        $postcodes = Mage::getStoreConfig(self::XML_PATH_POSTCODES);
        $postcodes = preg_split('/(\r\n|\n)/', $postcodes);
        $postcodes = array_map(function($row) {
            // normalize format, remove leading 0, e.g. 0563 = 563
            $row = trim($row);
            $row = ltrim($row, '0');
            return strlen($row) ? $row : false;
        }, $postcodes);
        $postcodes = array_filter($postcodes);

        return $postcodes;
    }

    /**
     * @param string $postcode
     * @return bool
     */
    public function isPostcodeSupported($postcode)
    {
        $postcodes = $this->getPostcodes();
        if (!$postcodes) {
            // no restrictions
            return true;
        }

        // normalize format, remove leading 0, e.g. 0563 = 563
        $postcode = trim($postcode);
        $postcode = ltrim($postcode, '0');

        return in_array($postcode, $postcodes);
    }

    /**
     * Delivery availability template text
     *
     * @return string
     */
    public function getLocationLinkTemplate()
    {
        return Mage::getStoreConfig(self::XML_PATH_LOCATION_LINK_TEMPLATE);
    }

    /**
     * Delivery availability template text
     *
     * @return string
     */
    public function getAvailabilityTemplate()
    {
        return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_TEMPLATE);
    }

    /**
     * @return string
     */
    public function getAvailabilityTextFetching()
    {
        return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_TEXT_FETCHING);
    }

    /**
     * @return string
     */
    public function getAvailabilityTextClickToSee()
    {
        return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_TEXT_CLICK_TO_SEE);
    }

    /**
     * @return string
     */
    public function getAvailabilityTextPostcodeError()
    {
        return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_TEXT_POSTCODE_ERROR);
    }

    /**
     * @return string
     */
    public function getAvailabilityTextOutOfStock()
    {
        return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_TEXT_OUT_OF_STOCK);
    }

    /**
     * @return string
     */
    public function getAvailabilityTextNoDate()
    {
        return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_TEXT_NO_DATE);
    }

    /**
     * Delivery availability change popup title
     *
     * @return string
     */
    public function getAvailabilityChoosePopupTitle()
    {
        return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_CHOOSE_POPUP_TITLE);
    }

    /**
     * Delivery availability change popup description
     *
     * @return string
     */
    public function getAvailabilityChoosePopupDescription()
    {
        return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_CHOOSE_POPUP_DESCRIPTION);
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
     * @return string[]|bool - ['open', 'close'], false if not working
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
            // misconfig, not working
            return false;
        }
        return array(
            'open' => $parts[0],
            'close' => $parts[1],
        );
    }

    /**
     * @return int
     */
    public function getPorterbuddyUntil()
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_PORTERBUDDY_UNTIL);
    }

    /**
     * @return bool
     */
    public function isRequireSignatureDefault()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_REQUIRE_SIGNATURE_DEFAULT);
    }

    /**
     * @return int
     */
    public function getMinAgeCheckDefault()
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_MIN_AGE_CHECK_DEFAULT);
    }

    /**
     * @return bool
     */
    public function isIdCheckDefault()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ID_CHECK_DEFAULT);
    }

    /**
     * @return bool
     */
    public function isOnlyToRecipientDefault()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ONLY_RECIPIENT_DEFAULT);
    }

    /**
     * @return int|null
     */
    public function getRequireSignatureAttr()
    {
        return Mage::getStoreConfig(self::XML_PATH_REQUIRE_SIGNATURE_ATTR);
    }

    /**
     * @return int|null
     */
    public function getMinAgeCheckAttr()
    {
        return Mage::getStoreConfig(self::XML_PATH_MIN_AGE_CHECK_ATTR);
    }

    /**
     * @return int|null
     */
    public function getIdCheckAttr()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ID_CHECK_ATTR);
    }

    /**
     * @return int|null
     */
    public function getOnlyToRecipientAttr()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ONLY_RECIPIENT_ATTR);
    }

    /**
     * @return bool
     */
    public function availabilityAutoUpdateComposite()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_AVAILABILITY_AUTO_UPDATE_COMPOSITE);
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
     * @return string Convert_Porterbuddy_Model_Carrier::TIMESLOT_CHECKOUT_* constant
     */
    public function getTimeslotSelection()
    {
        return Mage::getStoreConfig(self::XML_PATH_TIMESLOT_SELECTION);
    }


    /**
     * @return int
     */
    public function getExtraPickupWindows()
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_EXTRA_PICKUP_WINDOWS);
    }

    /**
     * Overriden price for express delivery, in base currency
     *
     * @return float|null
     */
    public function getPriceOverrideExpress()
    {
        $value = Mage::getStoreConfig(self::XML_PATH_PRICE_OVERRIDE_EXPRESS);
        if (!strlen($value)) {
            return null;
        }

        return Mage::app()->getLocale()->getNumber($value);
    }

    /**
     * Overriden price for normal delivery, in base currency
     *
     * @return float|null
     */
    public function getPriceOverrideDelivery()
    {
        $value = Mage::getStoreConfig(self::XML_PATH_PRICE_OVERRIDE_DELIVERY);
        if (!strlen($value)) {
            return null;
        }

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
     * @return float|null
     */
    public function getReturnPrice()
    {
        $value = Mage::getStoreConfig(self::XML_PATH_RETURN_PRICE);
        if (!strlen($value)) {
            return null;
        }

        return Mage::app()->getLocale()->getNumber($value);
    }

    /**
     * @return float
     */
    public function getRefreshOptionsTimeout()
    {
        $value = Mage::getStoreConfig(self::XML_PATH_REFRESH_OPTIONS_TIMEOUT);
        if (!strlen($value)) {
            return 0;
        }

        return Mage::app()->getLocale()->getNumber($value);
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
     * @return string
     */
    public function getErrorEmailEnabled($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ERROR_EMAIL_ENABLED, $storeId);
    }

    /**
     * @return string
     */
    public function getErrorEmailIdentify($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ERROR_EMAIL_IDENTITY, $storeId);
    }

    /**
     * @return string
     */
    public function getErrorEmailTemplate($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ERROR_EMAIL_TEMPLATE, $storeId);
    }

    /**
     * @return array
     */
    public function getErrorEmailRecipients()
    {
        $emails = array();
        foreach ((array)Mage::getStoreConfig(self::XML_PATH_ERROR_EMAIL_RECIPIENTS) as $row) {
            if (isset($row['email'])) {
                $emails[] = trim($row['email']);
            }
        }
        return $emails;
    }

    /**
     * @return array
     */
    public function getErrorEmailRecipientsPorterbuddy()
    {
        $emails = array();
        foreach ((array)Mage::getStoreConfig(self::XML_PATH_ERROR_EMAIL_RECIPIENTS_PORTERBUDDY) as $row) {
            if (isset($row['email'])) {
                $emails[] = trim($row['email']);
            }
        }
        return $emails;
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
     * Convert Weight to KILOGRAM
     *
     * @param float|null $weight
     * @param string|null $weightUnit optional, by default from config
     * @return float|null
     * @throws Convert_Porterbuddy_Exception
     */
    public function convertWeightToGrams($weight, $weightUnit = null)
    {
        if (!strlen($weight)) {
            return null;
        }

        if (!is_numeric($weight)) {
            $this->log(
                "Weight must be numeric `$weight`.",
                null,
                Zend_Log::ERR
            );
            throw new Convert_Porterbuddy_Exception($this->__('Weight conversion error.'));
        }

        if (!$weightUnit) {
            $weightUnit = $this->getWeightUnit();
        }

        switch ($weightUnit) {
            case Convert_Porterbuddy_Model_Carrier::WEIGHT_KILOGRAM:
                return $weight*1000;
            case Convert_Porterbuddy_Model_Carrier::WEIGHT_GRAM:
                return (float)$weight;
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
     * @param float|null $dimension
     * @return float|null
     * @throws Convert_Porterbuddy_Exception
     */
    public function convertDimensionToCm($dimension)
    {
        if (!strlen($dimension)) {
            return null;
        }

        if (!is_numeric($dimension)) {
            $this->log(
                "Dimension must be numeric `$dimension`.",
                null,
                Zend_Log::ERR
            );
            throw new Convert_Porterbuddy_Exception($this->__('Dimension conversion error.'));
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
            'start' => null,
            'end' => null,
            'return' => false,
        );

        $parts = explode('_', $methodCode);
        $type = array_shift($parts);
        if (Convert_Porterbuddy_Model_Carrier::CODE == $type) {
            // skip carrier code if present
            $type = array_shift($parts);
        }

        $result['type'] = $type;

        $part = reset($parts);
        if ('return' == $part) {
            // skip start/end
            $result['return'] = true;
        } else {
            $result['start'] = array_shift($parts);
            $result['end'] = array_shift($parts);
            $result['return'] = ('return' === array_shift($parts));
        }

        return $result;
    }

    public function formatApiDateTime($dateTime)
    {
        if (null === $dateTime) {
            return null;
        }
        if (is_string($dateTime)) {
            $dateTime = new DateTime($dateTime);
        }

        $dateTime->setTimezone($this->getTimezone());

        return $dateTime->format(static::API_DATE_FORMAT);
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
     * @param Mage_Sales_Model_Order $order
     * @param string $startingBy
     * @param callable $callback
     * @throws Exception
     */
    public function lockShipmentCreation($order, $startingBy, $callback)
    {
        $started = null;
        try {
            $started = $this->setShipmentCreationStarted($order, $startingBy);
            if ($started) {
                $callback($order);
                $this->unsetShipmentCreationStarted($order);
            }
        } catch (\Exception $e) {
            // logged
            if ($started) {
                $this->unsetShipmentCreationStarted($order);
            }

            throw $e;
        }
    }

    /**
     * Sets the flag 'ShipmentCreatingBy' for the order
     * @param Mage_Sales_Model_Order $order
     * @param string $startingBy
     * @return boolean
     */
    public function setShipmentCreationStarted($order, $startingBy)
    {
        /** @var Mage_Sales_Model_Order $orderCopy */
        $orderCopy = Mage::getModel('sales/order');
        $orderCopy->load($order->getId());

        if ($startedBy = $orderCopy->getPbShipmentCreatingBy()) {
            $this->log(
                'Shipment creation request is prohibited for ' . $startingBy
                . ' because already started by ' . $startedBy . '. Order: ' . $order->getId(),
                null,
                Zend_Log::NOTICE
            );
            return false;
        }
        $orderCopy->setPbShipmentCreatingBy($startingBy)
            ->save();
        $this->log(
            'Shipment creation started by ' . $startingBy . '. Order: ' . $order->getId(),
            null,
            Zend_Log::NOTICE
        );
        return true;
    }

    /**
     * Unsets the flag 'ShipmentCreatingBy' for the order
     * @param Mage_Sales_Model_Order $order
     */
    public function unsetShipmentCreationStarted($order)
    {
        /** @var Mage_Sales_Model_Order $orderCopy */
        $orderCopy = Mage::getModel('sales/order');
        $orderCopy->load($order->getId());
        $orderCopy->setPbShipmentCreatingBy(null)
            ->save();
        $this->log('Shipment creation ended. Order: ' . $order->getId(), null, Zend_Log::NOTICE);
    }

    public function formatPrice(Mage_Sales_Model_Quote $quote, $price)
    {
        $shippingPrice = $this->taxHelper->getShippingPrice(
            $price,
            $this->taxHelper->displayShippingPriceIncludingTax(),
            $quote->getShippingAddress()
        );
        $convertedPrice = $quote->getStore()->convertPrice($shippingPrice, true);
        return $convertedPrice;
    }

    /**
     * Converts {{...}} to #{...} placeholders, optionally wraps each
     *
     * @param string $template
     * @param string $wrapper optional
     * @return string
     */
    public function processPlaceholders(
        $template,
        $wrapper = '<span class="porterbuddy-availability-{{name}}">{{value}}</span>'
    ) {
        $template = preg_replace_callback('/{{(.+)}}/U', function($matches) use ($wrapper) {
            $name = $matches[1];
            $value = '#{' . $name . '}';
            if ($wrapper) {
                $value = str_replace(array('{{name}}', '{{value}}'), array($name, $value), $wrapper);
            }
            return $value;
        }, $template);

        return $template;
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
