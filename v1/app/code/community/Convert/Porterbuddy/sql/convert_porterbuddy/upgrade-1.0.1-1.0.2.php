<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
/** @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

// using 'customer_address_edit' form is dangerous as it can be used in Klarna checkout to import address after payment
// which results in coordinates being lost
// remove pb_location from any forms

/** @var Mage_Eav_Model_Config $eavConfig */
$eavConfig = Mage::getSingleton('eav/config');
$locationAttribute = $eavConfig->getAttribute('customer_address', 'pb_location');
$locationAttribute->setData('used_in_forms', array());
$locationAttribute->save();

$installer->endSetup();
