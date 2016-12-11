<?php

class TM_ReviewReminder_Model_Resource_Entity extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('tm_reviewreminder/entity','entity_id');
    }

    public function getOrderInfo($id)
    {
        $adapter = $this->_getReadAdapter();

        $productSelect = $adapter->select()
            ->from($this->getTable('sales/order_item'), "GROUP_CONCAT(' ', name)")
            ->where('order_id = ?', $id)
            ->where('product_type != ?', 'configurable');

        $select = $adapter->select()
            ->from($this->getTable('sales/order'),
                array(
                    'customer_firstname' => 'customer_firstname',
                    'customer_lastname' => 'customer_lastname',
                    'fullname' => 'CONCAT(customer_firstname, \' \', customer_lastname)',
                    'products' => $productSelect
                )
            )
            ->where('entity_id = ?', $id);

        return $adapter->fetchRow($select);
    }
}