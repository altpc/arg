<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE {$installer->getTable('easyslide_slides')}
        ADD COLUMN `target_mode` TINYINT(1) NOT NULL DEFAULT 0 AFTER `target`;
");

$installer->endSetup();