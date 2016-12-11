<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('prolabels/label')}
    ADD COLUMN `category_custom_url` TEXT NULL,
    ADD COLUMN `product_custom_url` TEXT NULL;

ALTER TABLE {$this->getTable('prolabels/system')}
    ADD COLUMN `category_custom_url` TEXT NULL,
    ADD COLUMN `product_custom_url` TEXT NULL;
");

$installer->endSetup();