<?php
class TM_AjaxSearch_Model_Mysql4_Category_Collection extends Mage_Catalog_Model_Resource_Category_Collection
{

    public function setStoreIdFilter($storeId)
    {
        $this->setStoreId($storeId);
        if (method_exists($this, 'addStoreFilter')) {
            $this->addStoreFilter($storeId);
        } else {
            $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
            $root   = Mage::getModel('catalog/category')->load($rootId);
            $this->addFieldToFilter(
                'path', array('like' => "{$root->getPath()}/%")
            );
        }
        return $this;
    }

    public function setQueryFilter($query)
    {
        /** @var $collection Mage_Catalog_Model_Resource_Category_Collection */
//        $collection = Mage::getModel('catalog/category')->getCollection()
        $this
            ->addAttributeToSelect('*')
            ->addAttributeToSelect('is_active')
        ;

        $words = array_filter(explode(' ', trim($query)));
        foreach ($words as $word) {
            $this->addAttributeToFilter(
                'name', array('like' => "%{$word}%")
            );
        }

        $this->addIsActiveFilter();
        
        foreach ($this as $key => $item) {
            if (!$item->getIsActive()) {
                $this->removeItemByKey($key);
            }
        }

        return $this;
    }
}