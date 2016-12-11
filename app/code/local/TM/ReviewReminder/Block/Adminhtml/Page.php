<?php
class TM_ReviewReminder_Block_Adminhtml_Page extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'reviewreminder';
        $this->_controller = 'adminhtml_page';
        $this->_headerText = Mage::helper('tm_reviewreminder')->__('Manage Reminders');
        parent::__construct();

        $this->_removeButton('add');
    }
    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/tm_reviewreminder/' . $action);
    }
}
