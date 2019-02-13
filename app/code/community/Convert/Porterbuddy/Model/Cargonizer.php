<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Cargonizer
{
    const FLAG_PRINTERS_UPDATED = 'porterbuddy_printers_updated';

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $coreHelper;

    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;


    /**
     * @param array|null $data
     * @param Convert_Porterbuddy_Helper_Data|null $helper
     */
    public function __construct(
        array $data = null, // for getModel to work
        Mage_Core_Helper_Data $coreHelper = null,
        Convert_Porterbuddy_Helper_Data $helper = null
    ) {
        $this->coreHelper = $coreHelper ?: Mage::helper('core');
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');
    }



    /**
     * @throws Exception
     */
    public function updatePrinters()
    {
      $apiKey = $this->helper->getCargonizerKey();
      $senderId = $this->helper->getCargonizerSender();
      $uri = ($this->helper->getCargonizerSandbox()?'https://sandbox.cargonizer.no/printers.xml':
              'https://cargonizer.no/printers.xml');
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
      $httpClient->setUri($uri)
          ->setHeaders($headers)
          ->setConfig(array('timeout' => $this->helper->getApiTimeout()));

          try {
              $response = $httpClient->request(Zend_Http_Client::GET);
          } catch (Exception $e) {
              $this->helper->log('getPrinters error', $logData, Zend_Log::ERR);
              $this->helper->log($e);
              throw $e;
          }

          $logData['status'] = $response->getStatus();
          $logData['response'] = $response->getBody();

          $data = simplexml_load_string($response->getBody());
          $logData['parsedResponse'] = $data;


          if (200 === $response->getStatus()) {
              $this->helper->log('getPrinters success', $logData);
              $options = array();
              foreach ($data->printer as $element => $value) {
                $options[] = array(
                  'value' => (string)$value->id[0],
                  'label' => (string)$value->name[0],
                );
              }

              Mage::getModel('core/config')->saveConfig(Convert_Porterbuddy_Helper_Data::XML_PATH_CARGONIZER_PRINTERS, json_encode($options));
              Mage::getModel('core/config')->cleanCache();

          }else{

            $message = $this->helper->__('Get printers  error');
            $this->helper->log('getPrinters error', $logData, Zend_Log::ERR);

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
