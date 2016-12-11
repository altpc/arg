<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$tableNameGatawayStorage = $installer->getTable('tm_email_gateway_storage');
if (!$connection->isTableExists($tableNameGatawayStorage)) {
    $table = $connection
        ->newTable($tableNameGatawayStorage)
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'ID')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 45, array(), 'Name')
        ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
            'nullable'  => false,
            'default'   => 1,
            ), 'Status')
        ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
            'nullable'  => false,
            'default'   => 1,
            ), 'Type (1- pop3, 2 - imap)')
        ->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
            'nullable'  => false,
            'default'   => '',
            ), 'Email')
        ->addColumn('host', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
            'nullable'  => false,
            'default'   => '',
            ), 'Host')
        ->addColumn('user', Varien_Db_Ddl_Table::TYPE_TEXT, 45, array(
            'nullable'  => false,
            'default'   => '',
            ), 'User')
        ->addColumn('password', Varien_Db_Ddl_Table::TYPE_TEXT, 45, array(
            'nullable'  => false,
            'default'   => '',
            ), 'Password')
        ->addColumn('port', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => 110,
            ), 'Port')
        ->addColumn('secure', Varien_Db_Ddl_Table::TYPE_SMALLINT, 1, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => 0,
            ), 'Secure (1 - ssl, 2 - tsl else none)')
        ->addColumn('remove', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
            'nullable'  => false,
            'default'   => 0,
            ), 'Remove')
        ;
    $connection->createTable($table);


    $installer->run("

    INSERT INTO `{$this->getTable('tm_email_gateway_storage')}`(`id`,`name`,`status`,`email`,`host`,`user`,`port`)
    VALUES (1, 'Default', 0, 'your email here', 'your host here', 'your user here', 110);

    ");
}

$tableNameGatawayTransport = $installer->getTable('tm_email_gateway_transport');
if (!$connection->isTableExists($tableNameGatawayTransport)) {
    $table = $connection
        ->newTable($tableNameGatawayTransport)
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'ID')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 45, array(), 'Name')
        ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
            'nullable'  => false,
            'default'   => 1,
            ), 'Status')
        ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
            'nullable'  => false,
            'default'   => 1,
            ), 'Type (1 - smtp else sendmail)')
        ->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
            'nullable'  => false,
            'default'   => '',
            ), 'Email')
        ->addColumn('host', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
            'nullable'  => false,
            'default'   => '',
            ), 'Host')
        ->addColumn('user', Varien_Db_Ddl_Table::TYPE_TEXT, 45, array(
            'nullable'  => false,
            'default'   => '',
            ), 'User')
        ->addColumn('password', Varien_Db_Ddl_Table::TYPE_TEXT, 45, array(
            'nullable'  => false,
            'default'   => '',
            ), 'Password')
        ->addColumn('port', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => 110,
            ), 'Port')
        ->addColumn('secure', Varien_Db_Ddl_Table::TYPE_SMALLINT, 1, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => 0,
            ), 'Secure (1 - ssl, 2 - tsl else none)')
        ->addColumn('auth', Varien_Db_Ddl_Table::TYPE_VARCHAR, 7, array(
            'nullable'  => false,
            'default'   => 'login',
            ), 'Password')
        ->addColumn('custom', Varien_Db_Ddl_Table::TYPE_VARCHAR, 8192, array(
            'nullable'  => false,
        ), 'Serialised custom options')
    ;
    $connection->createTable($table);
    $installer->run("

    INSERT INTO `{$this->getTable('tm_email_gateway_transport')}`(`id`,`name`,`status`,`email`,`host`,`user`,`port`)
    VALUES (1, 'Default', 0, 'your email here', 'your host here', 'your user here', 110);

    ");
}

$tableNameQueueMessage = $installer->getTable('tm_email_queue_message');
if (!$connection->isTableExists($tableNameQueueMessage)) {

    $table = $connection
        ->newTable($tableNameQueueMessage)
        ->addColumn('message_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'Message ID')
        ->addColumn('queue_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
            'unsigned'  => true,
            'nullable'  => false,
            ), 'Queue Id')
        ->addColumn('handle', Varien_Db_Ddl_Table::TYPE_CHAR, 32, array(
            'default'   => null,
            ), 'Handle')
        ->addColumn('body', Varien_Db_Ddl_Table::TYPE_VARCHAR, 8192, array(
            'nullable'  => false,
            ), 'Body')
        ->addColumn('md5', Varien_Db_Ddl_Table::TYPE_CHAR, 32, array(
            'nullable'  => false,
            ), 'MD5')
        ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, 1, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => TM_Email_Model_Queue_Message_Status::APPROVED,
            ), 'Status')
        ->addColumn('timeout', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
            'precision' => 14,
            'scale'     => 4,
            'unsigned'  => true,
            'default'   => null,
            ), 'Timeout')
        ->addColumn('created', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
            'unsigned'  => true,
            'nullable'  => false,
            ), 'Created')
        ;
        $connection->createTable($table);

    $connection->addIndex(
        $installer->getTable('tm_email_queue_message'),
        $installer->getIdxName(
            'tm_email_queue_message',
            array('handle'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('handle'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );

    $connection->addIndex(
        $installer->getTable('tm_email_queue_message'),
        $installer->getIdxName('tm_email_queue_message', array('queue_id')),
        array('queue_id')
    );
}

$tableNameQueueQueue = $installer->getTable('tm_email_queue_queue');
if (!$connection->isTableExists($tableNameQueueQueue)) {

    $table = $connection
        ->newTable($tableNameQueueQueue)
        ->addColumn('queue_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'Queue ID')
        ->addColumn('queue_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
            'nullable'  => false,
            ), 'Queue Name')
        ->addColumn('default_status', Varien_Db_Ddl_Table::TYPE_SMALLINT, 1, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => TM_Email_Model_Queue_Message_Status::APPROVED,
            ), 'Status')
        ->addColumn('timeout', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
            'nullable'  => false,
            'default'   => 30,
            ), 'Timeout')
        ;
    $connection->createTable($table);

    /**
     * Add foreign key
     */
    $connection->addForeignKey(
        $installer->getFkName('tm_email_queue_message', 'queue_id', 'tm_email_queue_queue', 'queue_id'),
        $installer->getTable('tm_email_queue_message'),
        'queue_id',
        $installer->getTable('tm_email_queue_queue'),
        'queue_id'
    );
}
// throw new Exception;

$installer->endSetup();
