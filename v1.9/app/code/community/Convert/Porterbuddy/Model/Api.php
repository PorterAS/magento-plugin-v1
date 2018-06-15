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
     */
    public function getDeliveryOptions(array $parameters)
    {
        // sign in, validate token
        $token = $this->getToken();

        $httpClient = new Varien_Http_Client();
        $apiUrl = $this->helper->getApiUrl();
        $uri = $apiUrl . '/api/v1/vendor/delivery_options';
        if ($parameters) {
            // can't use >setParameterGet($parameters)
            // API only accepts "dates[]=value&dates[]=value" instead of "dates[0]=value&dates[1]=value"
            $query = http_build_query($parameters);
            $query = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $query);
            $uri .= "?$query";
        }

        $response = $httpClient->setUri($uri)
            ->setHeaders('token', $token)
            ->setConfig(array('timeout' => 10))
            ->request(Zend_Http_Client::GET);
        $data = json_decode($response->getBody(), true);
        $message = isset($data['message']) ? $data['message'] : null;

        $logData = array(
            'api_url' => $apiUrl,
            'token' => $token,
            'parameters' => $parameters,
            'status' => $response->getStatus(),
            'response' => $response->getBody(),
        );
        if (200 !== $response->getStatus() || !isset($data['options'])) {
            $this->helper->log('GetDeliveryOptions error', $logData, Zend_Log::ERR);
            throw new Convert_Porterbuddy_Exception($message ?: $this->helper->__('Get delivery options error'));
        } else {
            $this->helper->log('GetDeliveryOptions success', $logData);
        }

        return $data['options'];
    }

    /**
     * Create new order
     *
     * @param array $parameters
     * @return array
     * @throws Zend_Http_Client_Exception
     * @throws Convert_Porterbuddy_Exception
     */
    public function createOrder(array $parameters)
    {
        // sign in, validate token
        $token = $this->getToken();

        $httpClient = new Varien_Http_Client();
        $apiUrl = $this->helper->getApiUrl();
        $response = $httpClient->setUri($apiUrl . '/api/v1/vendor/orders')
            ->setHeaders('token', $token)
            ->setParameterPost($parameters)
            ->setConfig(array('timeout' => 20))
            ->request(Zend_Http_Client::POST);
        $data = json_decode($response->getBody(), true);
        $message = isset($data['message']) ? $data['message'] : null;

        $logData = array(
            'api_url' => $apiUrl,
            'token' => $token,
            'parameters' => $parameters,
            'status' => $response->getStatus(),
            'response' => $response->getBody(),
        );
        if (201 !== $response->getStatus() || empty($data['id'])) {
            if (!$message && !empty($data['errors'])) {
                $errors = $this->helper->formatErrors($data['errors']);
                $message = implode('. ', $errors);
            }
            $this->helper->log('createOrder error', $logData, Zend_Log::ERR);
            throw new Convert_Porterbuddy_Exception($message ?: $this->helper->__('Create order error'));
        } else {
            $this->helper->log('createOrder success', $logData, Zend_Log::NOTICE); // important
        }

        return $data;
    }

    /**
     * Retrieves valid token
     *
     * @return string
     * @throws Zend_Http_Client_Exception
     * @throws Convert_Porterbuddy_Exception
     */
    public function getToken()
    {
        /** @var Mage_Core_Model_Flag $flagModel */
        $flagModel = Mage::getModel('core/flag', array('flag_code' => 'porterbuddy_token'));
        $flagData = $flagModel->loadSelf()->getFlagData();
        $token = isset($flagData['token']) ? $flagData['token'] : null;
        $expiresAt = isset($flagData['expires_at']) ? $flagData['expires_at'] : null;

        $tokenValid = false;
        $refreshTokenBeforeExpirationTime = $this->helper->getRefreshTokenTime();
        if ($token && $expiresAt && ($expiresAt - time()) >= $refreshTokenBeforeExpirationTime) {
            $tokenValid = $this->validateToken($token);
            if (!$tokenValid) {
                $this->helper->log('Token expired', $flagData);
            }
        }

        if (!$tokenValid) {
            $apiKey = $this->helper->getApiKey();
            $apiSecret = $this->helper->getApiSecret();
            $flagData = $this->signIn($apiKey, $apiSecret);
            $this->helper->log('Obtained new token', $flagData);
            $token = $flagData['token'];
            $flagModel->setFlagData($flagData)->save();
        } else {
            $this->helper->log('Reusing token', array($flagData));
        }

        return $token;
    }

    /**
     * @param $token
     * @return bool
     */
    public function validateToken($token)
    {
        $httpClient = new Varien_Http_Client();
        $apiUrl = $this->helper->getApiUrl();
        $response = $httpClient->setUri($apiUrl . '/api/v1/vendor/validate_token')
            ->setHeaders('token', $token)
            ->setConfig(array('timeout' => 5))
            ->request(Zend_Http_Client::GET);
        $data = json_decode($response->getBody(), true);
        return 200 == $response->getStatus() && isset($data['token']);
    }

    /**
     * @param $apiKey
     * @param $apiSecret
     * @return array token, expires_at
     * @throws Zend_Http_Client_Exception
     * @throws Convert_Porterbuddy_Exception
     */
    public function signIn($apiKey, $apiSecret)
    {
        $httpClient = new Varien_Http_Client();
        $apiUrl = $this->helper->getApiUrl();
        $parameters = array('api_key' => $apiKey, 'api_secret' => $apiSecret);
        $response = $httpClient->setUri($apiUrl . '/api/v1/vendor/sign_in')
            ->setParameterPost($parameters)
            ->setConfig(array('timeout' => 5))
            ->request(Zend_Http_Client::POST);
        $data = json_decode($response->getBody(), true);
        $message = isset($data['message']) ? $this->helper->__($data['message']) : null;

        $logData = array(
            'api_url' => $apiUrl,
            'parameters' => $parameters,
            'status' => $response->getStatus(),
            'response' => $response->getBody(),
        );
        if (200 !== $response->getStatus() || !isset($data['token'], $data['expires_at'])) {
            $this->helper->log('SignIn error', $logData, Zend_Log::ERR);
            throw new Convert_Porterbuddy_Exception($message ?: $this->helper->__('Unauthorized'));
        } else {
            $this->helper->log('SignIn success', $logData);
        }

        return $data;
    }
}
