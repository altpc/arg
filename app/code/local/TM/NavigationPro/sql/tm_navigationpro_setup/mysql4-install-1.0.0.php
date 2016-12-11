<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('tm_navigationpro_menu')}` (
  `menu_id` integer unsigned NOT NULL AUTO_INCREMENT,
  `root_menu_id` integer unsigned DEFAULT NULL,
  `category_id` integer unsigned DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `is_active` tinyint unsigned NOT NULL DEFAULT '1',
  `columns_mode` enum('menu','custom','parent') NOT NULL DEFAULT 'menu',
  `display_in_navigation` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`menu_id`),
  KEY `IDX_NAME` (`name`),
  KEY `IDX_IS_ACTIVE` USING HASH (`is_active`),
  KEY `FK_NAVIGATIONPRO_ROOT_MENU_ID` (`root_menu_id`),
  KEY `FK_NAVIGATIONPRO_CATEGORY_ID` (`category_id`),
  CONSTRAINT `FK_NAVIGATIONPRO_CATEGORY_ID` FOREIGN KEY (`category_id`)
    REFERENCES `{$this->getTable('catalog_category_entity')}` (`entity_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_NAVIGATIONPRO_ROOT_MENU_ID` FOREIGN KEY (`root_menu_id`)
    REFERENCES `{$this->getTable('tm_navigationpro_menu')}` (`menu_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('tm_navigationpro_menu_content')}` (
  `menu_id` integer unsigned NOT NULL,
  `store_id` smallint unsigned NOT NULL,
  `top` text,
  `bottom` text,
  `title` text,
  PRIMARY KEY (`menu_id`,`store_id`),
  KEY `FK_NAVIGATIONPRO_MENU_CONTENT_STORE` (`store_id`),
  KEY `FK_NAVIGATIONPRO_MENU_CONTENT_MENU_ID` (`menu_id`),
  CONSTRAINT `FK_NAVIGATIONPRO_MENU_CONTENT_MENU_ID` FOREIGN KEY (`menu_id`)
    REFERENCES `{$this->getTable('tm_navigationpro_menu')}` (`menu_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_NAVIGATIONPRO_MENU_CONTENT_STORE` FOREIGN KEY (`store_id`)
    REFERENCES `{$this->getTable('core_store')}` (`store_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('tm_navigationpro_column')}` (
  `column_id` integer unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` integer unsigned NOT NULL,
  `is_active` tinyint unsigned NOT NULL DEFAULT '1',
  `sort_order` smallint unsigned NOT NULL DEFAULT '50',
  `configuration` text NOT NULL,
  PRIMARY KEY (`column_id`),
  KEY `IDX_IS_ACTIVE` (`is_active`),
  KEY `IDX_SORT_ORDER` USING BTREE (`sort_order`),
  KEY `FK_NAVIGATIONPRO_COLUMN_MENU_ID` (`menu_id`),
  CONSTRAINT `FK_NAVIGATIONPRO_COLUMN_MENU_ID` FOREIGN KEY (`menu_id`)
    REFERENCES `{$this->getTable('tm_navigationpro_menu')}` (`menu_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('tm_navigationpro_column_content')}` (
  `column_id` integer unsigned NOT NULL,
  `store_id` smallint unsigned NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`column_id`,`store_id`),
  KEY `FK_NAVIGATIONPRO_COLUMN_CONTENT_STORE` (`store_id`),
  KEY `FK_NAVIGATIONPRO_COLUMN_CONTENT_COLUMN_ID` (`column_id`),
  CONSTRAINT `FK_NAVIGATIONPRO_COLUMN_CONTENT_COLUMN_ID` FOREIGN KEY (`column_id`)
    REFERENCES `{$this->getTable('tm_navigationpro_column')}` (`column_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_NAVIGATIONPRO_COLUMN_CONTENT_STORE` FOREIGN KEY (`store_id`)
    REFERENCES `{$this->getTable('core_store')}` (`store_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('tm_navigationpro_sibling')}` (
  `sibling_id` integer unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` integer unsigned NOT NULL,
  `is_active` tinyint unsigned NOT NULL DEFAULT '1',
  `sort_order` smallint NOT NULL DEFAULT '50',
  `dropdown_styles` text,
  PRIMARY KEY (`sibling_id`),
  KEY `IDX_IS_ACTIVE` (`is_active`),
  KEY `IDX_SORT_ORDER` USING BTREE (`sort_order`),
  KEY `FK_NAVIGATIOPRO_SIBLING_MENU_ID` (`menu_id`),
  CONSTRAINT `FK_NAVIGATIOPRO_SIBLING_MENU_ID` FOREIGN KEY (`menu_id`)
    REFERENCES `{$this->getTable('tm_navigationpro_menu')}` (`menu_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('tm_navigationpro_sibling_content')}` (
  `sibling_id` integer unsigned NOT NULL,
  `store_id` smallint unsigned NOT NULL,
  `content` text,
  `dropdown_content` text,
  PRIMARY KEY (`sibling_id`,`store_id`),
  KEY `FK_NAVIGATIOPRO_SIBLING_CONTENT_SIBLING_ID` (`sibling_id`),
  KEY `FK_NAVIGATIOPRO_SIBLING_CONTENT_STORE_ID` (`store_id`),
  CONSTRAINT `FK_NAVIGATIOPRO_SIBLING_CONTENT_SIBLING_ID` FOREIGN KEY (`sibling_id`)
    REFERENCES `{$this->getTable('tm_navigationpro_sibling')}` (`sibling_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_NAVIGATIOPRO_SIBLING_CONTENT_STORE_ID` FOREIGN KEY (`store_id`)
    REFERENCES `{$this->getTable('core_store')}` (`store_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
