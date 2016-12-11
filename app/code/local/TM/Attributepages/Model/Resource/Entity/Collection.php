<?php

class TM_Attributepages_Model_Resource_Entity_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('attributepages/entity');
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
        $this->_map['fields']['store']     = 'store_table.store_id';
    }

    /**
     * Returns pairs entity_id - title
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('entity_id', 'title');
    }

    public function addUseForAttributePageFilter()
    {
        $this->addFilter('use_for_attribute_page', 1, 'public');
        return $this;
    }

    /**
     * Add filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     * @param bool $withAdmin
     * @return TM_Attributepages_Model_Resource_Entity_Collection
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

    /**
     * Add filter to receive attribute pages only
     *
     * @return TM_Attributepages_Model_Resource_Entity_Collection
     */
    public function addAttributeOnlyFilter()
    {
        $this->addFilter('option_id', array('null' => true), 'public');
        return $this;
    }

    /**
     * @return TM_Attributepages_Model_Resource_Entity_Collection
     */
    public function addOptionOnlyFilter()
    {
        $this->addFilter('option_id', array('notnull' => true), 'public');
        return $this;
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Zend_Db_Select::GROUP);
        return $countSelect;
    }

    /**
     * Join store relation table if there is store filter
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                array('store_table' => $this->getTable('attributepages/entity_store')),
                'main_table.entity_id = store_table.entity_id',
                array('store_id')
            )->group('main_table.entity_id'); // in case of filter by multipe stores

            /*
             * Allow analytic functions usage because of one field grouping
             */
            $this->_useAnalyticFunction = true;
        }
        return parent::_renderFiltersBefore();
    }
}
