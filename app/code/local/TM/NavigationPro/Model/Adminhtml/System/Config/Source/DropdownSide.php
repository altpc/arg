<?php

class TM_NavigationPro_Model_Adminhtml_System_Config_Source_DropdownSide
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'right', 'label'=>Mage::helper('navigationpro')->__('Right')),
            array('value'=>'left', 'label'=>Mage::helper('navigationpro')->__('Left'))
        );
    }
}
