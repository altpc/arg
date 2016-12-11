<?php

class TM_NavigationPro_Model_Adminhtml_System_Config_Source_Type
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'subcategory', 'label'=>Mage::helper('navigationpro')->__('Subcategories')),
            // array('value'=>'layered', 'label'=>Mage::helper('navigationpro')->__('Layered Navigation')),
            array('value'=>'html', 'label'=>Mage::helper('navigationpro')->__('Widget or Plain Html'))
        );
    }
}
