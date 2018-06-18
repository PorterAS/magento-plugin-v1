<?php

class Convert_Porterbuddy_Model_Errornotifier
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @var array
     */
    protected $notifiers;

    /**
     * @param array|null $data
     * @param Convert_Porterbuddy_Helper_Data|null $helper
     */
    public function __construct(
        array $data = null, // for getModel to work
        Convert_Porterbuddy_Helper_Data $helper = null
    ) {
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');

        $notifiers = Mage::getConfig()->getNode("global/convert_porterbuddy/error_notifiers")->asCanonicalArray();
        foreach ($notifiers as $code => $config) {
            $this->notifiers[$code] = $config;
        }
    }

    public function notify(
        Exception $exception,
        Mage_Sales_Model_Order_Shipment $shipment,
        Mage_Shipping_Model_Shipment_Request $request = null
    ) {
        foreach ($this->getNotifiers() as $notifier) {
            $notifier->notify($exception, $shipment, $request);
        }
    }

    /**
     * @return Convert_Porterbuddy_Model_Error_NotifierInterface[]
     */
    public function getNotifiers()
    {
        $result = array();

        foreach ($this->notifiers as $code => $config) {
            $instance = Mage::getSingleton($config['model']);
            if (!$instance instanceof Convert_Porterbuddy_Model_Error_NotifierInterface) {
                $this->helper->log(
                    "Error notifier `$code` must implement Convert_Porterbuddy_Model_Error_NotifierInterface",
                    $config,
                    Zend_Log::ERR
                );
                continue;
            }
            $result[$code] = $instance;
        }

        return $result;
    }
}
