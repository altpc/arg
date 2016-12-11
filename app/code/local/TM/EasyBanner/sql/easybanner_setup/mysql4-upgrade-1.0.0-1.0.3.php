<?php

/**
 * @var Mage_Core_Model_Resource_Setup
 */
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('easybanner_banner_statistic')}
 DROP FOREIGN KEY `FK_easybanner_banner_statistic_banner_id`;

ALTER TABLE {$this->getTable('easybanner_banner_statistic')}
 ADD COLUMN `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
 DROP PRIMARY KEY,
 ADD PRIMARY KEY  USING BTREE(`id`);

ALTER TABLE {$this->getTable('easybanner_banner_statistic')}
 ADD CONSTRAINT `FK_easybanner_banner_statistic_banner_id` FOREIGN KEY `FK_easybanner_banner_statistic_banner_id` (`banner_id`)
    REFERENCES {$this->getTable('easybanner_banner')} (`banner_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

");

$installer->endSetup();
