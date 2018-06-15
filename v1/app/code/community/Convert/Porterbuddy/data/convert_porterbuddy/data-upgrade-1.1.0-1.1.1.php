<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
/** @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

// avoid name collisions
$installer->getConnection()->update(
    $installer->getTable('core/config_data'),
    array('path' => new \Zend_Db_Expr("REPLACE(path, 'carriers/porterbuddy/', 'carriers/cnvporterbuddy/')")),
    array('path like ?' => 'carriers/porterbuddy/%')
);

// serialized data doesn't work in default config
$installer->setConfigData(
    'carriers/cnvporterbuddy/containers',
    serialize(array (
        // 3 kg, 30x30x30 cm
        '_1502190161706_706' => array (
            'name' => 'Envelope',
            'code' => '1',
            'weight' => '3',
            'length' => '30',
            'width' => '30',
            'height' => '30',
        ),
        // 6 kg, 60x45x45 cm
        '_1502190175692_692' => array (
            'name' => 'Small Box',
            'code' => '2',
            'weight' => '6',
            'length' => '60',
            'width' => '45',
            'height' => '45',
        ),
        // 21 kg, 120x60x60 cm
        '_1502190187296_296' => array (
            'name' => 'Box',
            'code' => '3',
            'weight' => '21',
            'length' => '120',
            'width' => '60',
            'height' => '60',
        ),
        // 100 kg, 200x200x200 cm
        '_1502190206063_63' => array (
            'name' => 'Large Box',
            'code' => '4',
            'weight' => '100',
            'length' => '200',
            'width' => '200',
            'height' => '200',
        ),
    ))
);

$installer->endSetup();
