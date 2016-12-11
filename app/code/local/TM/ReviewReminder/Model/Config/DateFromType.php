<?php

class TM_ReviewReminder_Model_Config_DateFromType
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('tm_reviewreminder')->__('Last year')),
            array('value' => 2, 'label'=>Mage::helper('tm_reviewreminder')->__('Last month')),
            array('value' => 3, 'label'=>Mage::helper('tm_reviewreminder')->__('Last week')),
            array('value' => 4, 'label'=>Mage::helper('tm_reviewreminder')->__('From custom date'))
        );
    }
}