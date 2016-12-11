<?php
$installer = $this;

$installer->startSetup();

$inst = new Mage_Eav_Model_Entity_Setup('core_setup');

    $inst->removeAttribute('catalog_product', 'prolabel_product_display');
    $inst->removeAttribute('catalog_product', 'prolabel_product_position');
    $inst->removeAttribute('catalog_product', 'prolabel_product_image');
    $inst->removeAttribute('catalog_product', 'prolabel_product_text');
    $inst->removeAttribute('catalog_product', 'prolabel_product_positionstyle');
    $inst->removeAttribute('catalog_product', 'prolabel_product_fontstyle');
    $inst->removeAttribute('catalog_product', 'prolabel_category_display');
    $inst->removeAttribute('catalog_product', 'prolabel_category_position');
    $inst->removeAttribute('catalog_product', 'prolabel_category_image');
    $inst->removeAttribute('catalog_product', 'prolabel_category_text');
    $inst->removeAttribute('catalog_product', 'prolabel_category_positionstyle');
    $inst->removeAttribute('catalog_product', 'prolabel_category_fontstyle');
    $inst->removeAttribute('catalog_product', 'prolabel_p_display');
    $inst->removeAttribute('catalog_product', 'prolabel_p_position');
    $inst->removeAttribute('catalog_product', 'prolabel_p_image');
    $inst->removeAttribute('catalog_product', 'prolabel_p_text');
    $inst->removeAttribute('catalog_product', 'prolabel_p_positionstyle');
    $inst->removeAttribute('catalog_product', 'prolabel_p_fontstyle');
    $inst->removeAttribute('catalog_product', 'prolabel_c_display');
    $inst->removeAttribute('catalog_product', 'prolabel_c_position');
    $inst->removeAttribute('catalog_product', 'prolabel_c_image');
    $inst->removeAttribute('catalog_product', 'prolabel_c_text');
    $inst->removeAttribute('catalog_product', 'prolabel_c_positionstyle');
    $inst->removeAttribute('catalog_product', 'prolabel_c_fontstyle');

$installer->run("

CREATE TABLE  {$this->getTable('prolabels/label')} (
  `rules_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `conditions_serialized` text,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `system` tinyint(1) NOT NULL DEFAULT '0',
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
  `product_min_stock` varchar(5) DEFAULT NULL,
  `product_out_stock` tinyint(1) unsigned DEFAULT NULL,
  `product_out_stock_image` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`rules_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO {$this->getTable('prolabels/label')} (`rules_id`,`name`,`conditions_serialized`,`status`,`system`,`product_position`,`product_image`,`product_image_text`,`product_position_style`,`product_font_style`,`product_round_method`,`product_round`,`category_position`,`category_image`,`category_image_text`,`category_position_style`,`category_font_style`,`category_round_method`,`category_round`,`category_min_stock`,`category_out_stock`,`category_out_stock_image`,`product_min_stock`,`product_out_stock`,`product_out_stock_image`) VALUES
 (1,'On Sale','a:6:{s:4:\"type\";s:32:\"prolabels/rule_condition_combine\";s:9:\"attribute\";N;s:8:\"operator\";N;s:5:\"value\";b:1;s:18:\"is_value_processed\";N;s:10:\"aggregator\";s:3:\"all\";}',0,1,'bottom-left',NULL,'#discount_amount#',NULL,NULL,'round','1','top-left',NULL,NULL,NULL,NULL,'round','1',NULL,NULL,NULL,NULL,NULL,NULL),
 (2,'In Stock','a:6:{s:4:\"type\";s:32:\"prolabels/rule_condition_combine\";s:9:\"attribute\";N;s:8:\"operator\";N;s:5:\"value\";b:1;s:18:\"is_value_processed\";N;s:10:\"aggregator\";s:3:\"all\";}',0,1,'top-right',NULL,NULL,NULL,NULL,'round','1','top-left',NULL,NULL,NULL,NULL,'round','1',NULL,0,NULL,'5',1,NULL),
 (3,'Is New','a:6:{s:4:\"type\";s:32:\"prolabels/rule_condition_combine\";s:9:\"attribute\";N;s:8:\"operator\";N;s:5:\"value\";b:1;s:18:\"is_value_processed\";N;s:10:\"aggregator\";s:3:\"all\";}',0,1,'top-left',NULL,NULL,NULL,NULL,'round','1','top-left',NULL,NULL,NULL,NULL,'round',NULL,NULL,NULL,NULL,NULL,NULL,NULL);

CREATE TABLE  {$this->getTable('prolabels/index')} (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `rules_id` int(11) NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product` (`product_id`),
  KEY `tm_fk_constraint_rules` (`rules_id`),
  CONSTRAINT `tm_fk_constraint_rules` FOREIGN KEY (`rules_id`) REFERENCES {$this->getTable('prolabels/label')} (`rules_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tm_fk_constraint_rules_product` FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog/product')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE  {$this->getTable('prolabels/store')} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tm_fk_constraint_pro_store` (`rule_id`),
  CONSTRAINT `tm_fk_constraint_pro_store` FOREIGN KEY (`rule_id`) REFERENCES {$this->getTable('prolabels/label')} (`rules_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

");

$installer->endSetup();