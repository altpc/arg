<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();
$messageTableName = $installer->getTable('tm_askit_message');
$voteTableName = $installer->getTable('tm_askit_vote');
$itemTableName = $installer->getTable('tm_askit_item');

$connection->resetDdlCache();

/**
 * Drop foreign keys
 */
$connection->dropForeignKey(
    $voteTableName,
    $installer->getFkName(
        $voteTableName,
        'item_id',
        $itemTableName,
        'id'
    )
);

$connection->dropForeignKey($voteTableName, 'FK_ASKIT_VOTE_ITEM_ID');

$connection->dropForeignKey(
    $voteTableName,
    $installer->getFkName(
        $voteTableName,
        'customer_id',
        $installer->getTable('customer/entity'),
        'entity_id'
    )
);

$connection->dropForeignKey($voteTableName, 'FK_ASKIT_VOTE_CUSTOMER_ID');

$fKeys = $connection->getForeignKeys($voteTableName);
foreach ($fKeys as $fKey) {
    $connection->dropForeignKey($voteTableName, $fKey['FK_NAME']);
}

/**
 * Drop indexes
 */
$connection->dropIndex(
    $voteTableName,
    $installer->getIdxName(
        $voteTableName,
        array('item_id')
    )
);

$connection->resetDdlCache($voteTableName);
// rename column tm_askit_vote
$connection->changeColumn(
    $voteTableName,
    'item_id',
    'message_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        // 'length'    => 11,
        'unsigned'  => true,
        'nullable'  => true,
        'default'   => null,
        'comment'   => 'Message Id'
    ),
    true
);

/**
 * Add indexes
 */
$connection->addIndex(
    $voteTableName,
    $installer->getIdxName($voteTableName, array('message_id')),
    array('message_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
);

/**
 * Add foreign keys
 */
$connection->addForeignKey(
    $installer->getFkName($voteTableName, 'message_id', $messageTableName, 'id'),
    $voteTableName,
    'message_id',
    $messageTableName,
    'id'
);

$connection->addForeignKey(
    $installer->getFkName($voteTableName, 'customer_id', $installer->getTable('customer/entity'), 'entity_id'),
    $voteTableName,
    'message_id',
    $installer->getTable('customer/entity'),
    'entity_id'
);

$installer->endSetup();
