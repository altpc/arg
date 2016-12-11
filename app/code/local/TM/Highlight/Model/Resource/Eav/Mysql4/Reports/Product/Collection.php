<?php
/**
 * This is the part of 'Highlight' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 *
 * @author Templates-Master
 * @copyright Templates Master www.templates-master.com
 */

class TM_Highlight_Model_Resource_Eav_Mysql4_Reports_Product_Collection
    extends Mage_Reports_Model_Mysql4_Product_Collection
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

    public function addOrderedQty($from = '', $to = '')
    {
        $qtyOrderedTableName = $this->getTable('sales/order_item');
        $qtyOrderedFieldName = 'qty_ordered';

        $productIdFieldName = 'product_id';

        $compositeTypeIds = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
        //$productTypes = $this->getConnection()->quoteInto(' AND (e.type_id NOT IN (?))', $compositeTypeIds);

        if ($from != '' && $to != '') {
            $dateFilter = " AND `order`.created_at BETWEEN '{$from}' AND '{$to}'";
        } else {
            $dateFilter = "";
        }

        $this->getSelect()/*->reset()->from(*/->joinInner(
            array('order_items' => $qtyOrderedTableName),
            'cat_index.product_id=order_items.product_id',
            array('ordered_qty' => "SUM(order_items.{$qtyOrderedFieldName})")
        );

        $_joinCondition = $this->getConnection()->quoteInto(
            'order.entity_id = order_items.order_id AND order.state<>?', Mage_Sales_Model_Order::STATE_CANCELED
        );
        $_joinCondition .= $dateFilter;
        $this->getSelect()->joinInner(
            array('order' => $this->getTable('sales/order')),
            $_joinCondition,
            array()
        );


        $this->getSelect()
            ->joinInner(array('pet' => $this->getProductEntityTableName()),
                "pet.entity_id = order_items.{$productIdFieldName} AND e.entity_type_id = {$this->getProductEntityTypeId()}")//{$productTypes}
            ->group('pet.entity_id')
            ->having("SUM(order_items.{$qtyOrderedFieldName}) > 0");

        return $this;
    }

    public function addViewsCount($from = '', $to = '')
    {
        /**
         * Getting event type id for catalog_product_view event
         */
        foreach (Mage::getModel('reports/event_type')->getCollection() as $eventType) {
            if ($eventType->getEventName() == 'catalog_product_view') {
                $productViewEvent = $eventType->getId();
                break;
            }
        }

        $this->getSelect()/*->reset()*/
            /*->from(
                array('_table_views' => $this->getTable('reports/event')),
                array('views' => 'COUNT(_table_views.event_id)'))*/
            ->joinInner(
                array('_table_views' => $this->getTable('reports/event')),
                'cat_index.product_id=_table_views.object_id',
                array('views' => 'COUNT(_table_views.event_id)'))
            ->join(array('pet' => $this->getProductEntityTableName()),
                "pet.entity_id = _table_views.object_id AND pet.entity_type_id = {$this->getProductEntityTypeId()}")
            ->where('_table_views.event_type_id = ?', $productViewEvent)
            ->group('pet.entity_id')
            ->order('views desc')
            ->having('COUNT(_table_views.event_id) > 0');

        if ($from != '' && $to != '') {
            $this->getSelect()
                ->where('logged_at >= ?', $from)
                ->where('logged_at <= ?', $to);
        }

        return $this;
    }
}