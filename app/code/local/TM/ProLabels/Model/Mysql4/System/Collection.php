<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Model_Mysql4_System_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('prolabels/system');
    }
    
    public function addStoreFilter($store, $withAdmin = true)
    {
        $this->getSelect()->join(
                        array('store_table' => $this->getTable('prolabels/sysstore')),
                        'main_table.system_id = store_table.system_id',
                        array()
        )
        ->where('store_table.store_id in (?)', $store)
        ->group('main_table.system_id');
    
        return $this;
    }
    
    
    public function setStoreId()
    {
        return $this;
    }
}
