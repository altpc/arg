<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE {$installer->getTable('easyslide')} ADD COLUMN `effect` VARCHAR(30) NOT NULL DEFAULT 'scroll' AFTER `modified_time`;
");

$installer->endSetup();