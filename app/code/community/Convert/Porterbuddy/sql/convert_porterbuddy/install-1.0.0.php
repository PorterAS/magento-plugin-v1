<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
/** @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

// quote address
$tableName = $installer->getTable('sales/quote_address');
$installer->getConnection()->addColumn(
    $tableName,
    'pb_lat',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length' => '14,10',
        'nullable' => true,
        'comment' => 'Latitude',
    )
);
$installer->getConnection()->addColumn(
    $tableName,
    'pb_lng',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length' => '14,10',
        'nullable' => true,
        'comment' => 'Longitude',
    )
);
$installer->getConnection()->addColumn(
    $tableName,
    'pb_user_edited',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable' => false,
        'default' => 0,
        'comment' => 'Location User Edited',
    )
);

// order address
$tableName = $installer->getTable('sales/order_address');
$installer->getConnection()->addColumn(
    $tableName,
    'pb_lat',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length' => '14,10',
        'nullable' => true,
        'comment' => 'Latitude',
    )
);
$installer->getConnection()->addColumn(
    $tableName,
    'pb_lng',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length' => '14,10',
        'nullable' => true,
        'comment' => 'Longitude',
    )
);
$installer->getConnection()->addColumn(
    $tableName,
    'pb_user_edited',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable' => false,
        'default' => 0,
        'comment' => 'Location User Edited',
    )
);

$installer->endSetup();
