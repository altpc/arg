<?php
$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE  {$this->getTable('prolabels/system')} (
  `system_id` int(11) NOT NULL AUTO_INCREMENT,
  `rules_id` int(11) NOT NULL,
  `system_label_name` varchar(30) DEFAULT NULL,
  `l_status` tinyint(1) DEFAULT NULL,
  `product_position` varchar(25) DEFAULT NULL,
  `product_image` varchar(50) DEFAULT NULL,
  `product_image_text` varchar(50) DEFAULT NULL,
  `product_position_style` varchar(80) DEFAULT NULL,
  `product_font_style` varchar(80) DEFAULT NULL,
  `product_round_method` varchar(15) DEFAULT NULL,
  `product_round` varchar(5) DEFAULT NULL,
  `category_position` varchar(25) DEFAULT NULL,
  `category_image` varchar(50) DEFAULT NULL,
  `category_image_text` varchar(50) DEFAULT NULL,
  `category_position_style` varchar(80) DEFAULT NULL,
  `category_font_style` varchar(80) DEFAULT NULL,
  `category_round_method` varchar(15) DEFAULT NULL,
  `category_round` varchar(5) DEFAULT NULL,
  `category_min_stock` varchar(5) DEFAULT NULL,
  `category_out_stock` tinyint(1) unsigned DEFAULT NULL,
  `category_out_stock_image` varchar(45) DEFAULT NULL,
  `category_out_text` varchar(45) DEFAULT NULL,
  `product_min_stock` varchar(5) DEFAULT NULL,
  `product_out_stock` tinyint(1) unsigned DEFAULT NULL,
  `product_out_stock_image` varchar(45) DEFAULT NULL,
  `product_out_text` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`system_id`),
  CONSTRAINT `tm_fk_constraint_system_rules` 
    FOREIGN KEY (`rules_id`) REFERENCES {$this->getTable('prolabels/label')} (`rules_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE  {$this->getTable('prolabels/sysstore')} (
  `system_store_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(10) DEFAULT NULL,
  `system_id` int(11) DEFAULT NULL,
  `rules_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`system_store_id`),
  KEY `fk_tm_prolabels_system_store_label` (`system_id`),
  CONSTRAINT `fk_tm_prolabels_system_store_label` 
      FOREIGN KEY (`system_id`) REFERENCES {$this->getTable('prolabels/system')} (`system_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

");

$installer->endSetup();