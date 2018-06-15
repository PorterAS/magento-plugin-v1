<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Block_Popup extends Mage_Checkout_Block_Onepage_Abstract
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $order;

    protected function _construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        $order = $this->getOrder();
        if (!$order->getId() || !$order->getShippingCarrier() instanceof Convert_Porterbuddy_Model_Carrier) {
            return '';
        }
        if (count($order->getShipmentsCollection())) {
            // shipment already created
            return '';
        }
        if ($order->getShippingAddress()->getPbUserEdited()) {
            // user has selected location once
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (null === $this->order) {
            $order = Mage::getModel('sales/order');
            $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();

            $transport = new Varien_Object(array('order_id' => $orderId));
            Mage::dispatchEvent('convert_porterbuddy_popup_order_id', array('transport' => $transport));
            $orderId = $transport->getData('order_id');

            if ($orderId) {
                $order = $order->load($orderId);
            }
            $this->order = $order;
        }

        return $this->order;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->helper->getPopupTitle();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->helper->getPopupDescription();
    }

    /**
     * @return string
     */
    public function getAddressText()
    {
        return $this->helper->getPopupAddressText();
    }

    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return Mage::getSingleton('core/session')->getFormKey();
    }

    /**
     * @return string|null
     */
    public function getMapsApiKey()
    {
        return $this->helper->getMapsApiKey();
    }

    /**
     * Returns selected delivery latitude/longitude
     *
     * @return array|null lat,lng
     */
    public function getDeliveryLocation()
    {
        $shippingAddress = $this->getOrder()->getShippingAddress();

        return $this->helper->formatLocation($shippingAddress->getPbLocation());
    }

    /**
     * @return string
     */
    public function getDeliveryAddress()
    {
        $shippingAddress = $this->getOrder()->getShippingAddress();

        return $this->helper->formatAddress($shippingAddress);
    }

    /**
     * @return string|null
     */
    public function getStoreAddress()
    {
        /** @var Mage_Sales_Model_Order_Address $address */
        $address = Mage::getModel('sales/order_address');
        $street = array(
            Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS1),
            Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS2)
        );
        $address
            ->setStreet(array_filter($street))
            ->setCity(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_CITY))
            ->setPostcode(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ZIP))
            ->setRegionId(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_REGION_ID))
            ->setCountryId(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID));

        return $this->helper->formatAddress($address);
    }

    /**
     * Returns selected delivery latitude/longitude
     *
     * @return array|null
     */
    public function getStoreLocation()
    {
        return $this->helper->getStoreLocation($this->getOrder());
    }

    /**
     * @return int
     */
    public function getMapsZoom()
    {
        return $this->helper->getMapsZoom();
    }

    /**
     * Returns fallback map center latitude/longitude
     *
     * @return array|null
     */
    public function getDefaultLocation()
    {
        return $this->helper->getDefaultLocation();
    }

    /**
     * @return int
     */
    public function getDefaultZoom()
    {
        return $this->helper->getDefaultZoom();
    }
}
