<?php
class TM_Email_Model_Queue_Queue extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('tm_email/queue_queue');
    }

    public function toOptionArray()
    {
        return $this->_getResource()->toOptionArray();
    }

    public function toOptionHash()
    {
        return $this->_getResource()->toOptionHash();
    }
}