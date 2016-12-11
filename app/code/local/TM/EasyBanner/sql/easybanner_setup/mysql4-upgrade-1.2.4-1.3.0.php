<?php

/**
 * @var Mage_Core_Model_Resource_Setup
 */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('easybanner/banner'),
        'resize_image',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'length'   => 1,
            'nullable' => false,
            'default'  => 0,
            'comment'  => 'Resize image flag'
        )
    );
$installer->getConnection()
    ->addColumn(
        $installer->getTable('easybanner/banner'),
        'width',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'unsigned' => true,
            'nullable' => true,
            'default'  => 0,
            'comment'  => 'Image width'
        )
    );
$installer->getConnection()
    ->addColumn(
        $installer->getTable('easybanner/banner'),
        'height',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'unsigned' => true,
            'nullable' => true,
            'default'  => 0,
            'comment'  => 'Image height'
        )
    );
$installer->getConnection()
    ->addColumn(
        $installer->getTable('easybanner/banner'),
        'retina_support',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'length'   => 1,
            'nullable' => false,
            'default'  => 1,
            'comment'  => 'Retina support'
        )
    );
$installer->getConnection()
    ->addColumn(
        $installer->getTable('easybanner/banner'),
        'background_color',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 11,
            'nullable' => false,
            'default'  => '255,255,255',
            'comment'  => 'Background color'
        )
    );

$installer->endSetup();
