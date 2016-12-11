<?php

class TM_SoldTogether_Model_Mysql4_Customer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_map = array('fields' => array(
        'product_name'          => 'cpev1.value',
        'related_product_name'  => 'cpev2.value'
    ));

    protected function _construct()
    {
        $this->_init('soldtogether/customer');
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

        $countSelect->columns('main_table.relation_id');

        return $countSelect;
    }
}
