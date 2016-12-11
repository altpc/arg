<?php

class TM_EasyBanner_Adminhtml_Easybanner_Placeholder_WidgetController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Chooser Source action
     */
    public function chooserAction()
    {
        $uniqId     = $this->getRequest()->getParam('uniq_id');
        $massAction = $this->getRequest()->getParam('use_massaction', false);

        $placeholdersGrid = $this->getLayout()->createBlock('easybanner/adminhtml_placeholder_widget_chooser', '', array(
            'id'              => $uniqId,
            'use_massaction'  => $massAction
        ));

        $this->getResponse()->setBody($placeholdersGrid->toHtml());
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/easybanner/placeholder');
    }
}
