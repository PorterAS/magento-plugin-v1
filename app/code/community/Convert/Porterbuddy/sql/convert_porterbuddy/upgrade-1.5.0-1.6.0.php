<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
/** @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('sales/order');
$installer->getConnection()->addColumn(
    $tableName,
    'pb_shipment_creating_by',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 32,
        'nullable' => true,
        'comment' => 'Porterbuddy Shipment Is Creating By',
    )
);

$installer->endSetup();