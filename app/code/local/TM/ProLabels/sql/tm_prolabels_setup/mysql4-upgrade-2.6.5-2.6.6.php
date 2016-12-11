<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('prolabels/label')}
    ADD COLUMN `customer_group` TEXT DEFAULT NULL;

ALTER TABLE {$this->getTable('prolabels/system')}
    ADD COLUMN `customer_group` TEXT DEFAULT NULL;
");

$installer->endSetup();