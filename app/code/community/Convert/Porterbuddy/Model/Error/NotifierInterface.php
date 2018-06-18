<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
interface Convert_Porterbuddy_Model_Error_NotifierInterface
{
    public function notify(
        Exception $exception,
        Mage_Sales_Model_Order_Shipment $shipment,
        Mage_Shipping_Model_Shipment_Request $request = null
    );
}
