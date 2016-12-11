<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE {$installer->getTable('easyslide')}
        ADD COLUMN `slider_type` TINYINT(1) NOT NULL DEFAULT 0  AFTER `effect`, 
        ADD COLUMN `nivo_options` TEXT NULL DEFAULT NULL  AFTER `slider_type` ;
");

$installer->endSetup();