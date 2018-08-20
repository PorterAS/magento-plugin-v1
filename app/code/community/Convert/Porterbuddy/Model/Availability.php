<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Availability
{
    const FLAG_POSTCODES_UPDATED = 'porterbuddy_postcodes_updated';

    /**
     * @var Convert_Porterbuddy_Model_Api
     */
    protected $api;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $coreHelper;

    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @var Convert_Porterbuddy_Model_Timeslots
     */
    protected $timeslots;

    /**
     * @param array|null $data
     * @param Convert_Porterbuddy_Helper_Data|null $helper
     */
    public function __construct(
        array $data = null, // for getModel to work
        Convert_Porterbuddy_Model_Api $api = null,
        Mage_Core_Helper_Data $coreHelper = null,
        Convert_Porterbuddy_Helper_Data $helper = null,
        Convert_Porterbuddy_Model_Timeslots $timeslots = null
    ) {
        $this->api = $api ?: Mage::getSingleton('convert_porterbuddy/api');
        $this->coreHelper = $coreHelper ?: Mage::helper('core');
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');
        $this->timeslots = $timeslots ?: Mage::getSingleton('convert_porterbuddy/timeslots');
    }

    /**
     * @return array
     */
    public function getPostcodes()
    {
        // read directly from DB since cron can update value but can't clear config cache
        /** @var Mage_Core_Model_Resource_Config_Data_Collection $collection */
        $collection = Mage::getResourceModel('core/config_data_collection');
        $collection->addFieldToFilter('scope', Mage_Adminhtml_Block_System_Config_Form::SCOPE_DEFAULT);
        $collection->addFieldToFilter('scope_id', 0);
        $collection->addFieldToFilter('path', Convert_Porterbuddy_Helper_Data::XML_PATH_POSTCODES);
        $postcodes = $collection->getFirstItem()->getValue();

        $postcodes = preg_split('/(\r\n|\n)/', $postcodes);
        $postcodes = array_map(function ($row) {
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
     * Returns date for formula "Want it {date}? Order in the next {N} hours"
     *
     * @return DateTime|false in *local* timezone for convenience
     */
    public function getAvailableUntil()
    {
        $date = $this->helper->getCurrentTime();

        // protect against misconfiguration
        for ($i = 0; $i < 10; $i++) {
            $openHours = $this->timeslots->getOpenHours($date);

            if ($openHours) {
                // Porterbuddy works until is set in local timezone
                $porterbuddyWorksUntil = clone $openHours['close'];

                $minutes = $this->helper->getPorterbuddyUntil(strtolower($date->format('D')));
                $porterbuddyWorksUntil->modify("-{$minutes} minutes");

                if ($date < $porterbuddyWorksUntil) {
                    /** @var DateTime $result */
                    $result = $porterbuddyWorksUntil;
                    $result->setTimezone($this->helper->getTimezone());
                    return $result;
                }
            }

            // for future days, we don't need specific opening hour, just make sure it's before closing hour
            $date
                ->modify('+1 day')
                ->setTime(0, 0, 0);
        }

        return false;
    }


    /**
     * @throws Exception
     */
    public function updatePostcodes()
    {
        // TODO: by website

        try {
            $parameters = $this->preparePostcodesParameters();
            $result = $this->api->getAllAvailability($parameters);
        } catch (Exception $e) {
            $this->helper->log($e, null, Zend_Log::ERR);
            throw $e;
        }

        // extract postcodes
        $postcodes = array_keys($result);

        $config = Mage::getModel('core/config_data');
        $config->load(Convert_Porterbuddy_Helper_Data::XML_PATH_POSTCODES, 'path')
            ->setValue(implode("\n", $postcodes))
            ->setPath(Convert_Porterbuddy_Helper_Data::XML_PATH_POSTCODES)
            ->save();

        $this->setPostcodesUpdated(new \DateTime());
    }

    /**
     * @return array|mixed
     */
    public function preparePostcodesParameters()
    {
        $params = array();
        $params['pickupWindows'] = $this->timeslots->getAvailabilityPickupWindows();

        $$params['pickupWindows'] = $this->timeslots->getAvailabilityPickupWindows();

        $originStreet1 = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS1);
        $originStreet2 = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS2);
        $params['originAddress'] = array(
            'streetName' => trim("$originStreet1 $originStreet2"),
            'streetNumber' => ',', // FIXME: set empty when API is updated
            'postalCode' => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ZIP),
            'city' => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_CITY),
            'country' => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID),
        );
        $params['products'] = array(Convert_Porterbuddy_Model_Carrier::METHOD_DELIVERY);

        $transport = new Varien_Object(array('params' => $params));
        Mage::app()->dispatchEvent('porterbuddy_availability_all_data', [
            'transport' => $transport,
        ]);
        $params = $transport->getData('params');

        return $params;
    }

    /**
     * @return DateTime|null
     */
    public function getPostcodesUpdated()
    {
        /** @var Mage_Core_Model_Flag $flag */
        $flag = Mage::getModel('core/flag', ['flag_code' => self::FLAG_POSTCODES_UPDATED])->loadSelf();
        $updated = $flag->getFlagData();

        if ($updated) {
            try {
                $updated = DateTime::createFromFormat(DateTime::ATOM, $updated);
            } catch (Exception $e) {
                // not a big deal
            }
        }

        return $updated;
    }

    /**
     * @param DateTime $dateTime
     * @return void
     * @throws Exception
     */
    public function setPostcodesUpdated(\DateTime $dateTime)
    {
        /** @var Mage_Core_Model_Flag $flag */
        $flag = Mage::getModel('core/flag', ['flag_code' => self::FLAG_POSTCODES_UPDATED])->loadSelf();
        $flag->setFlagData($dateTime->format(DateTime::ATOM));
        $flag->save();
    }
}
