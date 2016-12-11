<?php
/**
 * This is the part of 'Highlight' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 *
 * @author Templates-Master
 * @copyright Templates Master www.templates-master.com
 */

class TM_Highlight_Block_Product_Reports_Viewed extends TM_Highlight_Block_Product_List
{
    protected $_title       = 'Recently Viewed';
    protected $_priceSuffix = '-recently';
    protected $_className   = 'highlight-recently';

    protected $_indexName   = 'reports/product_index_viewed';

    /**
     * Initialize block's cache
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addData(array(
            'cache_lifetime'    => null,
            'cache_tags'        => array(Mage_Catalog_Model_Product::CACHE_TAG),
        ));
    }

    public function _beforeToHtml()
    {
        $collection = $this->getCollection($this->_getModel()->getCollection());

//        if ($this->getCustomerId()) {
//            $collection->setCustomerId($this->getCustomerId());
//        }

        $collection->excludeProductIds($this->_getModel()->getExcludeProductIds());
//        $ids = $this->getProductIds();
//        if (empty($ids)) {
            $collection->addIndexFilter();
//        } else {
//            $collection->addFilterByIds($ids);
//        }

        $collection->setAddedAtOrder();

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve Product Index model instance
     *
     * @return Mage_Reports_Model_Product_Index_Abstract
     */
    protected function _getModel()
    {
        if (is_null($this->_indexModel)) {
            if (is_null($this->_indexName)) {
                Mage::throwException(Mage::helper('reports')->__('Index model name must be defined'));
            }

            $this->_indexModel = Mage::getModel($this->_indexName);
        }

        return $this->_indexModel;
    }

    /**
     * Public method for retrieve Product Index model
     *
     * @return Mage_Reports_Model_Product_Index_Abstract
     */
    public function getModel()
    {
        return $this->_getModel();
    }
}
