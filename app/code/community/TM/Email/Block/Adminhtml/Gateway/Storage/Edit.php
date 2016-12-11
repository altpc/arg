<?php

class TM_Email_Block_Adminhtml_Gateway_Storage_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'tm_email';
        $this->_controller = 'adminhtml_gateway_storage';

        parent::__construct();

        $this->_addButton('test', array(
            'label'     => Mage::helper('tm_email')->__('Test Email Gateway Connection'),
            'onclick'   => "$('edit_form').action = '" . $this->getUrl('*/*/test') . "'; editForm.submit();",
            //'onclick'   => "$('edit_form').action = '" . $this->getUrl('*/*/test', array('back' => 'edit')) . "'; editForm.submit();",
            'class'     => 'save',
        ), -100);

    }

    public function getHeaderText()
    {
        $item = Mage::registry('tm_email_gateway_storage_data');
        if($item && $item->getId()) {

            return Mage::helper('tm_email')->__("Edit Email Gateway '%s'", $this->htmlEscape($item->getName()));

        } else {
            return Mage::helper('tm_email')->__('Add New Email Storage Gateway');
        }
    }
}