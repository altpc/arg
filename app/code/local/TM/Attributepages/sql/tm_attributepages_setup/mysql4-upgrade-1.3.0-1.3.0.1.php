<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getTable('attributepages/entity');

$installer->getConnection()->addColumn($table, 'page_title',
    "VARCHAR(255) NOT NULL DEFAULT '' AFTER `title`");

$installer->endSetup();
