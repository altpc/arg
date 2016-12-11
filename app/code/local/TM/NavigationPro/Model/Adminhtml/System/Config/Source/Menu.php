<?php

class TM_NavigationPro_Model_Adminhtml_System_Config_Source_Menu
{
    public function toOptionArray()
    {
        return Mage::getResourceModel('navigationpro/menu_collection')
            ->addFieldToFilter('root_menu_id', array('is' => new Zend_Db_Expr('NULL')))
            ->toOptionArray('name');
    }
}
