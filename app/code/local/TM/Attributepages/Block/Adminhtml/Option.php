<?php

class TM_Attributepages_Block_Adminhtml_Option extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'attributepages';
        $this->_controller = 'adminhtml_page';
        $this->_headerText = Mage::helper('attributepages')->__('Manage Options');

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
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/attributepages/attributepages_option/' . $action);
    }
}
