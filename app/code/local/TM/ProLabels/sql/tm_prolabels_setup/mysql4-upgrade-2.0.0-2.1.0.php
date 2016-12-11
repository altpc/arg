<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('prolabels/label')}
    ADD COLUMN `category_out_text` VARCHAR(45) NULL  AFTER `product_out_stock_image` ,
    ADD COLUMN `product_out_text` VARCHAR(45) NULL  AFTER `product_out_stock_image`;

ALTER TABLE {$this->getTable('prolabels/label')}

    CHANGE COLUMN `name` `label_name` VARCHAR(40) NOT NULL,
    CHANGE COLUMN `status` `label_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    CHANGE COLUMN `system` `system_label` TINYINT(1) NOT NULL DEFAULT '0';
");

$installer->endSetup();