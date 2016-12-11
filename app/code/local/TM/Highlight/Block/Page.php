<?php
/**
 * This is the part of 'Highlight' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 *
 * @author Templates-Master
 * @copyright Templates Master www.templates-master.com
 */

class TM_Highlight_Block_Page extends TM_Highlight_Block_Product_List
{
    protected $_defaultToolbarBlock = 'catalog/product_list_toolbar';

    protected $_collectionBlock = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setCacheLifetime(null);
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $this->_productCollection = $this->getCollectionBlock()
                ->getLoadedProductCollection();
        }

        return $this->_productCollection;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     */
    protected function _beforeToHtml()
    {
        // higlight block applies collection filters in _beforeToHtml method
        $this->getCollectionBlock()->beforeToHtml();

        /**
         * Copy of parent method with commented event
         * to prevent double call for the same collection
         */
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getProductCollection();

        // use sortable parameters
        if ($orders = $this->getAvailableOrders()) {
            $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $this->getSortBy()) {
            $toolbar->setDefaultOrder($sort);
        }
        if ($dir = $this->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }
        if ($modes = $this->getModes()) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        // Mage::dispatchEvent('catalog_block_product_list_collection', array(
        //     'collection' => $this->_getProductCollection()
        // ));

        $this->_getProductCollection()->load();
    }

    public function setCollectionBlock(Mage_Catalog_Block_Product_Abstract $block)
    {
        $this->_collectionBlock = $block;
        if ($name = $this->getToolbarBlockName()) {
            $this->getCollectionBlock()->setToolbarBlockName($name);
        }
        return $this;
    }

    public function getCollectionBlock()
    {
        if (!$this->_collectionBlock) {
            $type = $this->_data['collection_block_type'];
            $this->_collectionBlock = $this->getLayout()->createBlock($type);
        }
        return $this->_collectionBlock;
    }

    public function getTitle()
    {
        return $this->getCollectionBlock()->getTitle();
    }
}
