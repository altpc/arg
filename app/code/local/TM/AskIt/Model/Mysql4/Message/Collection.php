<?php

class TM_AskIt_Model_Mysql4_Message_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_addQuestionAnswersDataFlag      = false;
    protected $_addQuestionCountAnswersDataFlag = false;
    protected $_addItemNameFilter               = null;

    protected $_questionCountAnswersFrom = false;
    protected $_questionCountAnswersTo  = false;
    protected $_itemInfo                 = false;

    protected $_map = array('fields' => array(
        'product_name'   => 'cpev.value'
    ));

    public function _construct()
    {
        parent::_construct();
        $this->_init('askit/message');
    }

    public function addStatusFilter($statuses)
    {
        if (!is_array($statuses)) {
            $statuses = array($statuses);
        }
        $this->getSelect()->where('main_table.status IN (?)', $statuses);
        return $this;
    }

    public function addItemInfo()
    {
        if (false === $this->_itemInfo) {
            $keys = array('item_id', 'item_type_id');
            $table = $this->getTable('askit/item');
            $this->getSelect()->joinLeft(
                array('i' => $table),
                'i.message_id = main_table.id',
                $keys
            )
            // ->group('main_table.id')
            ;
            $this->_itemInfo = true;
        }
        return $this;
    }

    public function groupById()
    {
        $this->getSelect()->group('main_table.id');
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

    public function addItemTypeIdFilter($itemTypeId)
    {
        $this->addItemInfo();
        $this->getSelect()->where('i.item_type_id = ?', $itemTypeId);
        return $this;
    }

    public function addItemIdFilter($itemId)
    {
        $this->addItemInfo();
        $this->getSelect()->where('i.item_id = ?', $itemId);
        return $this;
    }

    public function addProductIdFilter($productId)
    {
        $this->addItemInfo();
        $this->getSelect()->where(
            'i.item_type_id = ' . TM_AskIt_Model_Item_Type::PRODUCT_ID .
            ' AND i.item_id = ?',
            $productId
        );
        return $this;
    }

    public function addParentIdFilter($parentId)
    {
        $this->getSelect()->where('main_table.parent_id = ?', $parentId);
        return $this;
    }

    public function addQuestionFilter()
    {
        $this->getSelect()->where('main_table.parent_id IS NULL');
        return $this;
    }

    public function addCustomerFilter()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            $this->getSelect()->where('main_table.customer_id = ?', $customerId);
        }

        return $this;
    }

    public function addPrivateFilter()
    {
//        $this->getSelect()->where('main_table.private = 0');
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            $this->getSelect()->where(
                '(main_table.private = 0) OR (main_table.private = 1 AND main_table.customer_id = ?)',
                $customerId
            );
        } else {
            $this->getSelect()->where('main_table.private = 0');
        }

        return $this;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();

        if ($this->_addQuestionAnswersDataFlag) {
            $this->_addQuestionAnswersData();
        }

        if ($this->_addQuestionCountAnswersDataFlag) {
            $this->_addQuestionCountAnswersData();
        }

        if ($this->_questionCountAnswersFrom
            || $this->_questionCountAnswersTo) {

            $this->_runCountAnswersFilter();
        }

        if (!empty($this->_addItemNameFilter)) {
            $this->_runItemNameFilter();
        }
        return $this;
    }

    public function addQuestionCountAnswersData($flag = true)
    {
        $this->_addQuestionCountAnswersDataFlag = $flag;
        return $this;
    }

    protected  function _addQuestionCountAnswersData()
    {
        $select = $this->getConnection()->select()
            ->from(
                $this->getResource()->getMainTable(),
                array('parent_id', 'count_answers' => 'COUNT(id)')
            )
            ->where('parent_id IS NOT NULL')
            ->group('parent_id')
            ;
        $data = array();
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $data[$row['parent_id']] = $row['count_answers'];
        }

        foreach ($this as $row) {
            $count = 0;
            if (isset($data[$row->getId()])) {
                $count = $data[$row->getId()];
            }
            $row->setData('count_answers', $count);
        }
        return $this;
    }

    public function addCountAnswerFilter($from = false, $to = false)
    {
        $this->_addQuestionCountAnswersDataFlag = true;
        $this->_questionCountAnswersFrom = (int) $from;
        $this->_questionCountAnswersTo = (int) $to;
        return $this;
    }

    /**
     *
     * @param bool $flag
     */
    public function addQuestionAnswersData($flag = true)
    {
        $this->_addQuestionAnswersDataFlag = $flag;
        return $this;
    }

    /**
     *
     * @return \TM_AskIt_Model_Mysql4_Item_Collection
     */
    protected  function _addQuestionAnswersData()
    {
        $ids = array();
        foreach ($this as $row) {
            if (null == $row->getParentId()) {
                $ids[] = $row->getId();
            }
        }

        $select = $this->getConnection()->select()
            ->from($this->getResource()->getMainTable())
            ->where('parent_id IN(?)', $ids)
            ;

        $_data = $data = array();
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $_data[$row['parent_id']][$row['id']] = $row;
        }
        foreach ($_data as $id => $row) {
            $_collection = new Varien_Data_Collection();
            foreach ($row as $_row) {

                $item = $this->getNewEmptyItem();
                if ($this->getIdFieldName()) {
                    $item->setIdFieldName($this->getIdFieldName());
                }
                $item->addData($_row);
                $_collection->addItem($item);
            }
            $data[$id] = $_collection;
//                $this->addItem($item);
        }
        foreach ($this as $row) {
            if (isset($data[$row->getId()])) {
                $row->setData('answers', $data[$row->getId()]);
            }
        }
        return $this;
    }

    public function addItemNameFilter($name)
    {
        $this->_addItemNameFilter = strtolower($name);
        return $this;
    }

    protected  function _runCountAnswersFilter()
    {
        $from = $this->_questionCountAnswersFrom;
        $to = $this->_questionCountAnswersTo;

        foreach($this as $row) {
            $_count = $row->getData('count_answers');
            if ($_count < $from || $_count > $to) {
                $this->removeItemByKey($row->getId());
            }
        }
        return $this;
    }

    protected  function _runItemNameFilter()
    {
        foreach ($this as $row) {
            $_item = Mage::helper('askit')->getItem($row->getData());
            $name = strtolower($_item->getName());

            if (false === strpos($name, strtolower($this->_addItemNameFilter))) {
                $this->removeItemByKey($row->getId());
            }
        }
        return $this;
    }

    public function addStoreFilter($storeId, $all = true)
    {
        $stores = array($storeId);
        if ($all) {
            $stores[] = 0;
        }
        $this->getSelect()->where('main_table.store_id IN (?)', $stores);
        return $this;
    }
}
