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

        $headers = [
            'x-api-key' => $apiKey,
            'Content-type' => 'application/json',
        ];
        $httpClient->setUri($uri)
            ->setHeaders($headers)
            ->setConfig(array('timeout' => $this->helper->getApiTimeout()))
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

        if (/*200 === $response->getStatus() && */isset($data['deliveryWindows'])) {
            $this->helper->log('getAvailability success', $logData);
            return $data['deliveryWindows'];
        }

        $message = $this->helper->__('Get availability options error');
        $this->helper->log('getAvailability error', $logData, Zend_Log::ERR);

        if (/*422 === $response->getStatus() && */is_array($data)) {
            $errors = array();
            foreach ($data as $error) {
                if (isset($error['message'], $error['propertyPath'])) {
                    $errors[] = $this->helper->__("%s {$error['message']}", $error['propertyPath']);
                }
            }
            $message .= ' - ' . implode(', ', $errors);
        }

        throw new Convert_Porterbuddy_ApiException($message);
    }

    /**
     * Create new order
     *
     * @param array $parameters
     * @param string $idempotencyKey optional
     * @return array
     * @throws Zend_Http_Client_Exception
     * @throws Convert_Porterbuddy_ApiException
     */
    public function createOrder(array $parameters, $idempotencyKey = null)
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
            'idempotency_key' => $idempotencyKey,
        );

        $headers = [
            'x-api-key' => $apiKey,
            'Content-type' => 'application/json',
        ];
        if ($idempotencyKey) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        $httpClient->setUri($uri)
            ->setHeaders($headers)
            ->setConfig(array('timeout' => $this->helper->getApiTimeout()))
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

        if (!empty($data['orderId'])) {
            $this->helper->log('createOrder success', $logData, Zend_Log::NOTICE);
            return $data;
        }

        $message = $this->helper->__('Create order error');
        $this->helper->log('createOrder error', $logData, Zend_Log::ERR);

        if (/*422 === $response->getStatus() && */is_array($data)) {
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

    /**
     * Get all availability
     *
     * @param array $parameters
     * @return array
     * @throws Zend_Http_Client_Exception
     * @throws Convert_Porterbuddy_Exception
     * @throws Exception
     */
    public function getAllAvailability(array $parameters)
    {
        $apiKey = $this->helper->getApiKey();
        if (!strlen($apiKey)) {
            throw new Convert_Porterbuddy_Exception("Porterbuddy API key must be configured.");
        }

        $httpClient = new Varien_Http_Client();
        $uri = $this->helper->getApiUrl() . '/availability/all';

        $logData = array(
            'api_url' => $uri,
            'api_key' => $apiKey,
            'parameters' => $parameters,
        );

        $headers = [
            'x-api-key' => $apiKey,
            'Content-type' => 'application/json',
        ];
        $httpClient->setUri($uri)
            ->setHeaders($headers)
            ->setConfig(array('timeout' => $this->helper->getApiTimeout()))
            ->setRawData(json_encode($parameters), 'application/json');

        try {
            $response = $httpClient->request(Zend_Http_Client::POST);
        } catch (Exception $e) {
            $this->helper->log(__FUNCTION__ . ' error', $logData, Zend_Log::ERR);
            $this->helper->log($e);
            throw $e;
        }

        $logData['status'] = $response->getStatus();
        $logData['response'] = $response->getBody();

        $data = json_decode($response->getBody(), true);

        if (/*200 === $response->getStatus() && */isset($data['postalCodeDeliveryWindows'])) {
            $this->helper->log(__FUNCTION__ . ' success', $logData);
            return $data['postalCodeDeliveryWindows'];
        }

        $message = $this->helper->__('Get all postcodes error');
        $this->helper->log(__FUNCTION__ . ' error', $logData, Zend_Log::ERR);

        if (/*422 === $response->getStatus() && */is_array($data)) {
            $errors = array();
            foreach ($data as $error) {
                if (isset($error['message'], $error['propertyPath'])) {
                    $errors[] = $this->helper->__("%s {$error['message']}", $error['propertyPath']);
                }
            }
            $message .= ' - ' . implode(', ', $errors);
        }

        throw new Convert_Porterbuddy_ApiException($message);
    }
    
    /**
     * Get Shipping Label Public URL
     *
     * @param string $url
     * @return array
     * @throws Zend_Http_Client_Exception
     * @throws Convert_Porterbuddy_ApiException
     */
    public function getShippingLabelPublicURL($url)
    {
        $apiKey = $this->helper->getApiKey();
        if (!strlen($apiKey)) {
            throw new Convert_Porterbuddy_Exception("Porterbuddy API key must be configured.");
        }

        $httpClient = new Varien_Http_Client();

        $logData = array(
            'api_url' => $url,
            'api_key' => $apiKey
        );

        $headers = [
            'x-api-key' => $apiKey,
            'Content-type' => 'application/json',
        ];

        $httpClient->setUri($url)
            ->setHeaders($headers)
            ->setConfig(array('timeout' => $this->helper->getApiTimeout()));

        try {
            $response =  $httpClient->request(Zend_Http_Client::GET);
        } catch (Exception $e) {
            $this->helper->log('getShippingLabelPublicURL error', $logData, Zend_Log::ERR);
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

        if (!empty($data['shipmentLabelUrl'])) {
            $this->helper->log('getShippingLabelPublicURL success', $logData, Zend_Log::NOTICE);
            return $data['shipmentLabelUrl'];
        }

        $message = $this->helper->__('getShippingLabelPublicURL error');
        $this->helper->log('getShippingLabelPublicURL error', $logData, Zend_Log::ERR);

        if (/*422 === $response->getStatus() && */is_array($data)) {
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



    /**
     * @throws Exception
     */
    public function printToCargonizer($labelURL)
    {
      $apiKey = $this->helper->getCargonizerKey();
      $senderId = $this->helper->getCargonizerSender();
      $uri = 'https://' . ($this->helper->getCargonizerSandbox()?'sandbox.':'') .
              'cargonizer.no/print?printer_id=' . $this->helper->getCargonizerPrinter();
      if (!strlen($apiKey) || !strlen($senderId)) {
          throw new Convert_Porterbuddy_Exception("Cargonizer not properly configured.");
      }
      $httpClient = new Varien_Http_Client();

      $logData = array(
          'api_url' => $uri,
          'api_key' => $apiKey,
          'senderID' => $senderId,
      );
      $headers = [
          'X-Cargonizer-Key' => $apiKey,
          'X-Cargonizer-Sender' => $senderId,
      ];
      $labelPdf = file_get_contents($labelURL);
      if ($labelPdf === false){
        $this->helper->log("Label Not successfully downloaded");
        throw new Convert_Porterbuddy_Exception("Cargonizer label not properly downloaded.");
      }

      $httpClient->setUri($uri)
          ->setHeaders($headers)
          ->setConfig(array('timeout' => $this->helper->getApiTimeout()))
          ->setRawData(array('data' => $labelPdf), 'multipart/form-data');

          try {
              $response = $httpClient->request(Zend_Http_Client::POST);
          } catch (Exception $e) {
              $this->helper->log('printToCargonizer error', $logData, Zend_Log::ERR);
              $this->helper->log($e);
              throw $e;
          }

          $logData['status'] = $response->getStatus();
          $logData['response'] = $response->getBody();

          if (202 === $response->getStatus()) {
              $this->helper->log('printToCargonizer success', $logData);

          }else{

            $message = $this->helper->__('printToCargonizer  error');
            $this->helper->log('printToCargonizer error', $logData, Zend_Log::ERR);

            if (/*422 === $response->getStatus() && */is_array($data)) {
                $errors = array();
                foreach ($data as $error) {
                    if (isset($error['message'], $error['propertyPath'])) {
                        $errors[] = $this->helper->__("%s {$error['message']}", $error['propertyPath']);
                    }
                }
                $message .= ' - ' . implode(', ', $errors);
            }

            throw new Convert_Porterbuddy_ApiException($message);
        }
        return;

    }

}
