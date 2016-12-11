<?php
class TM_Email_Block_Adminhtml_Queue_Queue extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_queue_queue';
        $this->_blockGroup = 'tm_email';
        $this->_headerText = Mage::helper('tm_email')->__('Queues');
        $this->_addButtonLabel = Mage::helper('tm_email')->__('Add');
        parent::__construct();
    }
}