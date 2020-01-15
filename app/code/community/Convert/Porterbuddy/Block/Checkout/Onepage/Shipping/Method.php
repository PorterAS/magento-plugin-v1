<?php
class Convert_Porterbuddy_Block_Checkout_Onepage_Shipping_Method extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    protected function _afterToHtml($html)
    {
        $html .= $this->getLayout()->createBlock('convert_porterbuddy/widget')->setTemplate('convert/porterbuddy/widget.phtml')->toHtml();
        return $html;
    }
}