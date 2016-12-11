<?php

/**
 * @var Mage_Core_Model_Resource_Setup
 */
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('easybanner_banner')};
CREATE TABLE  {$this->getTable('easybanner_banner')} (
  `banner_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(64) NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT '100',
  `title` text NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '#',
  `image` varchar(255) NOT NULL,
  `html` text NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `mode` enum('image','html') NOT NULL DEFAULT 'image',
  `target` enum('self','blank','popup') NOT NULL,
  `hide_url` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `conditions_serialized` text NOT NULL,
  PRIMARY KEY (`banner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('easybanner_banner_placeholder')};
CREATE TABLE  {$this->getTable('easybanner_banner_placeholder')} (
  `banner_id` smallint(6) unsigned NOT NULL,
  `placeholder_id` smallint(6) unsigned NOT NULL,
  PRIMARY KEY (`banner_id`,`placeholder_id`),
  KEY `FK_easybanner_banner_placeholder_placeholder_id` (`placeholder_id`),
  CONSTRAINT `FK_easybanner_banner_placeholder_banner_id` FOREIGN KEY (`banner_id`) REFERENCES {$this->getTable('easybanner_banner')} (`banner_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_easybanner_banner_placeholder_placeholder_id` FOREIGN KEY (`placeholder_id`) REFERENCES {$this->getTable('easybanner_placeholder')} (`placeholder_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('easybanner_banner_statistic')};
CREATE TABLE  {$this->getTable('easybanner_banner_statistic')} (
  `banner_id` smallint(6) unsigned NOT NULL,
  `date` date NOT NULL,
  `display_count` int(10) unsigned NOT NULL DEFAULT '0',
  `clicks_count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  USING BTREE (`banner_id`,`date`),
  CONSTRAINT `FK_easybanner_banner_statistic_banner_id` FOREIGN KEY (`banner_id`) REFERENCES {$this->getTable('easybanner_banner')} (`banner_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('easybanner_banner_store')};
CREATE TABLE  {$this->getTable('easybanner_banner_store')} (
  `banner_id` smallint(6) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`banner_id`,`store_id`),
  KEY `FK_easybanner_banner_store_store_id` (`store_id`),
  CONSTRAINT `FK_easybanner_banner_store_banner_id` FOREIGN KEY (`banner_id`) REFERENCES {$this->getTable('easybanner_banner')} (`banner_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_easybanner_banner_store_store_id` FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core_store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('easybanner_placeholder')};
CREATE TABLE  {$this->getTable('easybanner_placeholder')} (
  `placeholder_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `parent_block` varchar(64) NOT NULL,
  `position` varchar(128) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `limit` tinyint(3) unsigned NOT NULL,
  `mode` enum('rotator','slider') NOT NULL DEFAULT 'rotator',
  `banner_offset` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`placeholder_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

INSERT INTO {$this->getTable('easybanner_placeholder')} (`placeholder_id`, `name`, `parent_block`, `position`, `status`, `limit`, `mode`, `banner_offset`) VALUES
(1, 'left-top', 'left', 'before=\"-\"', 1, 1, 'rotator', 1),
(2, 'right-top', 'right', 'before=\"-\"', 1, 1, 'rotator', 1),
(3, 'right-bottom', 'right', 'after=\"-\"', 1, 1, 'rotator', 1),
(4, 'left-bottom', 'left', 'after=\"-\"', 1, 1, 'rotator', 1),
(5, 'content-top', 'content', 'before=\"-\"', 1, 1, 'rotator', 1),
(6, 'content-bottom', 'content', 'after=\"-\"', 1, 1, 'rotator', 1),
(7, 'nav-top', 'top.menu', 'before=\"-\"', 1, 1, 'rotator', 1),
(8, 'nav-bottom', 'top.menu', 'after=\"-\"', 1, 1, 'rotator', 1),
(9, 'page-bottom', 'before_body_end', 'after=\"-\"', 1, 1, 'rotator', 1);

-- DROP TABLE IF EXISTS {$this->getTable('easybanner_layout_update')};
CREATE TABLE  {$this->getTable('easybanner_layout_update')} (
  `layout_update_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `handle` varchar(255) DEFAULT NULL,
  `xml` text,
  PRIMARY KEY (`layout_update_id`),
  KEY `handle` (`handle`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

INSERT INTO {$this->getTable('easybanner_layout_update')} (`layout_update_id`, `handle`, `xml`) VALUES
(1, 'default', '<reference name=\"left\">\n  <block type=\"easybanner/placeholder\" name=\"easybanner.placeholder.left-top\" before=\"-\">\n    <action method=\"setPlaceholderId\"><id>1</id></action>\n  </block>\n</reference>'),
(2, 'default', '<reference name=\"right\">\n  <block type=\"easybanner/placeholder\" name=\"easybanner.placeholder.right-top\" before=\"-\">\n    <action method=\"setPlaceholderId\"><id>2</id></action>\n  </block>\n</reference>'),
(3, 'default', '<reference name=\"right\">\n  <block type=\"easybanner/placeholder\" name=\"easybanner.placeholder.right-bottom\" after=\"-\">\n    <action method=\"setPlaceholderId\"><id>3</id></action>\n  </block>\n</reference>'),
(4, 'default', '<reference name=\"left\">\n  <block type=\"easybanner/placeholder\" name=\"easybanner.placeholder.left-bottom\" after=\"-\">\n    <action method=\"setPlaceholderId\"><id>4</id></action>\n  </block>\n</reference>'),
(5, 'default', '<reference name=\"content\">\n  <block type=\"easybanner/placeholder\" name=\"easybanner.placeholder.content-top\" before=\"-\">\n    <action method=\"setPlaceholderId\"><id>5</id></action>\n  </block>\n</reference>'),
(6, 'default', '<reference name=\"content\">\n  <block type=\"easybanner/placeholder\" name=\"easybanner.placeholder.content-bottom\" after=\"-\">\n    <action method=\"setPlaceholderId\"><id>6</id></action>\n  </block>\n</reference>'),
(7, 'default', '<reference name=\"top.menu\">\n  <block type=\"easybanner/placeholder\" name=\"easybanner.placeholder.nav-top\" before=\"-\">\n    <action method=\"setPlaceholderId\"><id>7</id></action>\n  </block>\n</reference>'),
(8, 'default', '<reference name=\"top.menu\">\n  <block type=\"easybanner/placeholder\" name=\"easybanner.placeholder.nav-bottom\" after=\"-\">\n    <action method=\"setPlaceholderId\"><id>8</id></action>\n  </block>\n</reference>'),
(9, 'default', '<reference name=\"before_body_end\">\n  <block type=\"easybanner/placeholder\" name=\"easybanner.placeholder.page-bottom\" after=\"-\">\n    <action method=\"setPlaceholderId\"><id>9</id></action>\n  </block>\n</reference>');

-- DROP TABLE IF EXISTS {$this->getTable('easybanner_layout_link')};
CREATE TABLE  {$this->getTable('easybanner_layout_link')} (
  `layout_link_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `placeholder_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `banner_id` smallint(6) unsigned DEFAULT NULL,
  `layout_update_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`layout_link_id`),
  UNIQUE KEY `KEY_store_id` (`store_id`,`placeholder_id`,`banner_id`,`layout_update_id`),
  KEY `FK_easybanner_layout_link_layout_update_id` (`layout_update_id`),
  KEY `FK_easybanner_layout_link_placeholder_id` (`placeholder_id`),
  KEY `FK_easybanner_layout_link_banner_id` (`banner_id`),
  CONSTRAINT `FK_easybanner_layout_link_layout_update_id` FOREIGN KEY (`layout_update_id`) REFERENCES {$this->getTable('easybanner_layout_update')} (`layout_update_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

INSERT INTO {$this->getTable('easybanner_layout_link')} (`layout_link_id`, `store_id`, `placeholder_id`, `banner_id`, `layout_update_id`) VALUES
(1, 0, 1, NULL, 1),
(2, 0, 2, NULL, 2),
(3, 0, 3, NULL, 3),
(4, 0, 4, NULL, 4),
(5, 0, 5, NULL, 5),
(6, 0, 6, NULL, 6),
(7, 0, 7, NULL, 7),
(8, 0, 8, NULL, 8),
(9, 0, 9, NULL, 9);

");

$installer->endSetup();
