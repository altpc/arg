<?php

$installer = $this;
$connection = $installer->getConnection();

// old magento versions check
$resourceModel = (string)Mage::getConfig()->getNode()->global->models->admin->resourceModel;
$entityConfig  = Mage::getSingleton('core/resource')->getEntity($resourceModel, 'permission_block');

if (!$entityConfig ||
    !$connection->isTableExists($installer->getTable('admin/permission_block'))) {

    return;
}

// magento 1.9.2.2 compatibility: whitelist blocks in table 'permission_block'
$blocks = array(
    'highlight/product_attribute_date',
    'highlight/product_attribute_yesno',
    'highlight/product_reports_viewed',
    'highlight/product_bestseller',
    'highlight/product_featured',
    'highlight/product_new',
    'highlight/product_popular',
    'highlight/product_random',
    'highlight/product_special',
    'highlight/review_new'
);

foreach ($blocks as $block) {
    $model = Mage::getResourceModel('admin/block_collection')
        ->addFieldToFilter('block_name', $block)
        ->getFirstItem();

    if ($model->getId()) {
        continue;
    }

    $model->setBlockName($block)
        ->setIsAllowed(1)
        ->save();
}
