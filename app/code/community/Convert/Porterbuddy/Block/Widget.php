<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Block_Widget extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    /**
     * @var Mage_Core_Helper_Data
     */
    protected $coreHelper;

    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    /**
     * @var Mage_Tax_Helper_Data
     */
    protected $taxHelper;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $quote;

    protected function _construct()
    {
        $this->coreHelper = Mage::helper('core');
        $this->helper = Mage::helper('convert_porterbuddy');
        $this->taxHelper = Mage::helper('tax');
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
        $text = str_replace('{{formatted_price}}', $this->formatPrice($basePrice), $text);

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
     * @return array
     */
    public function getDates()
    {
        $result = array();

        $rates = $this->getShippingRates();

        if (isset($rates[Convert_Porterbuddy_Model_Carrier::CODE])) {
            $timezone = Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
            $timezone = new DateTimeZone($timezone);

            /** @var Mage_Sales_Model_Quote_Address_Rate $rate */
            foreach ($rates[Convert_Porterbuddy_Model_Carrier::CODE] as $rate) {
                $methodInfo = $this->helper->parseMethod($rate->getMethod());

                $startTime = new DateTime($methodInfo['date']);
                $startTime->setTimezone($timezone); // shift to local time
                $dateKey = $startTime->format('Y-m-d');

                if (!isset($result[$dateKey])) {
                    $dateLabel = $this->coreHelper->formatDate($startTime->format('r'), Mage_Core_Model_Locale::FORMAT_TYPE_FULL);
                    $dateLabel = preg_replace('/\s+\d+$/', '', $dateLabel); // remove year
                    $result[$dateKey] = array(
                        'label' => $dateLabel,
                        'datetime' => $startTime->format(DateTime::ATOM), // allow client-side formatting
                        'timeslots' => array(),
                    );
                }

                $isReturn = $methodInfo['return'];
                if (Convert_Porterbuddy_Model_Carrier::METHOD_ASAP == $methodInfo['type']) {
                    $result[$dateKey]['timeslots'][$rate->getCode()] = array(
                        'label' => $this->getAsapName(),
                        'value' => $rate->getCode(),
                        'datetime' => null, // not applicable
                        'price' => $this->formatPrice($rate->getPrice()),
                        'return' => $isReturn,
                        'class' => 'porterbuddy-timeslot-asap' . ($isReturn ? ' porterbuddy-timeslot-return' : ''),
                    );
                } elseif (Convert_Porterbuddy_Model_Carrier::METHOD_SCHEDULED == $methodInfo['type']) {
                    // scheduled_2017-08-03T14:00:00+00:00_2
                    $endTime = new DateTime($methodInfo['date']);
                    $endTime->setTimezone($timezone); // shift to local time
                    $endTime->modify("+{$methodInfo['timeslotLength']} hours");

                    $result[$dateKey]['timeslots'][$rate->getCode()] = array(
                        'label' => $this->formatTimeslot($startTime, $endTime),
                        'value' => $rate->getCode(),
                        'datetime' => $startTime->format(DateTime::ATOM), // allow client-side formatting
                        'price' => $this->formatPrice($rate->getPrice()),
                        'return' => $isReturn,
                        'class' => 'porterbuddy-timeslot-scheduled' . ($isReturn ? ' porterbuddy-timeslot-return' : ''),
                    );
                }
            }
        }

        $transport = new Varien_Object(['result' => $result]);
        Mage::dispatchEvent('convert_porterbuddy_form_get_dates', array('transport' => $transport));
        $result = $transport->getResult();

        return $result;
    }

    /**
     * Convers and formats shipping price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->getShippingPrice(
            $price,
            $this->taxHelper->displayShippingPriceIncludingTax()
        );
    }

    /**
     * @return Mage_Sales_Model_Quote_Address_Rate|null
     */
    public function getAsapRate()
    {
        $rates = $this->getShippingRates();
        if (isset($rates[Convert_Porterbuddy_Model_Carrier::CODE])) {
            /** @var Mage_Sales_Model_Quote_Address_Rate $rate */
            foreach ($rates[Convert_Porterbuddy_Model_Carrier::CODE] as $rate) {
                $methodInfo = $this->helper->parseMethod($rate->getMethod());

                if (Convert_Porterbuddy_Model_Carrier::METHOD_SCHEDULED == $methodInfo['type']) {
                    return $rate;
                }
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getTimeslotTemplate()
    {
        /** @var Mage_Core_Block_Template $block */
        $block = Mage::app()->getLayout()->createBlock('core/template');
        $block->setTemplate('convert/porterbuddy/template/timeslot.phtml');
        return $block->toHtml();
    }

    /**
     * Formats timeslot title, e.g. "10:00 - 12:00"
     *
     * @param DateTime $startTime
     * @param DateTime $endTime
     * @return string
     */
    public function formatTimeslot(DateTime $startTime, DateTime $endTime)
    {
        // local time - shift timezone
        $timezone = $this->helper->getTimezone();

        $startTime = clone $startTime;
        $startTime->setTimezone($timezone);
        $endTime = clone $endTime;
        $endTime->setTimezone($timezone);

        return $startTime->format('H:i') . '&ndash;' . $endTime->format('H:i');
    }
}
