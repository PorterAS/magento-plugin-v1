<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Block_Checkout extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $quote;

    /**
     * @var Convert_Porterbuddy_Model_Timeslots
     */
    protected $timeslots;

    protected function _construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
        $this->timeslots = Mage::getSingleton('convert_porterbuddy/timeslots');
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        $text = $this->helper->getTitle();

        return $text;

    }
    /**
     * @return string
     */
    public function getSubTitle()
    {
        $text = $this->helper->getSubTitle();

        return $text;

    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->helper->getDescription();
    }

    /**
     * @return string
     */
    public function getAsapName()
    {
        return $this->helper->getAsapName();
    }

    /**
     * @return string
     */
    public function getLeaveDoorstepText()
    {
        return $this->helper->getLeaveDoorstepText();
    }

    /**
     * @return bool
     */
    public function getLeaveDoorstep()
    {
        return (bool)$this->getQuote()->getPbLeaveDoorstep();
    }

    /**
     * @return string
     */
    public function getCommentText()
    {
        return $this->helper->getCommentText();
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->getQuote()->getPbComment();
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
     * @return float|null
     */
    public function getRefreshOptionsTimeout()
    {
        return $this->helper->getRefreshOptionsTimeout();
    }

    /**
     * @return string|null
     */
    public function getPostcode()
    {
        return $this->getAddress()->getPostcode();
    }

    /**
     * @return int|null
     */
    public function getDiscount()
    {
        return Mage::getSingleton('checkout/session')->getPbDiscount();
    }
}
