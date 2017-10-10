<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Cron
{
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
        Convert_Porterbuddy_Helper_Data $helper = null,
        Convert_Porterbuddy_Model_Shipment $shipment = null
    ) {
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

        $timeout = $this->helper->getCreateShipmentTimeout();

        $lastOrderId = null;
        $order = $this->getNextOrder($lastOrderId);

        while ($order->getId()) {
            $paidMinutesAgo = (time() - strtotime($order->getPbPaidAt()))/60;

            $createCase = null;
            if (!$order->getPbUserEdited() && $paidMinutesAgo >= $timeout) {
                $createCase = "no user approved location after `$timeout` minutes";
            } elseif ($order->getPbUserEdited() && $order->getPbLocation()) {
                $createCase = 'both user approved location and payment received';
            }

            if ($createCase) {
                try {
                    $this->helper->log(
                        'Creating shipment by cron.',
                        array(
                            'order_id' => $order->getId(),
                            'create_case' => $createCase,
                        ),
                        Zend_Log::NOTICE
                    );
                    $this->shipment->createShipment($order);
                } catch (Exception $e) {
                    // logged
                }
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
        $collection->join(
            array('address' => 'sales/order_address'),
            'main_table.entity_id = address.parent_id AND address.address_type = "shipping"',
            array('pb_user_edited', 'pb_location')
        );
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

        return $collection->getFirstItem();
    }
}
