<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE {$installer->getTable('easyslide_slides')}
        ADD COLUMN `desc_pos` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER `description`,
        ADD COLUMN `background` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER `desc_pos`;
");

$installer->endSetup();