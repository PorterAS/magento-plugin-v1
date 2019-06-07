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
    const XML_PATH_SUB_TITLE = 'carriers/cnvporterbuddy/sub_title';
    const XML_PATH_DESCRIPTION = 'carriers/cnvporterbuddy/description';
    const XML_PATH_ASAP_NAME = 'carriers/cnvporterbuddy/asap_name';
    const XML_PATH_AUTO_CREATE_SHIPMENT = 'carriers/cnvporterbuddy/auto_create_shipment';
    const XML_PATH_API_MODE = 'carriers/cnvporterbuddy/api_mode';
    const XML_PATH_API_TIMEOUT = 'carriers/cnvporterbuddy/api_timeout';
    const XML_PATH_DEVELOPMENT_API_URL = 'carriers/cnvporterbuddy/development_api_url';
    const XML_PATH_DEVELOPMENT_API_KEY = 'carriers/cnvporterbuddy/development_api_key';
    const XML_PATH_TESTING_API_URL = 'carriers/cnvporterbuddy/testing_api_url';
    const XML_PATH_TESTING_API_KEY = 'carriers/cnvporterbuddy/testing_api_key';
    const XML_PATH_PRODUCTION_API_URL = 'carriers/cnvporterbuddy/production_api_url';
    const XML_PATH_PRODUCTION_API_KEY = 'carriers/cnvporterbuddy/production_api_key';

    const XML_PATH_POSTCODES = 'carriers/cnvporterbuddy/postcodes';
    const XML_PATH_SHOW_AVAILABILITY = 'carriers/cnvporterbuddy/show_availability';
    const XML_PATH_LOCATION_DISCOVERY = 'carriers/cnvporterbuddy/location_discovery';
    const XML_PATH_AVAILABILITY_TEMPLATE = 'carriers/cnvporterbuddy/availability_template';
    const XML_PATH_AVAILABILITY_TEXT_FETCHING = 'carriers/cnvporterbuddy/availability_text_fetching';
    const XML_PATH_AVAILABILITY_TEXT_CLICK_TO_SEE = 'carriers/cnvporterbuddy/availability_text_click_to_see';
    const XML_PATH_AVAILABILITY_TEXT_POSTCODE_ERROR = 'carriers/cnvporterbuddy/availability_text_postcode_error';
    const XML_PATH_AVAILABILITY_TEXT_NO_DATE = 'carriers/cnvporterbuddy/availability_text_delivery_no_date';
    const XML_PATH_AVAILABILITY_ENTER_POSTAL_CODE = 'carriers/cnvporterbuddy/availability_enter_postal_code';
    const XML_PATH_AVAILABILITY_ENTER_POSTAL_CODE_PLACEHOLDER = 'carriers/cnvporterbuddy/availability_enter_postal_code_placeholder';
    const XML_PATH_AVAILABILITY_DETECTING_LOCATION = 'carriers/cnvporterbuddy/availability_detecting_location';
    const XML_PATH_AVAILABILITY_SERVICE_NOT_AVAILABLE = 'carriers/cnvporterbuddy/availability_service_not_available';
    const XML_PATH_AVAILABILITY_AUTO_UPDATE_COMPOSITE = 'carriers/cnvporterbuddy/availability_auto_update_composite';
    const XML_PATH_AVAILABILITY_URL = 'carriers/cnvporterbuddy/availability_widget_url';
    const XML_PATH_AVAILABILITY_CHANGE_LOCATION_BUTTON = 'carriers/cnvporterbuddy/availability_change_location_button';
    const XML_PATH_AVAILABILITY_SEARCH_LOCATION_BUTTON = 'carriers/cnvporterbuddy/availability_search_location_button';
    const XML_PATH_AVAILABILITY_TRY_AGAIN_BUTTON = 'carriers/cnvporterbuddy/availability_try_again_button';
    const XML_PATH_AVAILABILITY_YOUR_POSTCODE = 'carriers/cnvporterbuddy/availability_your_postcode';
    
    const XML_PATH_SENDER_EMAIL_IDENTITY = 'carriers/cnvporterbuddy/sender_email_identity';
    const XML_PATH_DEFAULT_PHONE_CODE = 'carriers/cnvporterbuddy/default_phone_code';
    const XML_PATH_PACKAGER_MODE = 'carriers/cnvporterbuddy/packager_mode';
    const XML_PATH_PACKING_TIME = 'carriers/cnvporterbuddy/packing_time';
    const XML_PATH_TIMESLOT_SELECTION = 'carriers/cnvporterbuddy/timeslot_selection';
    const XML_PATH_DAYS_AHEAD = 'carriers/cnvporterbuddy/days_ahead';
    const XML_PATH_EXTRA_PICKUP_WINDOWS = 'carriers/cnvporterbuddy/pickup_windows_extra';
    const XML_PATH_TIMESLOT_WINDOW = 'carriers/cnvporterbuddy/timeslot_window';

    const XML_PATH_PRICE_OVERRIDE_EXPRESS = 'carriers/cnvporterbuddy/price_override_express';
    const XML_PATH_PRICE_OVERRIDE_DELIVERY = 'carriers/cnvporterbuddy/price_override_delivery';

    const XML_PATH_DISCOUNTS = 'carriers/cnvporterbuddy/discounts';
    const XML_PATH_DISCOUNT_COUPONS = 'carriers/cnvporterbuddy/discount_coupons';

    const XML_PATH_HOURS_MON = 'carriers/cnvporterbuddy/hours_mon';
    const XML_PATH_HOURS_TUE = 'carriers/cnvporterbuddy/hours_tue';
    const XML_PATH_HOURS_WED = 'carriers/cnvporterbuddy/hours_wed';
    const XML_PATH_HOURS_THU = 'carriers/cnvporterbuddy/hours_thu';
    const XML_PATH_HOURS_FRI = 'carriers/cnvporterbuddy/hours_fri';
    const XML_PATH_HOURS_SAT = 'carriers/cnvporterbuddy/hours_sat';
    const XML_PATH_HOURS_SUN = 'carriers/cnvporterbuddy/hours_sun';

    const XML_PATH_PORTERBUDDY_UNTIL = 'carriers/cnvporterbuddy/porterbuddy_until';
    const XML_PATH_PORTERBUDDY_UNTIL_MON = 'carriers/cnvporterbuddy/porterbuddy_until_mon';
    const XML_PATH_PORTERBUDDY_UNTIL_TUE = 'carriers/cnvporterbuddy/porterbuddy_until_tue';
    const XML_PATH_PORTERBUDDY_UNTIL_WED = 'carriers/cnvporterbuddy/porterbuddy_until_wed';
    const XML_PATH_PORTERBUDDY_UNTIL_THU = 'carriers/cnvporterbuddy/porterbuddy_until_thu';
    const XML_PATH_PORTERBUDDY_UNTIL_FRI = 'carriers/cnvporterbuddy/porterbuddy_until_fri';
    const XML_PATH_PORTERBUDDY_UNTIL_SAT = 'carriers/cnvporterbuddy/porterbuddy_until_sat';
    const XML_PATH_PORTERBUDDY_UNTIL_SUN = 'carriers/cnvporterbuddy/porterbuddy_until_sun';

    const XML_PATH_REQUIRE_SIGNATURE_DEFAULT = 'carriers/cnvporterbuddy/require_signature_default';
    const XML_PATH_MIN_AGE_CHECK_DEFAULT = 'carriers/cnvporterbuddy/min_age_check_default';
    const XML_PATH_ID_CHECK_DEFAULT = 'carriers/cnvporterbuddy/id_check_default';
    const XML_PATH_ONLY_RECIPIENT_DEFAULT = 'carriers/cnvporterbuddy/only_to_recipient_default';

    const XML_PATH_REQUIRE_SIGNATURE_ATTR = 'carriers/cnvporterbuddy/require_signature_attr';
    const XML_PATH_MIN_AGE_CHECK_ATTR = 'carriers/cnvporterbuddy/min_age_check_attr';
    const XML_PATH_ID_CHECK_ATTR = 'carriers/cnvporterbuddy/id_check_attr';
    const XML_PATH_ONLY_RECIPIENT_ATTR = 'carriers/cnvporterbuddy/only_to_recipient_attr';

    const XML_PATH_REFRESH_OPTIONS_TIMEOUT = 'carriers/cnvporterbuddy/refresh_options_timeout';
    const XML_PATH_ALLOW_LEAVE_AT_DOORSTEP = 'carriers/cnvporterbuddy/allow_leave_at_doorstep';
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
    const XML_PATH_ERROR_EMAIL_PORTERBUDDY = 'carriers/cnvporterbuddy/error_email_porterbuddy';

    const XML_PATH_DEBUG = 'carriers/cnvporterbuddy/debug';

    const XML_PATH_CARGONIZER_ENABLED = 'carriers/cnvporterbuddy/cargonizer_enabled';
    const XML_PATH_CARGONIZER_SANDBOX = 'carriers/cnvporterbuddy/cargonizer_sandbox';
    const XML_PATH_CARGONIZER_SENDER = 'carriers/cnvporterbuddy/cargonizer_sender';
    const XML_PATH_CARGONIZER_KEY = 'carriers/cnvporterbuddy/cargonizer_key';
    const XML_PATH_CARGONIZER_PRINTER = 'carriers/cnvporterbuddy/cargonizer_printer';
    const XML_PATH_CARGONIZER_PRINTERS = 'carriers/cnvporterbuddy/cargonizer_printers';

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
     * @param mixed $store
     * @return bool
     */
    public function getActive($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ACTIVE, $store);
    }

    /**
     * @return string
     */
    public function getSubTitle()
    {
        return Mage::getStoreConfig(self::XML_PATH_SUB_TITLE);
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
     * @return bool
     */
    public function getAutoCreateShipment()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_AUTO_CREATE_SHIPMENT);
    }

    /**
     * @return bool
     */
    public function getAllowLeaveAtDoorstep()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ALLOW_LEAVE_AT_DOORSTEP);
    }

    /**
     * @return STRING
     */
    public function getApiMode()
    {
        return Mage::getStoreConfig(self::XML_PATH_API_MODE);
    }

    /**
     * @return int
     */
    public function getApiTimeout()
    {
        return Mage::getStoreConfig(self::XML_PATH_API_TIMEOUT);
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
     * @return string
     */
    public function getSenderEmailIdentify($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SENDER_EMAIL_IDENTITY, $storeId);
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
    public function getAvailabilityTextNoDate()
    {
        return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_TEXT_NO_DATE);
    }

    /**
     * Delivery availability template text
     *
     * @return string
     */
    public function getAvailabilityEnterPostalCode()
    {
        return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_ENTER_POSTAL_CODE);
    }

    /**
    * Delivery availability template text
    *
    * @return string
    */
    public function getAvailabilityEnterPostalCodePlaceholder()
    {
      return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_ENTER_POSTAL_CODE_PLACEHOLDER);
    }

    /**
    * Delivery availability template text
    *
    * @return string
    */
    public function getAvailabilityDetectingLocation()
    {
      return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_DETECTING_LOCATION);
    }

    /**
    * Delivery availability template text
    *
    * @return string
    */
    public function getAvailabilityServiceNotAvailable()
    {
      return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_SERVICE_NOT_AVAILABLE);
    }

    /**
    * Delivery availability template text
    *
    * @return string
    */
    public function getAvailabilityChangeLocationButton()
    {
      return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_CHANGE_LOCATION_BUTTON);
    }

    /**
    * Delivery availability template text
    *
    * @return string
    */
    public function getAvailabilitySearchLocationButton()
    {
      return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_SEARCH_LOCATION_BUTTON);
    }


    /**
    * Delivery availability template text
    *
    * @return string
    */
    public function getAvailabilityYourPostcode()
    {
      return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_YOUR_POSTCODE);
    }
    /**
    * Delivery availability template text
    *
    * @return string
    */
    public function getAvailabilityTryAgainButton()
    {
      return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_TRY_AGAIN_BUTTON);
    }
    /**
    * @return string
    */
    public function getAvailabilityURL()
    {
      return Mage::getStoreConfig(self::XML_PATH_AVAILABILITY_URL);
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
    public function getConfiguredOpenHours($dayOfWeek)
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
    * Returns open hours range in UTC timezone
    *
    * @param DateTime
    * @return DateTime[]|false Date range or false if not working
    */
    public function getOpenHours(DateTime $baseDate)
    {
      $localTimezone = $this->getTimezone();
      $defaultTimezone = new DateTimeZone('UTC');

      // ensure local timezone
      $baseDate = clone $baseDate;
      $baseDate->setTimezone($localTimezone);

      $openHours = $this->getConfiguredOpenHours(strtolower($baseDate->format('D')));
      if (false === $openHours) {
        // not working
        return false;
      }

      $openTime = $openHours['open'];
      $closeTime = $openHours['close'];

      // set time in local timezone and convert to UTC
      $openDatetime = clone $baseDate;
      $parts = explode(':', $openTime);
      $openDatetime->setTimezone($localTimezone);
      $openDatetime->setTime($parts[0], $parts[1], 0);
      $openDatetime->setTimezone($defaultTimezone);

      $closeDatetime = clone $baseDate;
      $parts = explode(':', $closeTime);
      $closeDatetime->setTimezone($localTimezone);
      $closeDatetime->setTime($parts[0], $parts[1], 0);
      $closeDatetime->setTimezone($defaultTimezone);

      if ($openDatetime >= $closeDatetime) {
        // misconfig, treat as day off
        return false;
      }

      return array(
        'open' => $openDatetime,
        'close' => $closeDatetime,
      );
    }



    /**
    * Generate pickup windows as large as possible over several days
    *
    * @param DateTime|null $deliveryDate
    * @param int $extraWindows
    * @return array
    */
    public function getPickupWindows()
    {
      // generate up to delivery date + extra windows
      $windows = [];
      $currentTime = $this->getCurrentTime();
      $date = $this->getCurrentTime();

      $addedExtra = 0;
      $triedExtra = 0;
      $extraWindows = $this->getDaysAhead();
      while ($addedExtra < $extraWindows) {
        $hours = $this->getOpenHours($date);
        if ($hours && $currentTime < $hours['close']) {
          $hours['open'] = max($hours['open'], $currentTime);
          $windows[] = array(
            'start' => $hours['open'],
            'end' => $hours['close'],
          );
          $addedExtra++;
        }
        $date->modify('+1 day');
        if ($triedExtra++ > 20) {
          // prevent infinite loop in case of misconfigured working hours
          break;
        }
      }

      // add packing time to first window
      $addTime = $this->getPackingTime();
      /** @var DateTime[] $window */
      foreach ($windows as $i => $window) {
        // if window can't fit packing time (shop is about to close), remove it and find next
        $window['start']->modify("+$addTime minutes");
        if ($window['start'] > $window['end']) {
          unset($windows[$i]);
          continue;
        }
        break;
      }

      // convert to API formst
      $windows = array_map(function ($window) {
        return array(
          'start' => $this->formatApiDateTime($window['start']),
          'end' => $this->formatApiDateTime($window['end']),
        );
      }, $windows);

      return array_values($windows);
    }



    /**
     * @param string $dayOfWeek optional
     * @return int
     * @throws Convert_Porterbuddy_Exception
     */
    public function getPorterbuddyUntil($dayOfWeek = null)
    {
        $default = Mage::getStoreConfig(self::XML_PATH_PORTERBUDDY_UNTIL);
        if (!$dayOfWeek) {
            return (int)$default;
        }

        $map = array(
            'mon' => self::XML_PATH_PORTERBUDDY_UNTIL_MON,
            'tue' => self::XML_PATH_PORTERBUDDY_UNTIL_TUE,
            'wed' => self::XML_PATH_PORTERBUDDY_UNTIL_WED,
            'thu' => self::XML_PATH_PORTERBUDDY_UNTIL_THU,
            'fri' => self::XML_PATH_PORTERBUDDY_UNTIL_FRI,
            'sat' => self::XML_PATH_PORTERBUDDY_UNTIL_SAT,
            'sun' => self::XML_PATH_PORTERBUDDY_UNTIL_SUN,
        );
        if (!isset($map[$dayOfWeek])) {
            throw new Convert_Porterbuddy_Exception($this->__('Incorrect day of week `%s`.', $dayOfWeek));
        }

        $value = Mage::getStoreConfig($map[$dayOfWeek]);
        if (strlen($value)) {
            return (int)$value;
        }

        return (int)$default;
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
          * Discounts
          *
          * @return int
          */
         public function getDiscounts()
         {
             $discounts = array();
             $configDiscounts = Mage::getStoreConfig(self::XML_PATH_DISCOUNTS);
             if($configDiscounts){
                 $configDiscounts = unserialize($configDiscounts);
                 foreach($configDiscounts as $row){
                     if(isset($row['discount'])){
                         $discounts[] = (array)$row;
                     }
                 }
             }
             return $discounts;
         }

         /**
          * Discount coupons
          *
          * @return int
          */
         public function getDiscountCoupons()
         {
             $coupons = array();
             $configCoupons = Mage::getStoreConfig(self::XML_PATH_DISCOUNT_COUPONS);
             if($configCoupons){
                 $configCoupons = unserialize($configCoupons);
                 foreach($configCoupons as $row){
                     if(isset($row['couponcode'])){
                         $coupons[] = (array)$row;
                     }
                 }
             }
             return $coupons;
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
     * Porterbuddy email that is always in the email list
     *
     * @return string
     */
    public function getErrorEmailPorterbuddy()
    {
        return Mage::getStoreConfig(self::XML_PATH_ERROR_EMAIL_PORTERBUDDY);
    }

    /**
    * Enable Cargonizer
    *
    * @return string
    */
    public function getCargonizerEnabled()
    {
      return Mage::getStoreConfig(self::XML_PATH_CARGONIZER_ENABLED);
    }

    /**
    * Cargonizer Sandbox Mode
    *
    * @return string
    */
    public function getCargonizerSandbox()
    {
      return Mage::getStoreConfig(self::XML_PATH_CARGONIZER_SANDBOX);
    }

    /**
    * Cargonizer Sender ID
    *
    * @return string
    */
    public function getCargonizerSender()
    {
      return Mage::getStoreConfig(self::XML_PATH_CARGONIZER_SENDER);
    }

    /**
    * Cargonizer Key
    *
    * @return string
    */
    public function getCargonizerKey()
    {
      return Mage::getStoreConfig(self::XML_PATH_CARGONIZER_KEY);
    }

    /**
    * Cargonier Printers
    *
    * @return string
    */
    public function getCargonizerPrinter()
    {
      return Mage::getStoreConfig(self::XML_PATH_CARGONIZER_PRINTER);
    }

    /**
    * Cargonier Printers
    *
    * @return string
    */
    public function getCargonizerPrinters()
    {
      return Mage::getStoreConfig(self::XML_PATH_CARGONIZER_PRINTERS);
    }

    /**
    * Cargonier Printers
    *
    * @return string
    */
    public function saveCargonizerPrinters($value)
    {

      Mage::getConfig()->saveConfig(self::XML_PATH_CARGONIZER_PRINTERS, $value);
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
        // TODO: methodInfo object

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

    public function formatPrice(Mage_Sales_Model_Quote $quote, $price, $format=true)
    {
        $shippingPrice = $this->taxHelper->getShippingPrice(
            $price,
            $this->taxHelper->displayShippingPriceIncludingTax(),
            $quote->getShippingAddress()
        );
        $convertedPrice = $quote->getStore()->convertPrice($shippingPrice, $format);
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
        $wrapper
    ) {
        $template = preg_replace_callback('/{{(.+)}}/U', function($matches) use ($wrapper) {
            $name = $matches[1];
            $value = '#{' . $name . '#}';
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
