<?php

class Convert_Porterbuddy_Block_Availability extends Mage_Core_Block_Template
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    protected function _construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
    }

    public function getMapsApiKey()
    {
        return $this->helper->getMapsApiKey();
    }

    /**
     * Retrieve currently viewed product object
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', Mage::registry('product'));
        }
        return $this->getData('product');
    }

    /**
     * @return string
     */
    public function getChoosePopupTitle()
    {
        return $this->helper->getAvailabilityChoosePopupTitle();
    }

    /**
     * @return string
     */
    public function getChoosePopupDescription()
    {
        return $this->helper->getAvailabilityChoosePopupDescription();
    }

    /**
     * @return bool
     */
    public function isAlwaysShow()
    {
        return Convert_Porterbuddy_Model_Carrier::AVAILABILITY_ALWAYS == $this->helper->showAvailability();
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->helper->getActive()
            || Convert_Porterbuddy_Model_Carrier::AVAILABILITY_HIDE == $this->helper->showAvailability()
        ) {
            return '';
        }

        $product = $this->getProduct();
        if (!$product->isAvailable() || $product->isVirtual()) {
            return '';
        }

        return parent::_toHtml();
    }
}
