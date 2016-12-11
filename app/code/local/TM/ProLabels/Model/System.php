<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Model_System extends Mage_Catalog_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('prolabels/system');
    }

    protected function _beforeSave()
    {
        $this->_getResource()->loadLabelProductImage($this);
        $this->_getResource()->loadLabelCategoryImage($this);
        if ($this->getRulesId() == '2') {
            $this->_getResource()->loadLabelProductOutImage($this);
            $this->_getResource()->loadLabelCategoryOutImage($this);
        }

        /* parent::_beforeSave(); */
    }

    protected function _afterLoad()
    {
        if (Mage::getStoreConfig("prolabels/general/customer_group")) {
            $this->setData('customer_group_ids', unserialize($this->getCustomerGroup()));
        }

        parent::_afterLoad();
    }

    public function deleteDisableIndex($rulesId)
    {
        $this->getResource()->deleteIndexs($rulesId);
    }

    public function getLabelProductIds($rulesId)
    {
        return $this->getResource()->getLabelProductIds($rulesId);
    }

    public function getStoreIds($id)
    {
        return $this->_getResource()->getStoreIds($id);
    }

    public function getSystemLabelsData($rulesId)
    {
        return $this->_getResource()->getSystemLabelsData($rulesId);
    }

    public function getSystemContentLabels()
    {
        return $this->_getResource()->getSystemContentLabels();
    }
}
