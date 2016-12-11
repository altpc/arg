<?php

    class TM_Email_Model_Resource_Queue_Queue extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        // Note that the id refers to the key field in your database table.
        $this->_init('tm_email/queue_queue', 'queue_id');
    }

    /**
     *
     * @return array
     */
    protected function _getFetchAll()
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
        ;
        return $this->_getReadAdapter()->fetchAll($select);
    }

    public function toOptionHash()
    {
        return $this->_toOptionHash();
    }

    protected function _toOptionHash($valueField = 'queue_id', $labelField = 'queue_name')
    {
        $rowset = array();
        foreach ($this->_getFetchAll() as $row)
        {
            $rowset[$row[$valueField]] = $row[$labelField];
        }

        return $rowset;
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray();
    }

    protected function _toOptionArray($valueField = 'queue_id', $labelField = 'queue_name', $additional=array())
    {
        $result = array();
        $additional['value'] = $valueField;
        $additional['label'] = $labelField;

        foreach ($this->_getFetchAll() as $_row) {
            foreach ($additional as $code => $field) {
                $data[$code] = $_row[$field];
            }
            $result[] = $data;
        }
        return $result;
    }
}