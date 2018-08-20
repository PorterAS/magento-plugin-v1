<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Cron
{
    /**
     * @var Convert_Porterbuddy_Model_Availability
     */
    protected $availability;

    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @var Convert_Porterbuddy_Model_Shipment
     */
    protected $shipment;

    public function __construct(
        array $data = null, // for getModel to work
        Convert_Porterbuddy_Model_Availability $availability = null,
        Convert_Porterbuddy_Helper_Data $helper = null,
        Convert_Porterbuddy_Model_Shipment $shipment = null
    ) {
        $this->availability = $availability?: Mage::getSingleton('convert_porterbuddy/availability');
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');
        $this->shipment = $shipment ?: Mage::getSingleton('convert_porterbuddy/shipment');
    }

    /**
     * Automatically create shipments for paid Porterbuddy orders
     */
    public function sendShipments()
    {
        if (!$this->helper->getAutoCreateShipment()) {
            return;
        }

        $lastOrderId = null;
        $order = $this->getNextOrder($lastOrderId);

        while ($order->getId()) {
            try {
                $this->helper->lockShipmentCreation(
                    $order,
                    Convert_Porterbuddy_Helper_Data::SHIPMENT_CREATOR_CRON,
                    function($order) {
                        $this->helper->log(
                            'Creating shipment by cron.',
                            array('order_id' => $order->getId()),
                            Zend_Log::NOTICE
                        );
                        $this->shipment->createShipment($order);
                    }
                );
            } catch (\Exception $e) {
                // already logged
            }

            $lastOrderId = $order->getId();
            $order = $this->getNextOrder($lastOrderId);
        }
    }

    /**
     * Loads orders one by one so that other cron instance doesn't start processing same orders before current instance finishes
     *
     * @param int $lastOrderId (optional)
     * @return Mage_Sales_Model_Order
     */
    public function getNextOrder($lastOrderId = null)
    {
        /** @var Mage_Sales_Model_Resource_Order_Collection $collection */
        $collection = Mage::getResourceModel('sales/order_collection');
        $collection->getSelect()->joinLeft(
            array('shipment' => 'sales_flat_shipment'),
            'main_table.entity_id = shipment.order_id',
            array()
        );
        $collection
            ->addFieldToFilter('shipping_method', array('like' => Convert_Porterbuddy_Model_Carrier::CODE . '\_%'))
            ->addFieldToFilter('pb_paid_at', array('notnull' => true))
            ->addFieldToFilter('pb_autocreate_status', array('null' => true)) // not processed
            ->addFieldToFilter('shipment.entity_id', array('null' => true)) // shipment not created
            ->setPageSize(1);

        if ($lastOrderId) {
            $collection->addFieldtoFilter('order_id', array('gt' => $lastOrderId));
        }

        $collection->load();

        return $collection->getFirstItem();
    }

    /**
     * Pulls in postcodes from API
     */
    public function updatePostcodes()
    {
        if (!$this->isActiveInAnyWebsite()) {
            return;
        }

        $this->helper->log('Start updating postcodes by cron', null, Zend_Log::INFO);

        try {
            $this->availability->updatePostcodes();
            $this->helper->log('Postcodes have been successfully updated by cron', null, Zend_Log::INFO);
        } catch (\Exception $e) {
            $this->helper->log('Postcodes update by cron failed - ' . $e->getMessage(), null, Zend_Log::INFO);
            // stack trace logged
        }
    }

    /**
     * @return bool
     */
    protected function isActiveInAnyWebsite()
    {
        /** @var Mage_Core_Model_Website $website */
        foreach (Mage::app()->getWebsites() as $website) {
            if ($this->helper->getActive($website->getDefaultStore())) {
                return true;
            }
        }

        return false;
    }
}
