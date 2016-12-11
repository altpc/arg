<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'attributepages/entity'
 */
$typeText = defined('Varien_Db_Ddl_Table::TYPE_TEXT')
    ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_VARCHAR;

$table = $installer->getConnection()
    ->newTable($installer->getTable('attributepages/entity'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true
        ), 'Entity Id')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => true,
        'default'  => null
        ), 'Attribute Id')
    ->addColumn('option_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => true,
        'default'  => null
        ), 'Option Id')
    ->addColumn('name', $typeText, 64, array(
        'nullable' => false
        ), 'Page Name')
    ->addColumn('identifier', $typeText, 255, array(
        'nullable' => false
        ), 'Url')
    ->addColumn('title', $typeText, 255, array(
        ), 'Title')
    ->addColumn('content', $typeText, '64k', array(
        ), 'Content')
    ->addColumn('image', $typeText, 255, array(
        ), 'Image for Attribute Pages')
    ->addColumn('thumbnail', $typeText, 255, array(
        ), 'Thumbnail for product Pages')
    ->addColumn('meta_keywords', $typeText, '64k', array(
        'nullable' => true
        ), 'Page Meta Keywords')
    ->addColumn('meta_description', $typeText, '64k', array(
        'nullable' => true
        ), 'Page Meta Description')
    ->addColumn('display_settings', $typeText, '64k', array(
        'nullable' => true,
        'default'  => null
        ), 'Page Display Settings')
    ->addColumn('root_template', $typeText, 255, array(
        'nullable' => true,
        'default'  => null
        ), 'Page Template')
    ->addColumn('layout_update_xml', $typeText, '64k', array(
        'nullable' => true,
        'default'  => null
        ), 'Page Layout Update Content')
    ->addColumn('use_for_attribute_page', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array( // for option and attribute
        'nullable' => false,
        'default'  => '1'
        ), 'Is Page Active')
    ->addColumn('use_for_product_page', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array( // for option only
        'nullable' => false,
        'default'  => '0'
        ), 'Is Option Activated on Product Page')
    ->addColumn('excluded_option_ids', $typeText, '64k', array(
        'nullable' => true,
        'default'  => null
        ), 'Excluded Option Ids')
    ->addIndex($installer->getIdxName('attributepages/entity', array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName('attributepages/entity', array('option_id')),
        array('option_id'))
    ->addIndex($installer->getIdxName('attributepages/entity', array('identifier')),
        array('identifier'))
    ->addIndex($installer->getIdxName('attributepages/entity', array('title')),
        array('title'))
    ->addForeignKey(
        $installer->getFkName('attributepages/entity', 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav/attribute'),
        'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('attributepages/entity', 'option_id', 'eav/attribute_option', 'option_id'),
        'option_id',
        $installer->getTable('eav/attribute_option'),
        'option_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Attribute Pages Entity');
$installer->getConnection()->createTable($table);

/**
 * Create table 'attributepages/entity_store'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('attributepages/entity_store'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'primary'  => true
        ), 'Entity ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true
        ), 'Store ID')
    ->addIndex($installer->getIdxName('attributepages/entity_store', array('store_id')),
        array('store_id'))
    ->addForeignKey(
        $installer->getFkName('attributepages/entity_store', 'entity_id', 'attributepages/entity', 'entity_id'),
        'entity_id',
        $installer->getTable('attributepages/entity'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('attributepages/entity_store', 'store_id', 'core/store', 'store_id'),
        'store_id',
        $installer->getTable('core/store'),
        'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Attribute Page To Store Linkage Table');
$installer->getConnection()->createTable($table);

$installer->endSetup();
