<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Block_Adminhtml_Frontend_Token
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    protected function _construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
    }

    public function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $element->getElementHtml();

        $newToken = $this->helper->generateToken();

        $html .= <<<HTML
<button class="scalable" type="button" id="convert_porterbuddy_token_generate" onclick="generateConvertPorterbuddyToken()">
    {$this->__('Generate new token')}
</button>
<script type="text/javascript">
function generateConvertPorterbuddyToken() {
    var input = $('{$element->getHtmlId()}');
    if (!input.value || confirm('{$this->escapeHtml($this->__('Are you sure? Old token will stop working.'))}')) {
        input.value = '{$newToken}';
        $('convert_porterbuddy_token_generate').disable().addClassName('disabled');
    }
}
</script>
HTML;

        return $html;
    }
}
