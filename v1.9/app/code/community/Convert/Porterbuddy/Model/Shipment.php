<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Shipment
{
    const AUTOCREATE_CREATED = 'created';
    const AUTOCREATE_FAILED = 'failed';

    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    public function __construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Shipment
     * @throws Exception
     */
    public function createShipment(Mage_Sales_Model_Order $order)
    {
        if (count($order->getShipmentsCollection())) {
            // shipment created
            $this->helper->log('Can\' create shipment, already exists.', array('order_id' => $order->getId()), Zend_Log::WARN);
            throw new Convert_Porterbuddy_Exception($this->helper->__('This order has already been shipped.'));
        }

        $origInProcess = $order->getIsInProcess();

        $this->helper->log('Auto creating shipment.', array('order_id' => $order->getId()));

        try {
            /** @var Mage_Sales_Model_Service_Order $serviceOrder */
            $serviceOrder = Mage::getModel('sales/service_order', $order);
            $shipment = $serviceOrder->prepareShipment();
            $shipment->register();

            $this->helper->log('Shipment created automatically.', null, Zend_Log::NOTICE);

            $comment = $this->helper->__('Shipment created automatically.');
            $shipment->addComment($comment);

            $order->setIsInProcess(true);

            $order->setPbAutocreateStatus(self::AUTOCREATE_CREATED);

            /** @var Mage_Core_Model_Resource_Transaction $transactionSave */
            $transactionSave = Mage::getModel('core/resource_transaction');
            $transactionSave
                ->addObject($shipment)
                ->addObject($order)
                ->save();

            // send shipment email to admin (copy to)
            $shipment->sendEmail(false, $comment);

            $order->setIsInProcess($origInProcess);
            return $shipment;
        } catch (Exception $e) {
            $this->helper->log($e);
            $order->setIsInProcess($origInProcess);

            // current $order object is spoilt by unsuccessful shipment, so add comment separately
            /** @var Mage_Sales_Model_Order $orderCopy */
            $orderCopy = Mage::getModel('sales/order');
            $orderCopy->load($order->getId());
            $orderCopy->addStatusHistoryComment('Porterbuddy shipment wasn\'t ordered! ' . strip_tags($e->getMessage()));
            $orderCopy->setPbAutocreateStatus(self::AUTOCREATE_FAILED);
            $orderCopy->save();

            throw $e;
        }
    }
}
