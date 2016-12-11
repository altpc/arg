<?php

class TM_ReviewReminder_Model_Resource_Entity_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('tm_reviewreminder/entity');
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
        $this->_map['fields']['store']  = 'a.store_id';
        $this->_map['fields']['increment_id']  = 'a.increment_id';
    }
    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelectCountSql();
            $this->_totalRecords = count($this->getConnection()->fetchCol($sql, $this->_bindParams));
        }
        return intval($this->_totalRecords);
    }
    /**
     * Overriden to get it work with left join and group stmt
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->columns('main_table.entity_id');
        return $countSelect;
    }
    /**
     * Join reminder products info
     */
    public function joinOrderInfo()
    {
        $this->join(array('a' => 'sales/order'), 'main_table.order_id = a.entity_id',
            array(
                'customer_firstname' => 'customer_firstname',
                'customer_lastname' => 'customer_lastname',
                'store' => 'store_id',
                'increment_id' => 'increment_id'
            )
        )
        ->addExpressionFieldToSelect(
            'fullname',
            'CONCAT({{customer_firstname}}, \' \', {{customer_lastname}})',
            array(
                'customer_firstname' => 'a.customer_firstname',
                'customer_lastname' => 'a.customer_lastname'
            )
        )
        ->addExpressionFieldToSelect(
            'products',
            '(SELECT GROUP_CONCAT(\' \', x.name)
                FROM ' . $this->getTable('sales/order_item') . ' x
                WHERE {{entity_id}} = x.order_id
                    AND x.product_type != \'configurable\')',
            array('entity_id' => 'a.entity_id')
        );
        return $this;
    }

    /**
     * Add filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     * @param bool $withAdmin
     * @return TM_ReviewReminder_Model_Resource_Entity_Collection
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            if ($store instanceof Mage_Core_Model_Store) {
                $store = array($store->getId());
            }

            if (!is_array($store)) {
                $store = array($store);
            }

            if ($withAdmin) {
                $store[] = Mage_Core_Model_App::ADMIN_STORE_ID;
            }

            $this->addFilter('store', array('in' => $store), 'public');
        }
        return $this;
    }
}
