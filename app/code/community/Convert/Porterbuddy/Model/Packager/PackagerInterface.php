<?php

interface Convert_Porterbuddy_Model_Packager_PackagerInterface
{
    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return array
     * @throws Convert_Porterbuddy_Exception
     */
    public function estimateParcels(Mage_Shipping_Model_Rate_Request $request);

    /**
     * Creates packages automatically
     *
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return array
     * @throws Convert_Porterbuddy_Exception
     */
    public function createPackages(Mage_Shipping_Model_Shipment_Request $request);
}
