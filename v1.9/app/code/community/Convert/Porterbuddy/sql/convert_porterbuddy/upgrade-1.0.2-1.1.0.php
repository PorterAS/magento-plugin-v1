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
    'pb_paid_at',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'nullable' => true,
        'comment' => 'Paid At',
    )
);
$installer->getConnection()->addColumn(
    $tableName,
    'pb_autocreate_status',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 32,
        'nullable' => true,
        'comment' => 'Porterbuddy Auto-create Status',
    )
);

$installer->endSetup();
