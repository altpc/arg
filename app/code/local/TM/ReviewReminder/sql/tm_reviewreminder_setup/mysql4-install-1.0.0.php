<?php

$installer = $this;
$installer->startSetup();
/**
 * Create table 'tm_reviewreminder/entity'
 */
if ($installer->getConnection()->isTableExists($installer->getTable('tm_reviewreminder/entity')) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('tm_reviewreminder/entity'))
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true
        ), 'Entity id')
        ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false
        ), 'Order id')
        ->addColumn('order_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => false
        ), 'Order date')
        ->addColumn('customer_email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable'  => false
        ), 'Customer email')
        ->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => TM_ReviewReminder_Model_Entity::STATUS_NEW
        ), 'Status')
        ->addColumn('hash', Varien_Db_Ddl_Table::TYPE_TEXT, 16, array(
            'nullable' => false
        ), 'Reminder Hash')
        ->setComment('Templates Master Review Reminder Entity Table');
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();