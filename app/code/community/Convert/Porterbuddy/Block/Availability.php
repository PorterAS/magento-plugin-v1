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
     * Retrieve currently viewed product object
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getDiscount()
    {
      $discounts = $this->helper->getDiscounts();
      if($discounts.length == 0){
        return 0;
      }
      return $discounts[0]['discount'];
    }

    /**
     * Retrieve currently viewed product object
     *
     * @return boolean
     */
    public function showAvailabilityWidget()
    {
      $product = $this->getProduct();
      return $product->isAvailable() && !$product->isVirtual();
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
