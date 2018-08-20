<?php

class Convert_Porterbuddy_Model_Error_Emailnotifier implements Convert_Porterbuddy_Model_Error_NotifierInterface
{
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
        Convert_Porterbuddy_Helper_Data $helper = null
    ) {
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');
    }

    /**
     * {@inheritdoc}
     */
    public function notify(
        Exception $exception,
        Mage_Sales_Model_Order_Shipment $shipment,
        Mage_Shipping_Model_Shipment_Request $request = null
    ) {
        if (!$this->helper->getErrorEmailEnabled()) {
            return;
        }

        $emails = $this->helper->getErrorEmailRecipients();

        if ($exception instanceof Convert_Porterbuddy_ApiException) {
            // fixed email that is always on the email list
            $emails[] = $this->helper->getErrorEmailPorterbuddy();
            $emails = array_merge($emails, $this->helper->getErrorEmailRecipientsPorterbuddy());
        }

        $emails = array_unique($emails);

        $storeId = $shipment->getStoreId();
        $sender = $this->helper->getErrorEmailIdentify($storeId);

        if (!$sender || !$emails) {
            $this->helper->log(
                'Email error notify - sender and recipients must be defined',
                array('order_id' => $shipment->getOrderId()),
                Zend_Log::ALERT
            );
            return;
        }

        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');

        /** @var Mage_Core_Model_Email_Info $emailInfo */
        $emailInfo = Mage::getModel('core/email_info');
        foreach ($emails as $email) {
            $emailInfo->addTo($email);
        }
        $mailer->addEmailInfo($emailInfo);

        // Set all required params and send emails
        $mailer->setSender($sender);
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($this->helper->getErrorEmailTemplate($storeId));
        $mailer->setTemplateParams($this->getTemplateParams($exception, $shipment, $request));

        try {
            $mailer->send();
        } catch (\Exception $e) {
            $this->helper->log('Send error email failure - ' . $e->getMessage(), null, Zend_Log::ALERT);
            $this->helper->log($e);
        }
    }

    /**
     * @param Exception $exception
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param Mage_Shipping_Model_Shipment_Request|null $request
     * @return array
     */
    public function getTemplateParams(
        Exception $exception,
        Mage_Sales_Model_Order_Shipment $shipment,
        Mage_Shipping_Model_Shipment_Request $request = null
    ) {
        $packages = $shipment->getPackages();
        if ($packages && is_scalar($packages)) {
            $packages = unserialize($packages);
        }

        $logData = new Varien_Object();
        if ($exception instanceof Convert_Porterbuddy_ApiException) {
            $logData->setData($exception->getLogData());
        }

        $params = array(
            'shipment' => $shipment,
            'packages' => $packages,
            'packages_json' => json_encode($packages, JSON_PRETTY_PRINT),
            'order' => $shipment->getOrder(),
            'exception' => $exception,
            'message' => $exception->getMessage(),
            'apiUrl' => $logData->getData('api_url'),
            'apiKey' => $logData->getData('api_key'),
            'parameters' => $logData->getData('parameters'),
            'parameters_json' => json_encode($logData->getData('parameters'), JSON_PRETTY_PRINT),
            'status' => $logData->getData('status'), // optional
            'response' => $logData->getData('response'), // optional
        );

        $transport = new Varien_Object(array('params' => $params));
        Mage::dispatchEvent('convert_porterbuddy_error_email_params', array(
            'request' => $request,
            'transport' => $transport,
        ));
        $params = $transport->getData('params');

        return $params;
    }
}
