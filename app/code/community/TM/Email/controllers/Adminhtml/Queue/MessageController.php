<?php

class TM_Email_Adminhtml_Queue_MessageController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function previewAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function sendAction()
    {
        $_queues = Mage::getModel('tm_email/queue')->getQueue()->getQueues();
        foreach ($_queues as $_queue) {
            Mage::getModel('tm_email/queue')
                ->setName($_queue)
                ->receive();
        }
        $this->_redirect('*/*/index');
    }

    public function statusAction()
    {
        $request = $this->getRequest();
        $_id = $request->getParam('message_id');
        $status = $request->getParam('status');
        if (!TM_Email_Model_Queue_Message_Status::isStatus($status)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('tm_email')->__('Status was not successfully changed')
            );

        } else {
            Mage::getSingleton('tm_email/queue_message')
                ->load($_id)
                ->setStatus($status)
                ->save();
            $this->_getSession()->addSuccess(
                Mage::helper('tm_email')->__('Status were successfully updated')
            );
        }
        $this->_redirectReferer();
    }

    public function massStatusAction()
    {
        $request = $this->getRequest();
        $key = $request->getParam('massaction_prepare_key');
        $_ids = $request->getParam($key);
        if(!is_array($_ids)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('tm_email')->__('Please select item(s)'
            ));
        } else {
            try {
                $status = (int) $request->getParam('status');
                foreach ($_ids as $_id) {
                    $model = Mage::getSingleton('tm_email/queue_message')
                        ->load($_id)
                        ->setStatus($status)
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    Mage::helper('tm_email')->__('Total of %d record(s) were successfully updated',
                        count($_ids)
                    )
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massDeleteAction()
    {
        $_ids = $this->getRequest()->getParam('tm_email');
        if(!is_array($_ids)) {
            Mage::getSingleton('adminhtml/session')
                ->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($_ids as $_id) {
                    $model = Mage::getModel('tm_email/queue_message')->load($_id);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($_ids)
                ));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
       return Mage::getSingleton('admin/session')
           ->isAllowed('templates_master/tm_email/queue');
    }
}