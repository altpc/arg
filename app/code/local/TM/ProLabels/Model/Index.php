<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * Prolabels module for Magento - flexible label management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Model_Index extends Mage_Catalog_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('prolabels/index');
    }

    public function deleteDisableIndex($rulesId)
    {
        $this->getResource()->deleteIndexs($rulesId);
    }

    public function getLabelProductIds($rulesId)
    {
        return $this->getResource()->getLabelProductIds($rulesId);
    }
}
