<?php

class TM_AjaxSearch_Block_CategoryList extends Mage_Core_Block_Template
{
    /**
     * Initialize block's cache
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addData(array(
            'cache_lifetime' => 86400,
            'cache_tags'     => array(Mage_Catalog_Model_Category::CACHE_TAG)
        ));
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
            'TM_AJAXSEARCH',
            Mage::app()->getStore()->getId(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            $this->getTemplate(),
            $this->getNameInLayout()
        );
    }

    protected function _beforeToHtml()
    {
        $collection = Mage::getModel('catalog/category')
            ->getCollection()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->addNameToResult()
            ->addIsActiveFilter()
            ->addFieldToFilter('level', array('gt' => 1))
            ->addOrderField('name');

        if ($levelCount = $this->getLevelCount()) {
            $collection->addFieldToFilter('level', array('lte' => $levelCount + 1));
        }

        if ($ids = $this->getCategoryToExclude()) {
            $ids = explode(",", $ids);
            $collection->addFieldToFilter('entity_id', array('nin' => $ids));
        } elseif ($ids = $this->getCategoryToInclude()) {
            $ids = explode(",", $ids);
            $collection->addFieldToFilter('entity_id', array('in' => $ids));
        }

        if (method_exists($collection, 'addStoreFilter')) {
            $collection->addStoreFilter();
        } else {
            $rootId = Mage::app()->getStore()->getRootCategoryId();
            $root   = Mage::getModel('catalog/category')->load($rootId);
            $collection->addFieldToFilter('path', array('like' => "{$root->getPath()}/%"));
        }

        $this->setCategoryCollection($collection);

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve category levels count to show in combobox
     *
     * @return int
     */
    public function getLevelCount()
    {
        return (int)$this->_getConfigurableParam('category_level_count');
    }

    /**
     * Retrieve category levels count to show in combobox
     *
     * @return int
     */
    public function getCategoryToExclude()
    {
        return $this->_getConfigurableParam('category_to_exclude');
    }

    /**
     * Retrieve category levels count to show in combobox
     *
     * @return int
     */
    public function getCategoryToInclude()
    {
        return $this->_getConfigurableParam('category_to_include');
    }

    protected function _getConfigurableParam($key)
    {
        $data = $this->_getData($key);
        if (null === $data) {
            $data = Mage::getStoreConfig('tm_ajaxsearch/general/' . $key);
        }
        return $data;
    }
}