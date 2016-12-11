<?php

class TM_EasyBanner_Adminhtml_Easybanner_PlaceholderController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/easybanner/placeholder')
            ->_addBreadcrumb(Mage::helper('easybanner')->__('Templates Master'), Mage::helper('easybanner')->__('Templates Master'))
            ->_addBreadcrumb(Mage::helper('easybanner')->__('Easy Banner'), Mage::helper('easybanner')->__('Easy Banner'))
            ->_addBreadcrumb(Mage::helper('easybanner')->__('Placeholder Manager'), Mage::helper('easybanner')->__('Placeholder Manager'));
        return $this;
    }

    /**
     * Placeholder list page
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('easybanner/adminhtml_placeholder'));
        $this->renderLayout();
    }

    /**
     * Create new placeholder
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Placeholder edit form
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('easybanner/placeholder');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('easybanner')->__('This placeholder no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('easybanner_placeholder', $model);

        $this->_initAction()
            ->_addBreadcrumb($id ? Mage::helper('easybanner')->__('Edit Placeholder') : Mage::helper('easybanner')->__('New Placeholder'), $id ? Mage::helper('easybanner')->__('Edit Placeholder') : Mage::helper('easybanner')->__('New Placeholder'))
            ->_addContent(
                $this->getLayout()->createBlock('easybanner/adminhtml_placeholder_edit')
                    ->setData('action', $this->getUrl('*/*/save'))
                    ->setData('form_action_url', $this->getUrl('*/*/save'))
            );

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->renderLayout();
    }

    /**
     * Placeholder grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('easybanner/adminhtml_placeholder_grid')->toHtml()
        );
    }

    /**
     * Save placeholder
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('easybanner/placeholder');
            $model->setData($data);
            try {
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('easybanner')->__('Placeholder was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('placeholder_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('easybanner/placeholder');
                $model->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('easybanner')->__('Placeholder was successfully deleted'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('easybanner')->__('Unable to find a placeholder to delete'));
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/easybanner/placeholder');
    }
}
