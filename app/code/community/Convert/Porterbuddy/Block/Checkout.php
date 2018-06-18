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
        return $this->helper->getTitle();
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
     * @return bool
     */
    public function getReturnEnabled()
    {
        return $this->helper->getReturnEnabled() && $this->helper->getReturnPrice() > 0;
    }

    /**
     * @return string
     */
    public function getReturnText()
    {
        $text = $this->helper->getReturnText();

        $basePrice = $this->getReturnPrice();
        $text = str_replace(
            '{{formatted_price}}',
            $this->helper->formatPrice($this->getQuote(), $basePrice),
            $text
        );

        return $text;
    }

    /**
     * Return price in base currency
     *
     * @return string
     */
    public function getReturnPrice()
    {
        $returnPrice = $this->helper->getReturnPrice();

        $responseCurrencyCode = 'NOK';
        $baseCurrencyRate = $this->baseCurrencyRate = Mage::getModel('directory/currency')
            ->load($responseCurrencyCode)
            ->getAnyRate($this->getQuote()->getStore()->getBaseCurrencyCode());
        $returnPrice = (float)$returnPrice * $baseCurrencyRate;

        return $returnPrice;
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
     * @return bool
     */
    public function showTimeslots()
    {
        return Convert_Porterbuddy_Model_Carrier::TIMESLOT_CHECKOUT == $this->helper->getTimeslotSelection();
    }

    /**
     * @return array
     */
    public function getDates()
    {
        return $this->timeslots->getDatesTimeslots($this->getAddress(), false);
    }

    /**
     * @return float|null
     */
    public function getRefreshOptionsTimeout()
    {
        return $this->helper->getRefreshOptionsTimeout();
    }
}
