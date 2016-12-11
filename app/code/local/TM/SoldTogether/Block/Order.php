<?php

class TM_SoldTogether_Block_Order extends TM_SoldTogether_Block_Abstract
{
    protected $_cachePrefix = 'TM_SOLD_TOGETHER_ORDER';
    protected $_configGroup = 'order';

    protected function _beforeToHtml()
    {
        if (!Mage::getStoreConfigFlag('soldtogether/general/enabled')
            || !Mage::getStoreConfigFlag('soldtogether/order/enabled')) {

            return parent::_beforeToHtml();
        }

        $products = $products = $this->getProducts();
        if (!count($products)) {
            return parent::_beforeToHtml();
        }

        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        /**
         * @var Mage_Catalog_Model_Resource_Product_Collection
         */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(
            Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds()
        );
        $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->setPageSize($this->getProductsCount())
            ->setCurPage(1);

        $collection->getSelect()
            ->join(
                array('so' => Mage::getResourceModel('soldtogether/order')->getMainTable()),
                'e.entity_id = so.related_product_id',
                array()
            )
            ->where('so.product_id in (?)', $productIds)
            ->where('so.related_product_id not in (?)', $productIds)
            ->order('so.weight DESC');

        if ($this->getAmazonStyle()
            || Mage::getStoreConfig('soldtogether/order/addtocartcheckbox')) {

            $collection->getSelect()
                ->where('e.type_id IN (?)', array('simple', 'virtual'));
        }
        if (!Mage::getStoreConfig('soldtogether/general/out_of_stock')) {
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
        }
        if (Mage::getStoreConfig('soldtogether/general/random') && !$collection->count()) {
            reset($products);
            $collection = $this->_getRandomProductCollection(current($products));
        }

        $this->setProductCollection($collection);

        return parent::_beforeToHtml();
    }

    protected function _getRandomProductCollection($product)
    {
        if (!$category = Mage::registry('current_category')) {
            $categoryId = $product->getCategoryId() ? $product->getCategoryId()
                : current($product->getCategoryIds());
            $category = Mage::getModel('catalog/category');
            $category->load($categoryId);
        }

        $category->setIsAnchor(1); // @see Mage_Catalog_Model_Resource_Product_Collection::addCategoryFilter
        $collection = $category->getProductCollection();
        $collection->setVisibility(
            Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds()
        );
        $this->_addProductAttributesAndPrices($collection)
            ->setPageSize($this->getProductsCount())
            ->setCurPage(1)
            ->addAttributeToFilter('entity_id', array('nin' => array($product->getId())));
        if ($this->getAmazonStyle()
            || Mage::getStoreConfig('soldtogether/order/addtocartcheckbox')) {

            $collection->getSelect()
                ->where('e.type_id IN (?)', array('simple', 'virtual'));
        }
        $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
        if (!Mage::getStoreConfig('soldtogether/general/out_of_stock')) {
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
        }
        if (!$collection->count()) {
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->setVisibility(
                Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds()
            );
            $this->_addProductAttributesAndPrices($collection)
                ->addStoreFilter()
                ->setPageSize($this->getProductsCount())
                ->setCurPage(1)
                ->addAttributeToFilter('entity_id', array('nin' => array($product->getId())));

            if ($this->getAmazonStyle()
                || Mage::getStoreConfig('soldtogether/order/addtocartcheckbox')) {

                $collection->getSelect()
                    ->where('e.type_id IN (?)', array('simple', 'virtual'));
            }
            $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
            if (!Mage::getStoreConfig('soldtogether/general/out_of_stock')) {
                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
            }
        }

        return $collection;
    }

    public function getAmazonStyle()
    {
        if (!isset($this->_data['amazon_style'])) {
            $this->_data['amazon_style'] =
                Mage::getStoreConfig('soldtogether/order/amazonestyle');
        }
        // Zend_Debug::dump($this->_data);die;
        return (int)$this->_data['amazon_style'];
    }

}
