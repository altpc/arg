<?php

class TM_Attributepages_Block_Option_List extends TM_Attributepages_Block_Abstract
{
    /**
     * @var TM_Attributepages_Model_Resource_Entity_Collection
     */
    protected $_optionCollection;

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
     * Retrieve option url
     *
     * @param  TM_Attributepages_Model_Entity $option
     * @return string
     * @deprecated Use $option->getUrl() instead
     */
    public function getOptionUrl($option)
    {
        return $option->getUrl();
    }

    /**
     * Retrieve loaded category collection
     *
     * @return TM_Attributepages_Model_Resource_Entity_Collection
     */
    protected function _getOptionCollection()
    {
        if (null === $this->_optionCollection && $this->getCurrentPage()) {
            $storeId = Mage::app()->getStore()->getId();
            $parentPage = $this->getCurrentPage();
            $this->_optionCollection = Mage::getResourceModel('attributepages/entity_collection')
                ->addOptionOnlyFilter()
                ->addFieldToFilter('attribute_id', $parentPage->getAttributeId())
                ->addUseForAttributePageFilter()
                ->addStoreFilter($storeId)
                ->setOrder('main_table.title', 'asc');

            if ($excludedOptions = $parentPage->getExcludedOptionIdsArray()) {
                $this->_optionCollection
                    ->addFieldToFilter('option_id', array(
                        'nin' => $excludedOptions
                    ));
            }

            if ($limit = $this->getLimit()) {
                $this->_optionCollection->setPageSize($limit);
            }

            // filter options with the same urls: linked to All Store Views and current store
            $urls = $this->_optionCollection->getColumnValues('identifier');
            $duplicateUrls = array();
            foreach (array_count_values($urls) as $url => $count) {
                if ($count > 1) {
                    $duplicateUrls[] = $url;
                }
            }
            foreach ($duplicateUrls as $url) {
                $idsToRemove = array();
                $removeFlag = false;
                $options = $this->_optionCollection->getItemsByColumnValue('identifier', $url);
                foreach ($options as $option) {
                    if ($option->getStoreId() !== $storeId) {
                        $idsToRemove[] = $option->getId();
                    } else {
                        $removeFlag = true;
                    }
                }
                if ($removeFlag) {
                    foreach ($idsToRemove as $id) {
                        $this->_optionCollection->removeItemByKey($id);
                    }
                }
            }

            foreach ($this->_optionCollection as $option) {
                $option->setParentPage($parentPage);
            }
        }
        return $this->_optionCollection;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return TM_Attributepages_Model_Resource_Entity_Collection
     */
    public function getLoadedOptionCollection()
    {
        return $this->_getOptionCollection();
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getListingMode()
    {
        return $this->_getConfigurableParam('listing_mode');
    }

    public function getColumnCount()
    {
        return $this->_getConfigurableParam('column_count');
    }

    public function getImageWidth()
    {
        return $this->_getConfigurableParam('image_width');
    }

    public function getImageHeight()
    {
        return $this->_getConfigurableParam('image_height');
    }

    public function getGroupByFirstLetter()
    {
        return $this->_getConfigurableParam('group_by_first_letter');
    }

    public function getSliderId()
    {
        $key  = 'slider_id';
        $data = $this->_getData($key);
        if (null === $data) {
            $this->setData($key, $this->getCurrentPage()->getIdentifier());
        }
        return $this->_getData($key);
    }

    public function setCollection($collection)
    {
        $this->_optionCollection = $collection;
        return $this;
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
            'TM_ATTRIBUTEPAGES_OPTION_LIST',
            Mage::app()->getStore()->getId(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            $this->getTemplate(),
            $this->getNameInLayout(),
            $this->getCurrentPage()->getIdentifier(),
            $this->getListingMode(),
            $this->getColumnCount(),
            $this->getImageWidth(),
            $this->getImageHeight(),
            $this->getGroupByFirstLetter(),
            $this->getSliderId()
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
            $this->getItemsTags($this->_getOptionCollection())
        );
    }
}
