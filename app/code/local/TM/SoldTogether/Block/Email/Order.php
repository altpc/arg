<?php

class TM_SoldTogether_Block_Email_Order extends Mage_Catalog_Block_Product_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('tm/soldtogether/email/order.phtml');
    }

    public function getProductCollection()
    {
        if (!$order = $this->getOrder()) {
            return false;
        }
        $items = $order->getAllVisibleItems();
        $ids = array();
        foreach ($items as $item) {
            $ids[] = $item->getProductId();
        }

        $collection = Mage::getResourceModel('catalog/product_collection');

        $collection
            ->addAttributeToSelect('*')
            ->setStoreId($order->getStoreId())
            ->addAttributeToFilter('visibility', array(
                'neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
            ))
            ->setPageSize((int)Mage::getStoreConfig('soldtogether/email/order_count'))
            ->setCurPage(1)
            ->addUrlRewrite()
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents();

        $collection->getSelect()
            ->join(
                array('sc' => Mage::getResourceModel('soldtogether/order')->getMainTable()),
                'e.entity_id = sc.related_product_id',
                array()
            )
            ->where('sc.product_id in (?)', $ids)
            ->where('sc.related_product_id not in (?)', $ids)
            ->order('sc.weight DESC');

        return $collection;
    }
}
