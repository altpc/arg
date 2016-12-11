<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('tm_navigationpro_menu')}`
    ADD COLUMN `configuration` TEXT NOT NULL DEFAULT '' AFTER `is_active`;

");

$menus = Mage::getResourceModel('navigationpro/menu_collection');
foreach ($menus as $menu) {
    $menu->setLevelsPerDropdown(1)->save();
}

$installer->run("

ALTER TABLE `{$this->getTable('tm_navigationpro_menu')}`
    DROP COLUMN `columns_mode`,
    DROP COLUMN `display_in_navigation`;

");

$installer->endSetup();
