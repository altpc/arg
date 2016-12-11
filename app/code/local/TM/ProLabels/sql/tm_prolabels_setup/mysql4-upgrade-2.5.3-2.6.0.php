<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('prolabels/label')}
    ADD COLUMN `priority` VARCHAR(20) DEFAULT '0';

ALTER TABLE {$this->getTable('prolabels/system')}
    ADD COLUMN `priority` VARCHAR(20) DEFAULT '0';
");

$installer->endSetup();