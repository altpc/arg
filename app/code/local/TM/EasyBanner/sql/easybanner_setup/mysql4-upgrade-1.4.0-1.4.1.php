<?php

/**
 * @var Mage_Core_Model_Resource_Setup
 */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->changeColumn(
    $this->getTable('easybanner/placeholder'),
    'mode',
    'mode',
    "enum('rotator','slider','lightbox','awesomebar') DEFAULT 'rotator' NOT NULL"
);

$placeholder = Mage::getModel('easybanner/placeholder');
$placeholder->addData(array(
        'name'          => 'awesomebar',
        'parent_block'  => 'before_body_end',
        'position'      => 'after="-"',
        'status'        => 1,
        'limit'         => 100,
        'mode'          => TM_EasyBanner_Model_Placeholder::MODE_AWESOMEBAR,
        'banner_offset' => 1,
        'sort_mode'     => 'sort_order'
    ))
    ->save();

$installer->endSetup();
