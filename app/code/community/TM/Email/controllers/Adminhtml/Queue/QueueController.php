<?php

class TM_Email_Adminhtml_Queue_QueueController extends Mage_Adminhtml_Controller_Action
{

//    protected function _initAction() {
//        $this->loadLayout();
//        $this->_setActiveMenu('tm_email/tm_email')
//            ->_addBreadcrumb(
//                Mage::helper('tm_email')->__('Gateway Manager'),
//                Mage::helper('tm_email')->__('Gateway')
//            );
//
//        return $this;
//    }
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction()
    {
        $_id = $this->getRequest()->getParam('id', 0);

        $model = Mage::getModel('tm_email/queue_queue')->load($_id);
        $id = $model->getId();

        if (!$id && 0 !== $_id) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('tm_email')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('tm_email_queue_data', $model);

        $this->loadLayout();
        $this->_setActiveMenu('tm_email/queue_queue');
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_redirect('*/*/edit');
    }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost();

        if (!$data) {
             Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('tm_email')->__('Unable to find item to save')
            );
            $this->_redirect('*/*/');
        }

        try  {
            $model = Mage::getModel('tm_email/queue_queue');

            if (empty($data['queue_id'])) {
                unset($data['queue_id']);
            }
            $model->setData($data);

            $model->save();

            //success
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('tm_email')->__('Item was successfully saved')
            );
            Mage::getSingleton('adminhtml/session')->setFormData(false);

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }
            $this->_redirect('*/*/');
            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirect(
                '*/*/edit', array('id' => $this->getRequest()->getParam('id'))
            );
            return;
        }
    }

    public function deleteAction()
    {
        $_id = $this->getRequest()->getParam('id');
        if( 0 < $_id) {
            try {
                $model = Mage::getModel('tm_email/queue_queue');

                $model->setId($_id)->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Item was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $_id));
            }
        }
        $this->_redirect('*/*/');
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
                    $model = Mage::getModel('tm_email/queue_queue')->load($_id);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($_ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName   = 'tm_email_queue.csv';
        $content    = $this->getLayout()
            ->createBlock('tm_email/adminhtml_queue_queue_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'tm_email_queue.xml';
        $content    = $this->getLayout()
            ->createBlock('tm_email/adminhtml_queue_queue_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse(
        $fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
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