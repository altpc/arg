<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();
$messageTableName = $installer->getTable('tm_askit_message');
$voteTableName = $installer->getTable('tm_askit_vote');
$itemTableName = $installer->getTable('tm_askit_item');

if (!$connection->isTableExists($itemTableName)) {
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
        )
        ->addForeignKey(
            $installer->getFkName($itemTableName, 'message_id', 'tm_askit_message', 'id'),
            'message_id',
            $messageTableName,
            'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('Askit Assign Item Table');
    $connection->createTable($table);

    $select = $connection->select()->from($messageTableName);

    $messages = $connection->fetchAll($select);
    foreach ($messages as $id => $message) {
        if (!empty($message['item_id']) && !empty($message['item_type_id'])  && !empty($message['id'])) {
            $connection->insert($itemTableName, array(
                'message_id'   => $message['id'],
                'item_id'      => $message['item_id'],
                'item_type_id' => $message['item_type_id'],
            ));
        }
    }

    $connection->dropColumn($messageTableName, 'item_id');
    $connection->dropColumn($messageTableName, 'item_type_id');

    $installer->getConnection()->addIndex(
        $itemTableName,
        $installer->getIdxName(
            $itemTableName,
            array('message_id', 'item_id', 'item_type_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('message_id', 'item_id', 'item_type_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );
}

$installer->endSetup();
