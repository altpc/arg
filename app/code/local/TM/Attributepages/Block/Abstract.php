<?php

class TM_Attributepages_Block_Abstract extends Mage_Core_Block_Template
{
    public function getTitle()
    {
        return $this->_getConfigurableParam('title');
    }

    public function getPageTitle()
    {
        $title = $this->_getData('title');
        if (null === $title) {
            $currentPage = $this->getCurrentPage();
            if ($currentPage) {
                $title = $currentPage->getPageTitle();
            }
        }
        return $title;
    }

    /**
     * Retrieve current category model object
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentPage()
    {
        if (!$this->hasData('current_page')) {
            if ($identifier = $this->getData('identifier')) { // parent page for option list
                $storeId = Mage::app()->getStore()->getId();
                $collection = Mage::getResourceModel('attributepages/entity_collection')
                    ->addFieldToFilter('identifier', $identifier)
                    ->addUseForAttributePageFilter() // enabled flag
                    ->addStoreFilter($storeId)
                    ->setOrder('store_id', 'DESC');

                $this->setData('current_page', $collection->getFirstItem());
            } else {
                $this->setData('current_page', Mage::registry('attributepages_current_page'));
            }
        }
        return $this->getData('current_page');
    }

    protected function _getConfigurableParam($key)
    {
        $data = $this->_getData($key);
        if (null === $data) {
            $currentPage = $this->getCurrentPage();
            if ($currentPage) {
                $this->setData($key, $currentPage->getData($key));
            }
        }
        return $this->_getData($key);
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
