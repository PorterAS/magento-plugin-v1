<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Api
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
     * Retrieves delivery options via API
     *
     * @param array $parameters
     * @return array
     * @throws Zend_Http_Client_Exception
     * @throws Convert_Porterbuddy_Exception
     * @throws Exception
     */
    public function getAvailability(array $parameters)
    {
        $apiKey = $this->helper->getApiKey();
        if (!strlen($apiKey)) {
            throw new Convert_Porterbuddy_Exception("Porterbuddy API key must be configured.");
        }

        $httpClient = new Varien_Http_Client();
        $uri = $this->helper->getApiUrl() . '/availability';

        $logData = array(
            'api_url' => $uri,
            'api_key' => $apiKey,
            'parameters' => $parameters,
        );

        $httpClient->setUri($uri)
            ->setHeaders('x-api-key', $apiKey)
            ->setConfig(array('timeout' => 10))
            ->setRawData(json_encode($parameters), 'application/json');

        try {
            $response = $httpClient->request(Zend_Http_Client::POST);
        } catch (Exception $e) {
            $this->helper->log('getAvailability error', $logData, Zend_Log::ERR);
            $this->helper->log($e);
            throw $e;
        }

        $logData['status'] = $response->getStatus();
        $logData['response'] = $response->getBody();

        $data = json_decode($response->getBody(), true);

        if (200 === $response->getStatus() && isset($data['deliveryWindows'])) {
            $this->helper->log('getAvailability success', $logData);
            return $data['deliveryWindows'];
        }

        $message = $this->helper->__('Get availability options error');
        $this->helper->log('getAvailability error', $logData, Zend_Log::ERR);

        if (422 === $response->getStatus() && is_array($data)) {
            $errors = array();
            foreach ($data as $error) {
                if (isset($error['message'], $error['propertyPath'])) {
                    $errors[] = $this->helper->__("%s {$error['message']}", $error['propertyPath']);
                }
            }
            $message .= ' - ' . implode(', ', $errors);
        }

        throw new Convert_Porterbuddy_Exception($message);
    }

    /**
     * Create new order
     *
     * @param array $parameters
     * @return array
     * @throws Zend_Http_Client_Exception
     * @throws Convert_Porterbuddy_ApiException
     */
    public function createOrder(array $parameters)
    {
        $apiKey = $this->helper->getApiKey();
        if (!strlen($apiKey)) {
            throw new Convert_Porterbuddy_Exception("Porterbuddy API key must be configured.");
        }

        $httpClient = new Varien_Http_Client();
        $uri = $this->helper->getApiUrl() . '/order';

        $logData = array(
            'api_url' => $uri,
            'api_key' => $apiKey,
            'parameters' => $parameters,
        );

        $httpClient->setUri($uri)
            ->setHeaders('x-api-key', $apiKey)
            ->setConfig(array('timeout' => 20))
            ->setRawData(json_encode($parameters), 'application/json');

        try {
            $response =  $httpClient->request(Zend_Http_Client::POST);
        } catch (Exception $e) {
            $this->helper->log('createOrder error', $logData, Zend_Log::ERR);
            $this->helper->log($e);
            throw new Convert_Porterbuddy_ApiException(
                $this->helper->__('Connection error - %s', $e->getMessage()),
                $logData,
                $e
            );
        }

        $logData['status'] = $response->getStatus();
        $logData['response'] = $response->getBody();

        $data = json_decode($response->getBody(), true);

        if (200 === $response->getStatus() && !empty($data['orderId'])) {
            $this->helper->log('createOrder success', $logData, Zend_Log::NOTICE);
            return $data;
        }

        $message = $this->helper->__('Create order error');
        $this->helper->log('createOrder error', $logData, Zend_Log::ERR);

        if (422 === $response->getStatus() && is_array($data)) {
            $errors = array();
            foreach ($data as $error) {
                if (isset($error['message'], $error['propertyPath'])) {
                    $errors[] = $this->helper->__("%s {$error['message']}", $error['propertyPath']);
                }
            }
            $message .= ' - ' . implode(', ', $errors);
        }

        throw new Convert_Porterbuddy_ApiException($message, $logData);
    }
}
