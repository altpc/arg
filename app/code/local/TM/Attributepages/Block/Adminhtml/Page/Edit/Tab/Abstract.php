<?php

class TM_Attributepages_Block_Adminhtml_Page_Edit_Tab_Abstract
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function getPage()
    {
        return Mage::registry('attributepages_page');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return (bool) $this->getPage()->getAttributeId();
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return !$this->canShowTab();
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        $prefix = 'templates_master/attributepages/attributepages_page/';
        if ($this->getPage()->getOption()) {
            $prefix = 'templates_master/attributepages/attributepages_option/';
        }
        return Mage::getSingleton('admin/session')->isAllowed($prefix . $action);
    }
}
