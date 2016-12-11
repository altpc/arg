<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Model_Mysql4_Index extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('prolabels/index', 'id');
    }
    
    public function deleteIndexs($ruleId)
    {
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        $write->delete($this->getTable('prolabels/index'), $write->quoteInto('rules_id=?', $ruleId));
        $write->commit();
        return $this;
    }
    
    public function getLabelProductIds($id)
    {
        return $this->_getReadAdapter()->fetchCol($this->_getReadAdapter()->select()
            ->from($this->getTable('prolabels/index'), 'product_id')
            ->where('rules_id = ?', $id)
        );
    }
}