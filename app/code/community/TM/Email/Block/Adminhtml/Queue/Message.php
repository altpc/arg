<?php
class TM_Email_Block_Adminhtml_Queue_Message extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_queue_message';
        $this->_blockGroup = 'tm_email';
        $this->_headerText = Mage::helper('tm_email')->__('Message');
        parent::__construct();
        $this->_removeButton('add');

        $this->_addButton('send', array(
            'label'     => Mage::helper('tm_email')->__('Send'),
            'onclick'   => "setLocation('" . $this->getUrl('*/*/send') . "')",
            'class'     => 'save',
        ), -100);
    }
}