<?php

class TM_EasyBanner_Model_Rule_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('easybanner/rule_condition_combine');
    }

    public function getNewChildSelectOptions()
    {
        $bannerConditions = Mage::getModel('easybanner/rule_condition_banner')->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($bannerConditions as $code => $label) {
            $attributes[] = array('value'=>'easybanner/rule_condition_banner|' . $code, 'label' => $label);
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value'=>'easybanner/rule_condition_combine', 'label'=>Mage::helper('catalogrule')->__('Conditions Combination')),
            array('value' => $attributes, 'label'=>Mage::helper('catalogrule')->__('Conditions'))
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
