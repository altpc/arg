<?php

class TM_AjaxSearch_Model_Mysql4_Product_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    public function addSearchFilter($query)
    {
        return $this->setQueryFilter($query);
    }

    protected function _getEnabledAttributteCodes()
    {
        $attributes = array('name');
        $searchAttributes = Mage::getStoreConfig('tm_ajaxsearch/general/attributes');
        if (!empty($searchAttributes)) {
            $attributes = explode(',', $searchAttributes);
        }
        return $attributes;
    }

    public function setQueryFilter($query)
    {
        $attributes = $this->_getEnabledAttributteCodes();
        $andWhere = array();
        $orWhere = false;

        /* @var $stringHelper Mage_Core_Helper_String */
        $stringHelper = Mage::helper('core/string');

        $words = $stringHelper->splitWords($query, true);
//        $words = explode(' ', trim($query));
        if (empty($words) || empty($query)) {
            return $this;
        }

        foreach ($attributes as $attribute) {

            $this->addAttributeToSelect($attribute, true);
            foreach ($words as $word) {
                $andWhere[] = $this->_getAttributeConditionSql(
                    $attribute, array('like' => '%' . $word . '%')
                );
            }
            if ($orWhere) {
                $this->getSelect()->orWhere(implode(' AND ', $andWhere));
            } else {
                $this->getSelect()->where(implode(' AND ', $andWhere));
                $orWhere = true;
            }

            $andWhere = array();
        }

        return $this;
    }
}