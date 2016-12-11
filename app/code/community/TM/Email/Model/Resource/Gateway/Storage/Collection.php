<?php

class TM_Email_Model_Resource_Gateway_Storage_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('tm_email/gateway_storage');
    }
}