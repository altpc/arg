<?php

class TM_NavigationPro_Block_Adminhtml_Menu_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId   = 'menu_id';
        $this->_blockGroup = 'navigationpro';
        $this->_controller = 'adminhtml_menu';
        $this->_mode       = 'edit';

        parent::__construct();
    }
}
