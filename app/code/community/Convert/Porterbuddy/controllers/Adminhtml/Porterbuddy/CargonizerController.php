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
class Convert_Porterbuddy_Adminhtml_Porterbuddy_CargonizerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Convert_Porterbuddy_Model_Cargonizer
     */
    protected $cargonizer;

    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    protected function _construct()
    {
        $this->cargonizer = Mage::getSingleton('convert_porterbuddy/cargonizer');
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
            $this->cargonizer->updatePrinters();
            $session->addSuccess($this->helper->__('Printers have been updated.'));
        } catch (\Exception $e) {
            // already logged
            $session->addError($this->helper->__('An error occurred - %1', $e->getMessage()));
        }

        return $this->_redirectReferer();
    }
}
