<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Model_Sysstore extends Mage_Catalog_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('prolabels/sysstore');
    }
    
    public function deleteDisableIndex($rulesId)
    {
        $this->getResource()->deleteIndexs($rulesId);
    }
    
    public function getStoreIds($id)
    {
        return $this->_getResource()->lookupStoreIds($id);
    }
    
    public function deleteSystemStore($systemId)
    {
        $this->getResource()->deleteSystemStore($systemId);
    }
    
    public function storeLabelExist($store, $rulesId, $systemId)
    {
        return $this->_getResource()->storeLabelExist($store, $rulesId, $systemId);
    }
    
    public function allStoreLabelExist($rulesId, $systemId)
    {
        return $this->_getResource()->allStoreLabelExist($rulesId, $systemId);
    }
}
