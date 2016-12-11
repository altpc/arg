<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
    ALTER TABLE `{$installer->getTable('askit_item')}` RENAME TO `{$installer->getTable('tm_askit_item')}`;
    ALTER TABLE `{$installer->getTable('askit_vote')}` RENAME TO `{$installer->getTable('tm_askit_vote')}`;
");

$installer->endSetup();