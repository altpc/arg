<?php

/**
 * @var Mage_Core_Model_Resource_Setup
 */
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('easybanner_placeholder')}
 ADD COLUMN `sort_mode` enum('sort_order','random') NOT NULL DEFAULT 'sort_order';

");

$installer->endSetup();
