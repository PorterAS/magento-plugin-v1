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
        'pb_leave_doorstep',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'default' => 0,
            'nullable' => false,
            'comment' => 'Porterbuddy - leave at doorstep allowed',
        )
    );
    $installer->getConnection()->addColumn(
        $tableName,
        'pb_comment',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 512,
            'nullable' => true,
            'comment' => 'Porterbuddy comment',
        )
    );
}

$installer->endSetup();
