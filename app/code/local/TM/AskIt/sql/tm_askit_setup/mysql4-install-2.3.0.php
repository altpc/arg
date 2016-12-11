<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$messageTableName = $installer->getTable('tm_askit_message');
$voteTableName = $installer->getTable('tm_askit_vote');
$itemTableName = $installer->getTable('tm_askit_item');

if (!$connection->isTableExists($messageTableName)) {
    $table = $installer->getConnection()
        ->newTable($messageTableName)
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'Id')
        ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
            'unsigned'  => true,
            'nullable'  => true,
            'default'   => null,
            ), 'Parent Id')
        ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => 0,
            ), 'Store Id')
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            ), 'Customer ID')
        ->addColumn('customer_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
            'nullable'  => true,
            'default'   => '',
            ), 'Customer Name')
        ->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
            'nullable'  => false,
            'default'   => '',
            ), 'Email')
        ->addColumn('text', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
            'nullable'  => false,
            'default'   => '',
            ), 'Email')
        ->addColumn('hint', Varien_Db_Ddl_Table::TYPE_SMALLINT, 6, array(
            // 'unsigned'  => true,
            'nullable'  => false,
            'default'   => 0,
            ), 'Hint')
        ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
            // 'unsigned'  => true,
            'nullable'  => false,
            'default'   => 1,
            ), 'Hint')
        ->addColumn('created_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => false,
            ), 'Created At')
        ->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => false,
            ), 'Updated At')
        ->addColumn('private', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            // 'unsigned'  => true,
            'nullable'  => false,
            'default'   => 0,
            ), 'Is Private')
        ->addIndex(
            $installer->getIdxName('tm_askit_message', array('customer_id')),
            array('customer_id')
        )
        ->addIndex(
            $installer->getIdxName('tm_askit_message', array('store_id')),
            array('store_id')
        )
        ->addForeignKey(
            $installer->getFkName('tm_askit_message', 'customer_id', 'customer/entity', 'entity_id'),
            'customer_id',
            $installer->getTable('customer/entity'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_SET_NULL,
            Varien_Db_Ddl_Table::ACTION_SET_NULL
        )
        ->addForeignKey(
            $installer->getFkName('tm_askit_message', 'store_id', 'core/store', 'store_id'),
            'store_id',
            $installer->getTable('core/store'),
            'store_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('Askit Message Table');
    $installer->getConnection()->createTable($table);
}

if (!$connection->isTableExists($voteTableName)) {
    $table = $installer->getConnection()
        ->newTable($voteTableName)
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'Id')
        ->addColumn('message_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'unsigned'  => true,
            'nullable'  => true,
            'default'   => null,
            ), 'Message Id')
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            ), 'Customer ID')
        ->addIndex(
            $installer->getIdxName('tm_askit_vote', array('message_id')),
            array('message_id')
        )
        ->addIndex(
            $installer->getIdxName('tm_askit_vote', array('customer_id')),
            array('customer_id')
        )
        ->addForeignKey(
            $installer->getFkName('tm_askit_vote', 'message_id', 'tm_askit_message', 'id'),
            'message_id',
            $messageTableName,
            'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->addForeignKey(
            $installer->getFkName('tm_askit_vote', 'customer_id', 'customer/entity', 'entity_id'),
            'customer_id',
            $installer->getTable('customer/entity'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_SET_NULL,
            Varien_Db_Ddl_Table::ACTION_SET_NULL
        )
        ->setComment('Askit Vote Table');
    $installer->getConnection()->createTable($table);
}

if (!$connection->isTableExists($itemTableName)) {
    $table = $installer->getConnection()
        ->newTable($itemTableName)
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'Id')
        ->addColumn('message_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
            'unsigned'  => true,
            'nullable'  => true,
            'default'   => null,
            ), 'Massage Id')
        ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => true,
            'default'   => null,
            ), 'Item Id (product_id, cms page id or category id)')
        ->addColumn('item_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => TM_AskIt_Model_Item_Type::PRODUCT_ID,
            ), 'Item type id')
        ->addIndex(
            $installer->getIdxName('tm_askit_vote', array('message_id')),
            array('message_id')
        )
        ->addIndex(
            $installer->getIdxName(
                'tm_askit_item',
                array('message_id', 'item_id', 'item_type_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('message_id', 'item_id', 'item_type_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        )
        ->addForeignKey(
            $installer->getFkName('tm_askit_item', 'message_id', 'tm_askit_message', 'id'),
            'message_id',
            $installer->getTable('tm_askit_message'),
            'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('Askit Assign Item Table');
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();
