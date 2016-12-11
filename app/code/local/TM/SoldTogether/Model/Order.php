<?php

class TM_SoldTogether_Model_Order extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('soldtogether/order');
    }

    public function reindexAll()
    {
        $this->_getResource()->clearTable();
        $sales = Mage::getModel('sales/order')->getCollection();
        $sales->walk(array($this->_getResource(), 'addOrderProductData'));
    }
}
