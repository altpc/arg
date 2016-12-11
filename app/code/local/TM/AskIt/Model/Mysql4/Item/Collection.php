<?php

class TM_AskIt_Model_Mysql4_Item_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_addItemNameFilter = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('askit/item');
    }

    public function addItemTypeIdFilter($itemTypeId)
    {
        $this->getSelect()->where('main_table.item_type_id = ?', $itemTypeId);
        return $this;
    }

    public function addItemIdFilter($itemId)
    {
        $this->getSelect()->where('main_table.item_id = ?', $itemId);
        return $this;
    }

    public function addEmptyFilter()
    {
        $this->getSelect()->where('main_table.item_id IS NOT NULL')
            ->where('main_table.item_type_id IS NOT NULL');
        return $this;
    }

    // public function addProductIdFilter($productId)
    // {
    //     $this->getSelect()->where(
    //         'main_table.item_type_id = ' . TM_AskIt_Model_Item_Type::PRODUCT_ID .
    //         ' AND main_table.item_id = ?',
    //         $productId
    //     );
    //     return $this;
    // }

    public function addMessageIdFilter($messageId)
    {
        $messageId = array($messageId);
        $this->getSelect()->where('main_table.message_id IN (?)', $messageId);
        return $this;
    }

    public function addItemNameFilter($name)
    {
        $this->_addItemNameFilter = strtolower($name);
        return $this;
    }

    protected  function _runItemNameFilter()
    {
        foreach ($this as $row) {
            $_item = Mage::helper('askit')->getItem($row->getData());
            $name = strtolower($_item->getName());

            if (false === strpos($name, $this->_addItemNameFilter)) {
                $this->removeItemByKey($row->getId());
            }
        }
        return $this;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();

        if (!empty($this->_addItemNameFilter)) {
            $this->_runItemNameFilter();
        }
        return $this;
    }
}
