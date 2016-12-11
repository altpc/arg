<?php

$saleLabel = array(
    'rules_id'              => '1',
    'system_label_name'     => 'On Sale',
    'l_status'              => '1',
    'product_position'      => 'top-left',
    'product_image_text'    => 'Save #discount_percent#%',
    'product_font_style'    => 'color: #fff; text-shadow: 0 1px 0 rgba(0,0,0,0.3); width: 60px; height: 60px;background:#ff7800; border-radius:50%;',
    'product_round_method'  => 'round',
    'product_round'         => '1',
    'category_position'     => 'top-left',
    'category_image_text'   => 'Save #discount_percent#%',
    'category_font_style'   => 'color: #fff; text-shadow: 0 1px 0 rgba(0,0,0,0.3); width: 45px; height: 45px;background:#ff7800; border-radius:50%;',
    'category_round_method' => 'round',
    'category_round'        => '1'
);

$newLabel = array(
    'rules_id'              => '3',
    'system_label_name'     => 'Is New',
    'l_status'              => '1',
    'product_position'      => 'top-right',
    'product_image_text'    => 'New',
    'product_font_style'    => 'color: #fff; text-shadow: 0 1px 0 rgba(0,0,0,0.3); width: 60px; height: 60px;background:#00a7e5; border-radius:50%;',
    'product_round_method'  => 'round',
    'product_round'         => '1',
    'category_position'     => 'top-right',
    'category_image_text'   => 'New',
    'category_font_style'   => 'color: #fff; text-shadow: 0 1px 0 rgba(0,0,0,0.3); width: 45px; height: 45px;background:#00a7e5; border-radius:50%;',
    'category_round_method' => 'round',
    'category_round'        => '1'
);

/*
** Create On Sale Label
*/
$labelModel = Mage::getModel('prolabels/system');
$labelModel->setId(null)
    ->setData($saleLabel)
    ->save();

$storeModel = Mage::getModel('prolabels/sysstore');
$storeModel->setId(null);
$storeModel->addData(array('store_id' => '0'));
$storeModel->addData(array('system_id' => $labelModel->getSystemId()));
$storeModel->addData(array('rules_id' => $labelModel->getRulesId()));
$storeModel->save();

/*
** Create Is New Label
*/
$labelModel = Mage::getModel('prolabels/system');
$labelModel->setId(null)
    ->setData($newLabel)
    ->save();

$storeModel = Mage::getModel('prolabels/sysstore');
$storeModel->setId(null);
$storeModel->addData(array('store_id' => '0'));
$storeModel->addData(array('system_id' => $labelModel->getSystemId()));
$storeModel->addData(array('rules_id' => $labelModel->getRulesId()));
$storeModel->save();