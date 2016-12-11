<?php
/**
 * This is the part of 'Highlight' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 *
 * @author Templates-Master
 * @copyright Templates Master www.templates-master.com
 */

class TM_Highlight_Model_Resource_Eav_Mysql4_Catalog_Product_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    protected $_customJoinTables = array();

    public function isEnabledFlat()
    {
        return false;
    }

    /**
     * @param array $range Min and Max values
     */
    public function addQtyRangeFilter($range)
    {
        $this->_joinStockTable();

        if (is_numeric($range[0])) {
            $this->getSelect()->where('stock_table.qty >= ?', $range[0]);
        }
        if (isset($range[1]) && is_numeric($range[1])) {
            $this->getSelect()->where('stock_table.qty <= ?', $range[1]);
        }

        return $this;
    }

    /**
     * To show Out of stock items you should enable standard config first:
     *  - Display Out of Stock Products
     *
     * If this config is turned off - out of stock items are removed from
     * price_table and inner join filters them out from a colection.
     *
     * @param boolean $status Stock status flag
     */
    public function addStockFilter($status)
    {
        $this->_joinStockTable();

        $this->getSelect()->where('stock_table.stock_status = ?', (int)$status);

        return $this;
    }

    protected function _joinStockTable()
    {
        if (in_array('stock_table', $this->_customJoinTables)) {
            return $this;
        }

        $this->_customJoinTables['stock_table'] = 'stock_table';

        $conditions = array(
            'stock_table.product_id = e.entity_id',
            $this->getConnection()->quoteInto(
                'stock_table.website_id = ?',
                Mage::app()->getWebsite()->getWebsiteId()
            )
        );
        $this->getSelect()->join(
            array('stock_table' => $this->getTable('cataloginventory/stock_status')),
            join(' AND ', $conditions),
            array()
        );
        return $this;
    }

    /**
     * Specify category filter for product collection
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function addCategoryFilter(Mage_Catalog_Model_Category $category)
    {
        if (!is_array($this->_productLimitationFilters['category_id'])) {
            $this->_productLimitationFilters['category_id'] = array();
        }
        $this->_productLimitationFilters['category_id'][] = $category->getId();
        if ($category->getIsAnchor()) {
            unset($this->_productLimitationFilters['category_is_anchor']);
        } else {
            $this->_productLimitationFilters['category_is_anchor'] = 1;
        }

        $this->_applyProductLimitations();

        return $this;
    }

    /**
     * Apply limitation filters to collection
     *
     * Method allow use one time category product index table (or product website table)
     * for different combinations of store_id/category_id/visibility filter states
     *
     * Mehod support multiple changes in one collection object for this parameters
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected function _applyProductLimitations()
    {
        $this->_prepareProductLimitationFilters();
        $this->_productLimitationJoinWebsite();
        $this->_productLimitationJoinPrice();
        $filters = $this->_productLimitationFilters;

        if (!isset($filters['category_id']) && !isset($filters['visibility'])) {
            return $this;
        }

        $conditions = array(
            'cat_index.product_id=e.entity_id',
            $this->getConnection()->quoteInto('cat_index.store_id=?', $filters['store_id'])
        );
        if (isset($filters['visibility'])) {
            $conditions[] = $this->getConnection()
                ->quoteInto('cat_index.visibility IN(?)', $filters['visibility']);
        }

        $conditions[] = $this->getConnection()
            ->quoteInto('cat_index.category_id IN (?)', $filters['category_id']);
        if (count($filters['category_id']) > 1) {
            $this->getSelect()->group('e.entity_id'); // duplicates removed
        }

        /*if (isset($filters['category_is_anchor'])) {
            $conditions[] = $this->getConnection()
                ->quoteInto('cat_index.is_parent=?', $filters['category_is_anchor']);
        }*/

        $joinCond = join(' AND ', $conditions);
        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (isset($fromPart['cat_index'])) {
            $fromPart['cat_index']['joinCondition'] = $joinCond;
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        }
        else {
            $this->getSelect()->join(
                array('cat_index' => $this->getTable('catalog/category_product_index')),
                $joinCond,
                array('cat_index_position' => 'position')
            );
        }

        Mage::dispatchEvent('catalog_product_collection_apply_limitations_after', array(
            'collection'    => $this
        ));

        return $this;
    }

}
