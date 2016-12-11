<?php

$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$messageTableName = $installer->getTable('tm_askit_message');
$voteTableName = $installer->getTable('tm_askit_vote');
$itemTableName = $installer->getTable('tm_askit_item');

if (!$connection->isTableExists($messageTableName)) {
    $table = $connection
        ->newTable($messageTableName)
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'Id')
        ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
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
            // 'default'   => '',
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
            $installer->getIdxName($messageTableName, array('customer_id')),
            array('customer_id')
        )
        ->addIndex(
            $installer->getIdxName($messageTableName, array('store_id')),
            array('store_id')
        )
        ->addForeignKey(
            $installer->getFkName($messageTableName, 'customer_id', 'customer/entity', 'entity_id'),
            'customer_id',
            $installer->getTable('customer/entity'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_SET_NULL,
            Varien_Db_Ddl_Table::ACTION_SET_NULL
        )
        ->addForeignKey(
            $installer->getFkName($messageTableName, 'store_id', 'core/store', 'store_id'),
            'store_id',
            $installer->getTable('core/store'),
            'store_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('Askit Message Table');
    $connection->createTable($table);
}

if (!$connection->isTableExists($voteTableName)) {
    $table = $connection
        ->newTable($voteTableName)
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'Id')
        ->addColumn('message_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => true,
            'default'   => null,
            ), 'Message Id')
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            ), 'Customer ID')
        ->addIndex(
            $installer->getIdxName($voteTableName, array('message_id')),
            array('message_id')
        )
        ->addIndex(
            $installer->getIdxName($voteTableName, array('customer_id')),
            array('customer_id')
        )
        ->addForeignKey(
            $installer->getFkName($voteTableName, 'message_id', $messageTableName, 'id'),
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
    $connection->createTable($table);
}

if (!$connection->isTableExists($itemTableName)) {
    $uniqueType = Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE;
    $uniqueType = true ? array('type' => $uniqueType) : $uniqueType;
    $table = $connection
        ->newTable($itemTableName)
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'Id')
        ->addColumn('message_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
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
            $installer->getIdxName($voteTableName, array('message_id')),
            array('message_id')
        )->addIndex(
            $installer->getIdxName(
                $itemTableName,
                array('message_id', 'item_id', 'item_type_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('message_id', 'item_id', 'item_type_id'),
            $uniqueType
        )
        ->addForeignKey(
            $installer->getFkName($itemTableName, 'message_id', $messageTableName, 'id'),
            'message_id',
            $messageTableName,
            'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('Askit Assign Item Table');
    $connection->createTable($table);
}

$installer->endSetup();
