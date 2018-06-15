<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Model_Rate_Result extends Mage_Shipping_Model_Rate_Result
{
    /**
     * {@inheritdoc}
     */
    public function sortRatesByPrice()
    {
        if (!is_array($this->_rates) || !count($this->_rates)) {
            return $this;
        }

        // don't reorder rates, keep ASAP first and then scheduled as is
        return $this;
    }
}
