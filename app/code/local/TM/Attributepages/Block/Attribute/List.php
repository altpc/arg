<?php

class TM_Attributepages_Block_Attribute_List extends Mage_Core_Block_Template
{
    /**
     * @var TM_Attributepages_Model_Resource_Entity_Collection
     */
    protected $_attributeCollection;

    /**
     * Initialize block's cache
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addData(array(
            'cache_lifetime' => false
        ));
    }

    /**
     * Retrieve page url
     *
     * @param  TM_Attributepages_Model_Entity $page
     * @return string
     * @deprecated Use $page->getUrl() instead
     */
    public function getPageUrl($page)
    {
        return $page->getUrl();
    }

    /**
     * Retrieve loaded category collection
     *
     * @return TM_Attributepages_Model_Resource_Entity_Collection
     */
    protected function _getAttributeCollection()
    {
        if (null === $this->_attributeCollection) {
            $storeId = Mage::app()->getStore()->getId();
            $this->_attributeCollection = Mage::getResourceModel('attributepages/entity_collection')
                ->addAttributeOnlyFilter()
                ->addUseForAttributePageFilter()
                ->addStoreFilter($storeId)
                ->setOrder('main_table.title', 'asc');

            if ($excludedPages = $this->getExcludedPages()) {
                $excludedPages = explode(',', $excludedPages);
                $this->_attributeCollection
                    ->addFieldToFilter('identifier', array('nin' => $excludedPages));
            }

            if ($includedPages = $this->getIncludedPages()) {
                $includedPages = explode(',', $includedPages);
                $this->_attributeCollection
                    ->addFieldToFilter('identifier', array('in' => $includedPages));
            }

            // filter pages with the same urls: linked to All Store Views and current store
            $urls = $this->_attributeCollection->getColumnValues('identifier');
            $duplicateUrls = array();
            foreach (array_count_values($urls) as $url => $count) {
                if ($count > 1) {
                    $duplicateUrls[] = $url;
                }
            }
            foreach ($duplicateUrls as $url) {
                $idsToRemove = array();
                $removeFlag = false;
                $attributes = $this->_attributeCollection->getItemsByColumnValue('identifier', $url);
                foreach ($attributes as $attribute) {
                    if ($attribute->getStoreId() !== $storeId) {
                        $idsToRemove[] = $attribute->getId();
                    } else {
                        $removeFlag = true;
                    }
                }
                if ($removeFlag) {
                    foreach ($idsToRemove as $id) {
                        $this->_attributeCollection->removeItemByKey($id);
                    }
                }
            }
        }
        return $this->_attributeCollection;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return TM_Attributepages_Model_Resource_Entity_Collection
     */
    public function getLoadedAttributeCollection()
    {
        return $this->_getAttributeCollection();
    }

    public function setCollection($collection)
    {
        $this->_attributeCollection = $collection;
        return $this;
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $categoryId = false;
        if ($category = $this->getCurrentCategory()) {
            $categoryId = $category->getId();
        }

        return array(
            'TM_ATTRIBUTEPAGES_ATTRIBUTE_LIST',
            Mage::app()->getStore()->getId(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            $this->getTemplate(),
            $this->getNameInLayout(),
            $this->getExcludedPages(),
            $this->getIncludedPages(),
            $this->getColumnCount()
        );
    }

    /**
     * Retrieve block cache tags based on options collection
     *
     * @return array
     */
    public function getCacheTags()
    {
        return array_merge(
            parent::getCacheTags(),
            $this->getItemsTags($this->_getAttributeCollection())
        );
    }

    /**
     * Copied for 1.7 compatibility
     *
     * Collect and retrieve items tags.
     * Item should implements Mage_Core_Model_Abstract::getCacheIdTags method
     *
     * @param array|Varien_Data_Collection $items
     * @return array
     */
    public function getItemsTags($items)
    {
        $tags = array();
        /** @var $item Mage_Core_Model_Abstract */
        foreach($items as $item) {
            $itemTags = $item->getCacheIdTags();
            if (false === $itemTags) {
                continue;
            }
            $tags = array_merge($tags, $itemTags);
        }
        return $tags;
    }
}
