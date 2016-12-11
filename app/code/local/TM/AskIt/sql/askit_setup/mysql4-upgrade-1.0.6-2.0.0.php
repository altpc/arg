<?php
$installer = $this;
$installer->startSetup();

$table = $installer->getTable('askit_item');

$installer->getConnection()->dropForeignKey($table, 'FK_LINK_PRODUCT_ASKIT');

$installer->getConnection()->addColumn($table, 'item_type_id', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => TM_AskIt_Model_Item_Type::PRODUCT_ID,
        'comment'   => 'Item Type Id'
    )
);

$installer->getConnection()->changeColumn(
    $table,
    'product_id',
    'item_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment'   => 'Item Id (product_id, cms page id or category id)'
    )
);

//$installer->run("
//    ALTER TABLE `{$table}` RENAME TO `{$installer->getTable('askit/item')}`;
//");

//$collection = Mage::getModel('askit/item')->getCollection();
//$default = TM_AskIt_Model_Item_Type::PRODUCT_ID;
//foreach ($collection as $row) {
//    $row->setItemTypeId($default)
//    ->save();
//}

$installer->endSetup();