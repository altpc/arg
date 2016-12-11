<?php

class TM_ReviewReminder_Model_Config_SpecificOrders
{
    public function toOptionArray()
    {
        return array(
            array('value'=>0, 'label'=>Mage::helper('reports')->__('Any')),
            array('value'=>1, 'label'=>Mage::helper('reports')->__('Specified'))
        );
    }
}