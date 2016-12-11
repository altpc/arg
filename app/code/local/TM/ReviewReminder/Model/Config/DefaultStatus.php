<?php

class TM_ReviewReminder_Model_Config_DefaultStatus
{
    public function toOptionArray()
    {
        return array(
            array('value'=>TM_ReviewReminder_Model_Entity::STATUS_NEW, 'label'=>Mage::helper('reports')->__('New')),
            array('value'=>TM_ReviewReminder_Model_Entity::STATUS_PENDING, 'label'=>Mage::helper('reports')->__('Pending'))
        );
    }
}