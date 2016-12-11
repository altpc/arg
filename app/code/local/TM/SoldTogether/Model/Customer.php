<?php

class TM_SoldTogether_Model_Customer extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('soldtogether/customer');
    }
    
    public function reindexAll()
    {
        $this->_getResource()->clearTable();
        $customer = Mage::getResourceModel('customer/customer_collection');
        $customer->walk(array($this->_getResource(), 'addOrderProductData'));
    }
}