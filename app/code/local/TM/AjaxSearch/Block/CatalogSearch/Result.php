<?php

class TM_AjaxSearch_Block_CatalogSearch_Result extends Mage_CatalogSearch_Block_Result
{
    const TRIM_CHARLIST_CONFIG                = 'tm_ajaxsearch/general/trimmingchars';
    const USE_CATALOGSEARCH_COLLECTION_CONFIG = 'tm_ajaxsearch/general/use_catalogsearch_collection';
    const SORT_BY_CONFIG                      = 'tm_ajaxsearch/general/sortby';
    const SORT_ORDER_CONFIG                   = 'tm_ajaxsearch/general/sortorder';
    const ENABLE_CONFIG                       = 'tm_ajaxsearch/general/enabled';
    const UCCOSP_CONFIG                       = 'tm_ajaxsearch/general/use_custom_collection_on_search_page';

    /**
     *
     * @var string
     */
    protected $_queryText = null;

    protected $_category = null;

    /**
     *
     * @var int
     */
    protected $_storeId = null;

    /**
     *
     * @return int
     */
    public function getStoreId()
    {
        if (null == $this->_storeId) {
            $this->_storeId = (int) Mage::app()->getStore()->getStoreId();
        }
        return $this->_storeId;
    }

    public function getCategory()
    {
        if (null == $this->_category) {
            $category = null;
            $categoryId = $this->getRequest()->getParam('category', '');
            if ($categoryId) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                if (!$category->getId()) {
                    $category = null;
                }
            }
            $this->_category = $category;
        }

        return $this->_category;
    }

    /**
     *
     * @return string
     */
    public function getQueryText()
    {
        if (null == $this->_queryText) {
            $charlist = Mage::getStoreConfig(self::TRIM_CHARLIST_CONFIG);
            $query = Mage::helper('catalogsearch')->getQueryText();
            $this->_queryText = str_replace(str_split($charlist), '', $query);
        }

        return $this->_queryText;
    }

    protected function _getAlternativeProductCollection()
    {
        $query = $this->getQueryText();
        $store = $this->getStoreId();
        $category = $this->getCategory();

        $isStandartCollection = (bool) Mage::getStoreConfig(self::USE_CATALOGSEARCH_COLLECTION_CONFIG);
//        $isStandartCollection = true;
        if ($isStandartCollection) {

            $_query = Mage::helper('catalogsearch')->getQuery();
            $_query->prepare();

            $layer = Mage::getSingleton('catalogsearch/layer');
            if (null !== $category) {
                $layer->setCurrentCategory($category);
            }
            $collection = $layer->getProductCollection();
            /* @var $collection Mage_Catalog_Model_Resource_Eav_Resource_Product_Collection */

        } else {
            $collection = Mage::getResourceModel('ajaxsearch/product_collection')
                ->setQueryFilter($query);
            /* @var $collection TM_AjaxSearch_Model_Mysql4_Collection */
            $collection->addAttributeToSelect(
                Mage::getSingleton('catalog/config')->getProductAttributes()
            );
            $collection->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents();
            Mage::getSingleton('catalog/product_status')
                ->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')
                ->addVisibleInSearchFilterToCollection($collection);
        }

        $collection->addStoreFilter($store)
            ->addUrlRewrite()
//            ->addAttributeToSort($attributeToSort, $attributeSortOrder)
        ;

        if (null !== $category) {
            $collection->addCategoryFilter($category);
        }

        if (!Mage::helper('cataloginventory')->isShowOutOfStock()) {
            Mage::getSingleton('cataloginventory/stock')
                ->addInStockFilterToCollection($collection);
        }

//        $collection->load();
        return $collection;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Collection
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $enabled = (bool) Mage::getStoreConfig(self::ENABLE_CONFIG);
            $dontUseOnController = !Mage::getStoreConfig(self::UCCOSP_CONFIG);
            if (!$enabled || $dontUseOnController) {
                $this->_productCollection = parent::_getProductCollection();

                if (!$this->_productCollection instanceof Mage_CatalogSearch_Model_Resource_Fulltext_Collection) {
                    $collection = Mage::getResourceModel('catalogsearch/fulltext_collection');
                    $collection
                        ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                        ->addSearchFilter(Mage::helper('catalogsearch')->getQuery()->getQueryText())
                        ->setStore(Mage::app()->getStore())
                        ->addMinimalPrice()
                        ->addFinalPrice()
                        ->addTaxPercents()
                        ->addStoreFilter()
                        ->addUrlRewrite();

                    Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
                    Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);

                    $this->_productCollection =  $collection;
                }
            } else {
                $this->_productCollection = $this->_getAlternativeProductCollection();
            }
        }
        return $this->_productCollection;
    }

    /**
     * Prepare layout
     *
     * @return Mage_CatalogSearch_Block_Result
     */
    protected function _prepareLayout()
    {
        $helper = $this->helper('catalogsearch');
        // add Home breadcrumb
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {

            $queryText = $helper->getQueryText();
//            $queryText = $this->getQuery();
            $title = $this->__("Search results for: '%s'", $queryText);

            $breadcrumbs->addCrumb('home', array(
                'label' => $this->__('Home'),
                'title' => $this->__('Go to Home Page'),
                'link'  => Mage::getBaseUrl()
            ))->addCrumb('search', array(
                'label' => $title,
                'title' => $title
            ));
        }

        // modify page title
        $title = $this->__("Search results for: '%s'", $helper->getEscapedQueryText());
        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->setTitle($title);
        }

        return $this;
    }

    /**
     * Set Search Result collection
     *
     * @return Mage_CatalogSearch_Block_Result
     */
    public function setListCollection()
    {
        $this->getListBlock()
           ->setCollection($this->_getProductCollection());
       return $this;
    }
}