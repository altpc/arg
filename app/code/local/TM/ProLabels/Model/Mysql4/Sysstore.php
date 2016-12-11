<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Model_Mysql4_Sysstore extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('prolabels/sysstore', 'system_store_id');
    }
    
    public function deleteSystemStore($systemId)
    {
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        $write->delete($this->getTable('sysstore'), $write->quoteInto('system_id=?', $systemId));
        $write->commit();
        return $this;
    }
    
    public function lookupStoreIds($id) {
        return $this->_getReadAdapter()
            ->fetchAll($this->_getReadAdapter()->select()
                ->from($this -> getTable('sysstore'), 'store_id') -> where('system_id = ?', $id));
    }
    
    public function storeLabelExist($storeId, $rulesId, $systemId) {
        $result = $this->_getReadAdapter()
            ->fetchAll($this->_getReadAdapter()->select()
                ->from($this->getTable('sysstore'), 'system_store_id') 
                ->where('rules_id = ?', $rulesId)
                ->where('store_id = ?', $storeId)
                ->where('system_id NOT IN (?)', $systemId)
            );

        if (count($result) > 0) {
            return true;
        }
        return false;
    }
    
    public function allStoreLabelExist($rulesId, $systemId) {
        $result = $this->_getReadAdapter()
        ->fetchAll($this->_getReadAdapter()->select()
                        ->from($this->getTable('sysstore'), 'system_store_id')
                        ->where('rules_id = ?', $rulesId)
                        ->where('system_id NOT IN (?)', $systemId)
        );
    
        if (count($result) > 0) {
            return true;
        }
        return false;
    }
}