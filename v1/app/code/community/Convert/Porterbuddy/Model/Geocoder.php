<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Geocoder
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
     * Performs server-side geocoding request and returns coordinates on success
     *
     * @param string $address
     * @return array lat, lng
     * @throws Convert_Porterbuddy_Exception
     * @throws Zend_Http_Client_Exception
     */
    public function geocode($address)
    {
        $parameters = array(
            'address' => $address,
            'key' => $this->helper->getMapsApiKey(),
        );

        $httpClient = new Varien_Http_Client();
        $response = $httpClient->setUri('https://maps.googleapis.com/maps/api/geocode/json')
            ->setParameterGet($parameters)
            ->setConfig(array('timeout' => 10))
            ->request(Zend_Http_Client::GET);
        $data = json_decode($response->getBody(), true);
        $message = isset($data['error_message']) ? $data['error_message'] : null;

        $logData = array(
            'parameters' => $parameters,
            'status' => $response->getStatus(),
            'response' => $response->getBody(),
        );
        if (200 !== $response->getStatus() || 'OK' !== $data['status'] || empty($data['results'][0]['geometry']['location'])) {
            $this->helper->log('Geocode error', $logData, Zend_Log::ERR);
            throw new Convert_Porterbuddy_Exception($message ?: $this->helper->__("Geocoding error - cannot get location for address `$address`"));
        } else {
            $this->helper->log('Geocode success', $logData, Zend_Log::NOTICE);
        }

        // lat, lng
        return $data['results'][0]['geometry']['location'];
    }
}
