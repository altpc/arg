<?php

class TM_EasyBanner_Model_Mysql4_Banner_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_map = array('fields' => array(
        'placeholder' => 'placeholder.name',
        'status'      => 'main_table.status',
        'store'       => 'store_table.store_id'
    ));

    protected function _construct()
    {
        $this->_init('easybanner/banner');
    }

    /**
     * Adding banner placeholder names to result collection
     * Add for each banner placeholder information
     *
     * @return TM_EasyBanner_Model_Mysql4_Banner_Collection
     */
    public function addPlaceholderNamesToResult()
    {
        $bannerIds = $this->getColumnValues('banner_id');
        $placeholdersToBanner = array();

        if (count($bannerIds) > 0) {
            $select = $this->getConnection()->select()
                ->from(array('placeholder' => $this->getTable('easybanner/placeholder')), 'name')
                ->join(array('banner_placeholder' => $this->getResource()->getTable('easybanner/banner_placeholder')),
                    'banner_placeholder.placeholder_id = placeholder.placeholder_id',
                    array('banner_placeholder.banner_id'))
                ->where('banner_placeholder.banner_id IN (?)', $bannerIds);
            $result = $this->getConnection()->fetchAll($select);

            foreach ($result as $row) {
                if (!isset($placeholdersToBanner[$row['banner_id']])) {
                    $placeholdersToBanner[$row['banner_id']] = array();
                }
                $placeholdersToBanner[$row['banner_id']][] = $row['name'];
            }
        }

        foreach ($this as $item) {
            if (isset($placeholdersToBanner[$item->getId()])) {
                $item->setPlaceholder(implode(", ", $placeholdersToBanner[$item->getId()]));
            } else {
                $item->setPlaceholder(null);
            }
        }

        return $this;
    }

    /**
     * @return TM_EasyBanner_Model_Mysql4_Banner_Collection
     */
    public function joinLeft($table, $cond, $cols='*')
    {
        if (!isset($this->_joinedTables[$table])) {
            $this->getSelect()->joinLeft(array($table => $this->getTable($table)), $cond, $cols);
            $this->_joinedTables[$table] = true;
        }
        return $this;
    }

    public function addStatistics()
    {
        $this->joinLeft(
            'banner_statistic',
            'main_table.banner_id = banner_statistic.banner_id',
            array(
                'display_count' => 'SUM(display_count)',
                'clicks_count'  => 'SUM(clicks_count)'
            )
        );
        $this->getSelect()->group('main_table.banner_id');

        return $this;
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

        $countSelect->columns('main_table.banner_id');

        return $countSelect;
    }

    /**
     * Add filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     * @param bool $withAdmin
     * @return TM_EasyBanner_Model_Mysql4_Banner_Collection
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
     * Join store relation table if there is store filter
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                array('store_table' => $this->getTable('easybanner/banner_store')),
                'main_table.banner_id = store_table.banner_id',
                array()
            )->group('main_table.banner_id');
            /*
             * Allow analytic functions usage because of one field grouping
             */
            $this->_useAnalyticFunction = true;
        }
        return parent::_renderFiltersBefore();
    }
}
