<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
/** @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

// add attribute
$installer->addAttribute(
    'customer_address',
    'pb_location',
    array(
        'label' => 'Location',
        'visible' => false,
        'required' => false,
        'type' => 'varchar',
        'input' => 'text',
    )
);
/** @var Mage_Eav_Model_Config $eavConfig */
$eavConfig = Mage::getSingleton('eav/config');
$locationAttribute = $eavConfig->getAttribute('customer_address', 'pb_location');
$locationAttribute->setData('used_in_forms', array(
    // saving new address will reset coordinates on default onepage checkout
    'customer_address_edit',
));
$locationAttribute->save();

// remove separate lat, lng columns, add single location to quote/order address
$tables = array(
    $installer->getTable('sales/quote_address'),
    $installer->getTable('sales/order_address'),
);
foreach ($tables as $tableName) {
    $installer->getConnection()->dropColumn($tableName, 'pb_lat');
    $installer->getConnection()->dropColumn($tableName, 'pb_lng');
    $installer->getConnection()->addColumn(
        $tableName,
        'pb_location',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'comment' => 'Porterbuddy Location',
            'position' => 150,
        )
    );
}

$installer->endSetup();
