<?php

class TM_EasyBanner_Block_Adminhtml_Placeholder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_placeholder';
        $this->_blockGroup = 'easybanner';
        $this->_headerText = Mage::helper('easybanner')->__('Manage Placeholders');
        $this->_addButtonLabel = Mage::helper('easybanner')->__('Add Placeholder');
        parent::__construct();
    }
}