<?php

/**
 * @var Mage_Core_Model_Resource_Setup
 */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->changeColumn(
    $this->getTable('easybanner/banner'),
    'class_name',
    'class_name',
    "VARCHAR(256) DEFAULT '' NOT NULL"
);

$installer->endSetup();
