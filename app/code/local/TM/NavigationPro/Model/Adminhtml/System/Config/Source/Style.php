<?php

class TM_NavigationPro_Model_Adminhtml_System_Config_Source_Style
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'dropdown',  'label'=>Mage::helper('navigationpro')->__('Dropdown')),
            array('value'=>'accordion', 'label'=>Mage::helper('navigationpro')->__('Accordion'))
        );
    }
}
