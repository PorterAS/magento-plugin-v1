<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */

/**
 * Class Convert_Porterbuddy_Adminhtml_Porterbuddy_GeoipController
 *
 * Based on Sandfox_GeoIP
 */
class Convert_Porterbuddy_Adminhtml_Porterbuddy_PostcodesController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Convert_Porterbuddy_Model_Availability
     */
    protected $availability;

    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    protected function _construct()
    {
        $this->availability = Mage::getSingleton('convert_porterbuddy/availability');
        $this->helper = Mage::helper('convert_porterbuddy');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order');
    }

    public function updateAction()
    {
        /** @var Mage_Adminhtml_Model_Session $session */
        $session = Mage::getSingleton('adminhtml/session');

        try {
            $this->availability->updatePostcodes();
            $session->addSuccess($this->helper->__('Postcodes have been updated.'));
        } catch (\Exception $e) {
            // already logged
            $session->addError($this->helper->__('An error occurred - %1', $e->getMessage()));
        }

        return $this->_redirectReferer();
    }
}
