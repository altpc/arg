<?php
$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE {$this->getTable('soldtogether/customer')} (
  `relation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `related_product_id` int(10) unsigned NOT NULL,
  `weight` int(10) unsigned NOT NULL,
  `is_admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY USING BTREE (`relation_id`) ,
  UNIQUE KEY `UK1` (`product_id`,`related_product_id`),
  KEY `FK_TM_SOLDTOGETHER_CUSTOMER_PRODUCT` (`product_id`),
  KEY `FK_TM_SOLDTOGETHER_CUSTOMER_RELATED` (`related_product_id`),
  CONSTRAINT `FK_TM_SOLDTOGETHER_CUSTOMER_PRODUCT` FOREIGN KEY (`product_id`)
    REFERENCES {$this->getTable('catalog/product')} (`entity_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_TM_SOLDTOGETHER_CUSTOMER_RELATED` FOREIGN KEY (`related_product_id`)
    REFERENCES {$this->getTable('catalog/product')} (`entity_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('soldtogether/order')} (
  `relation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `related_product_id` int(10) unsigned NOT NULL,
  `weight` int(10) unsigned NOT NULL,
  `is_admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY USING BTREE (`relation_id`) ,
  UNIQUE KEY `UK1` (`product_id`,`related_product_id`),
  KEY `FK_TM_SOLDTOGETHER_ORDER_PRODUCT` (`product_id`),
  KEY `FK_TM_SOLDTOGETHER_ORDER_RELATED` (`related_product_id`),
  CONSTRAINT `FK_TM_SOLDTOGETHER_ORDER_PRODUCT` FOREIGN KEY (`product_id`)
    REFERENCES {$this->getTable('catalog/product')} (`entity_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_TM_SOLDTOGETHER_ORDER_RELATED` FOREIGN KEY (`related_product_id`)
    REFERENCES {$this->getTable('catalog/product')} (`entity_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

");

$installer->endSetup();
