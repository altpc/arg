<?php

/**
 * @var Mage_Core_Model_Resource_Setup
 */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('easybanner/banner'),
        'class_name',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 32,
            'nullable' => false,
            'default'  => '',
            'comment'  => 'Class name'
        )
    );

$installer->endSetup();
