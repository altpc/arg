<?php

class TM_Highlight_Model_Resource_Eav_Mysql4_Review_Product_Collection
    extends Mage_Review_Model_Mysql4_Review_Product_Collection
{
    protected $_customJoinTables = array();

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
}
