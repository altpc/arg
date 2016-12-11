<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Model_Rule_Condition_Rules extends Mage_CatalogRule_Model_Rule_Condition_Product
{
    /**
     * Collect validated attributes
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $productCollection
     * @return Mage_CatalogRule_Model_Rule_Condition_Product
     */
    public function loadAttributeOptions()
    {
        $attributes = array(
            'product_ids'       => Mage::helper('easybanner')->__('Product')
        );
        asort($attributes);
        $this->setAttributeOption($attributes);
        return $this;
    }
    
    public function collectValidatedAttributes($productCollection)
    {
        $attribute = $this->getAttribute();

        if ('category_ids' != $attribute && 'product_ids' != $attribute) {
            
            if ($this->getAttributeObject()->isScopeGlobal()) {
                $attributes = $this->getRule()->getCollectedAttributes();
                $attributes[$attribute] = true;
                $this->getRule()->setCollectedAttributes($attributes);
                $productCollection->addAttributeToSelect($attribute, 'left');
                
            } else {
                $this->_entityAttributeValues = $productCollection->getAllAttributeValues($attribute);
            }
        }

        return $this;
    }
}
