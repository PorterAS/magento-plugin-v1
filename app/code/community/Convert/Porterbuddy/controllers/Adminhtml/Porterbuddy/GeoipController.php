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
class Convert_Porterbuddy_Adminhtml_Porterbuddy_GeoipController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    protected function _construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order');
    }

    public function statusAction()
    {
        /** @var $session Mage_Core_Model_Session */
        $session = Mage::getSingleton('core/session');
        /** @var Convert_Porterbuddy_Model_Geoip $geoip */
        $geoip = Mage::getSingleton('convert_porterbuddy/geoip');

        $realSize = filesize($geoip->getResource()->getArchivePath());
        $totalSize = $session->getData('_geoip_file_size');
        echo $totalSize ? $realSize / $totalSize * 100 : 0;
    }

    public function synchronizeAction()
    {
        /** @var Convert_Porterbuddy_Model_Geoip $geoip */
        $geoip = Mage::getModel('convert_porterbuddy/geoip');

        try {
            $result = $geoip->update();
            $result['status'] = 'success';
            return $this->prepareDataJSON($result);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * @param string $message optional
     * @return Zend_Controller_Response_Abstract
     */
    protected function jsonError($message = null)
    {
        $result = array();
        $result['error'] = true;
        if ($message) {
            $result['message'] = $message;
        }
        return $this->prepareDataJSON($result);
    }

    /**
     * Prepare JSON formatted data for response to client
     *
     * @param $response
     * @return Zend_Controller_Response_Abstract
     */
    protected function prepareDataJSON($response)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }
}
