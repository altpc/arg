<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_Prolabels_Model_Rule_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('prolabels/rule_condition_combine');
    }

    public function getNewChildSelectOptions()
    {
        $bannerConditions = Mage::getModel('prolabels/rule_condition_product')->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($bannerConditions as $code => $label) {
            $attributes[] = array('value'=>'prolabels/rule_condition_product|' . $code, 'label' => $label);
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value'=>'prolabels/rule_condition_combine', 'label'=>Mage::helper('catalogrule')->__('Conditions Combination')),
            array('value' => $attributes, 'label'=>Mage::helper('catalogrule')->__('Product'))
        ));
        return $conditions;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        
        return $this;
    }
}
