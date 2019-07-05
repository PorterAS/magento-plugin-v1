<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
/** @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

$tables = array(
    $installer->getTable('sales/quote'),
    $installer->getTable('sales/order')
);
foreach ($tables as $tableName) {
    $installer->getConnection()->addColumn(
        $tableName,
        'pb_token',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'default' => '',
            'nullable' => false,
            'comment' => 'Porterbuddy - timeslot token',
        )
    );
}

$installer->endSetup();
