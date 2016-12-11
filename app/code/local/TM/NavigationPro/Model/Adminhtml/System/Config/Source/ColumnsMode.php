<?php

class TM_NavigationPro_Model_Adminhtml_System_Config_Source_ColumnsMode
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'parent', 'label'=>Mage::helper('navigationpro')->__('Use Parent Menu Preferences')),
            array('value'=>'menu', 'label'=>Mage::helper('navigationpro')->__('Use Root Menu Preferences')),
            array('value'=>'custom', 'label'=>Mage::helper('navigationpro')->__('Custom'))
        );
    }
}
