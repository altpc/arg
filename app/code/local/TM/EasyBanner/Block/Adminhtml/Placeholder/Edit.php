<?php

class TM_EasyBanner_Block_Adminhtml_Placeholder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId   = 'id';
        $this->_blockGroup = 'easybanner';
        $this->_controller = 'adminhtml_placeholder';

        $this->_updateButton('save', 'label', Mage::helper('easybanner')->__('Save Placeholder'));
        $this->_updateButton('delete', 'label', Mage::helper('easybanner')->__('Delete Placeholder'));
    }

    public function getHeaderText()
    {
        if (Mage::registry('easybanner_placeholder') && Mage::registry('easybanner_placeholder')->getId()) {
            return Mage::helper('easybanner')->__("Edit Placeholder '%s'", $this->htmlEscape(Mage::registry('easybanner_placeholder')->getName()));
        } else {
            return Mage::helper('easybanner')->__('Add Placeholder');
        }
    }

}