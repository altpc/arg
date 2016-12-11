<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();
$messageTableName = $installer->getTable('tm_askit_message');
$voteTableName = $installer->getTable('tm_askit_vote');
$itemTableName = $installer->getTable('tm_askit_item');

/**
 * Drop foreign keys
 */
$connection->dropForeignKey(
    $itemTableName,
    $installer->getFkName(
        $itemTableName,
        'customer_id',
        $installer->getTable('customer/entity'),
        'entity_id'
    )
);
$connection->dropForeignKey($itemTableName, 'FK_LINK_CUSTOMER_ASKIT');

$connection->dropForeignKey(
    $itemTableName,
    $installer->getFkName(
        $itemTableName,
        'store_id',
        $installer->getTable('core/store'),
        'store_id'
    )
);
$connection->dropForeignKey($itemTableName, 'FK_LINK_STORE_ASKIT');

/**
 * Drop indexes
 */
$connection->dropIndex(
    $itemTableName,
    $installer->getIdxName(
        $itemTableName,
        array('customer_id')
    )
);

$connection->dropIndex(
    $itemTableName,
    $installer->getIdxName(
        $itemTableName,
        array('store_id')
    )
);
$connection->dropIndex($itemTableName, 'FK_LINK_PRODUCT_ASKIT');
$connection->dropIndex($itemTableName, 'FK_LINK_CUSTOMER_ASKIT');
$connection->dropIndex($itemTableName, 'FK_LINK_STORE_ASKIT');

// rename table tm_askit_item to tm_askit_message
$connection->renameTable($itemTableName, $messageTableName);

/**
 * Add indexes
 */
$connection->addIndex(
    $messageTableName,
    $installer->getIdxName($messageTableName, array('customer_id')),
    array('customer_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
);

$connection->addIndex(
    $messageTableName,
    $installer->getIdxName($messageTableName, array('store_id')),
    array('store_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
);

/**
 * Add foreign keys
 */
$connection->addForeignKey(
    $installer->getFkName(
        $messageTableName,
        'customer_id',
        $installer->getTable('customer/entity'),
        'entity_id'
    ),
    $messageTableName,
    'customer_id',
    $installer->getTable('customer/entity'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL,
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$connection->addForeignKey(
    $installer->getFkName(
        $messageTableName,
        'store_id',
        $installer->getTable('core/store'),
        'store_id'
    ),
    $messageTableName,
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$installer->endSetup();
