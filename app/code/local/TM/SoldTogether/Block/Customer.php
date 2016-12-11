<?php

class TM_SoldTogether_Block_Customer extends TM_SoldTogether_Block_Abstract
{
    protected $_cachePrefix = 'TM_SOLD_TOGETHER_CUSTOMER';
    protected $_configGroup = 'customer';

    protected function _beforeToHtml()
    {
        if (!Mage::getStoreConfigFlag('soldtogether/general/enabled')
            || !Mage::getStoreConfigFlag('soldtogether/customer/enabled')) {

            return parent::_beforeToHtml();
        }

        $products = $this->getProducts();
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
                array('sc' => Mage::getResourceModel('soldtogether/customer')->getMainTable()),
                'e.entity_id = sc.related_product_id',
                array()
            )
            ->where('sc.product_id in (?)', $productIds)
            ->where('sc.related_product_id not in (?)', $productIds)
            ->order('sc.weight DESC');
        if (!Mage::getStoreConfig('soldtogether/general/out_of_stock')) {
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
        }
        if (Mage::getStoreConfig('soldtogether/general/random') && !$collection->count()) {
            reset($products);
            $collection = $this->_getRandomProductCollection(current($products));
            $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
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
        if (Mage::getStoreConfig('soldtogether/customer/addtocartcheckbox')) {
            $collection->getSelect()
                ->where('e.type_id IN (?)', array('simple', 'virtual'));
        }
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

            if (Mage::getStoreConfig('soldtogether/customer/addtocartcheckbox')) {
                $collection->getSelect()
                    ->where('e.type_id IN (?)', array('simple', 'virtual'));
            }
            if (!Mage::getStoreConfig('soldtogether/general/out_of_stock')) {
                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
            }
        }

        return $collection;
    }

   /**
    * Retrieve url for add product to cart
    * Will return product view page URL if product has required options
    *
    * @param Mage_Catalog_Model_Product $product
    * @param array $additional
    * @return string
    */
    public function getAddToCartUrl($product, $additional = array())
    {
        if ($product->getTypeInstance(true)->hasOptions($product)
            || 'grouped' === $product->getTypeId()) {

            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = array();
            }
            $additional['_query']['options'] = 'cart';

            $_url = $product->getUrl();
            $product->setUrl(null);
            $url = $this->getProductUrl($product, $additional);
            $product->setUrl($_url);
            return $url;
        }

        return parent::getAddToCartUrl($product, $additional);
    }
}
