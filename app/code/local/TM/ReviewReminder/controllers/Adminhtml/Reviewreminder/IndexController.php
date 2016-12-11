<?php

class TM_ReviewReminder_Adminhtml_ReviewReminder_IndexController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init actions
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/reviewreminder_index/index')
            ->_addBreadcrumb(
                Mage::helper('tm_reviewreminder')->__('Review Reminder'),
                Mage::helper('tm_reviewreminder')->__('Manage Reminders')
            );
        return $this;
    }
    public function indexAction()
    {
        $this->_title($this->__('Templates Master'))
             ->_title($this->__('Review Reminder'))
             ->_title($this->__('Manage Reminders'));
        $this->_initAction();
        $this->renderLayout();
    }
    public function indexOrdersAction()
    {
        $stores = explode(',', $this->getRequest()->getParam('stores'));
        if (count($stores) == 0 || trim($stores[0]) == '') {
            return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'error' => $this->__('Please select store view(s)')
            )));
        }

        $fromDateType = $this->getRequest()->getParam('from_date_type');
        switch ($fromDateType) {
            case 1:
                $timestamp = strtotime('-1 year');
            break;
            case 2:
                $timestamp = strtotime('-1 month');
            break;
            case 3:
                $timestamp = strtotime('-1 week');
            break;
            case 4:
                $fromDateStr = $this->getRequest()->getParam('from_date');
                if (($timestamp = strtotime($fromDateStr)) === false) {
                    return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                        'error' => $this->__('Please enter correct date in YYYY-MM-DD format')
                    )));
                }
            break;
        }
        $fromDate = date('Y-m-d', $timestamp);

        $lastProcessed = $this->getRequest()->getParam('last_processed', 0);
        $pageSize      = $this->getRequest()->getParam('page_size', 10);

        $orderModel = Mage::getModel('sales/order');
        $orders = $orderModel
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', array('gt' => $lastProcessed))
            ->addAttributeToFilter('created_at', array('from'=>$fromDate))
            ->setPageSize($pageSize)
            ->setCurPage(1);

        if (count($stores) > 1 || $stores[0] != '0') {
            $orders->addAttributeToFilter('store_id', array('in' => $stores));
        }

        if (Mage::helper('tm_reviewreminder')->allowSpecificStatuses()) {
            $orders->addAttributeToFilter('status',
                array('in' => Mage::helper('tm_reviewreminder')->specificOrderStatuses()));
        }

        $indexedOrdersIds = Mage::getModel('tm_reviewreminder/entity')
            ->getCollection()
            ->getColumnValues('order_id');
        $newOrderIds = $orders->getAllIds();
        $orderIdsDiff = array_diff($newOrderIds, $indexedOrdersIds);
        if (count($orderIdsDiff) > 0) {
            $ordersData = array();
            foreach ($orderIdsDiff as $id) {
                $order = $orderModel->load($id);
                $customerEmail = $order->getCustomerEmail();
                $orderDate = Mage::helper('tm_reviewreminder')->getOrderDate($order);

                $ordersData[] = array(
                    'order_id' => $id,
                    'customer_email' => $customerEmail,
                    'order_date' => $orderDate,
                    'status' => Mage::helper('tm_reviewreminder')->getDefaultStatus(),
                    'hash' => Mage::helper('tm_reviewreminder')->generateHash());
            }

            Mage::getSingleton('core/resource')
                ->getConnection('core_write')
                ->insertMultiple(Mage::getResourceModel('tm_reviewreminder/entity')
                                    ->getTable('tm_reviewreminder/entity'), $ordersData);
        }

        $processed = $this->getRequest()->getParam('processed', 0) + count($orders);
        $finished  = (int)(count($orders) < $pageSize);
        if ($finished) {
            Mage::app()->getCacheInstance()->cleanType('block_html');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
            'finished'  => $finished,
            'processed' => $processed,
            'last_processed' => $orders->getLastItem()->getId()
        )));
    }

    public function massStatusAction()
    {
        $remindersIds = (array)$this->getRequest()->getParam('reviewreminder');
        $status     = (int)$this->getRequest()->getParam('status');
        try {
            foreach ($remindersIds as $remindersId) {
                $reminder = Mage::getModel('tm_reviewreminder/entity')->load($remindersId);
                $reminder->setStatus($status)->save();
            }
            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($remindersIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()
                ->addException($e, $this->__('An error occurred while updating the product(s) status.'));
        }
        $this->_redirect('*/*/');
    }
    public function massDeleteAction()
    {
        $remindersIds = $this->getRequest()->getParam('reviewreminder');
        if (!is_array($remindersIds)) {
            $this->_getSession()->addError($this->__('Please select reminder(s).'));
        } else {
            if (!empty($remindersIds)) {
                try {
                    foreach ($remindersIds as $remindersId) {
                        $reminder = Mage::getModel('tm_reviewreminder/entity')->load($remindersId);
                        $reminder->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($remindersIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }
    public function massSendAction()
    {
        $remindersIds = $this->getRequest()->getParam('reviewreminder');
        if (!is_array($remindersIds)) {
            $this->_getSession()->addError($this->__('Please select reminder(s).'));
        } else {
            if (!empty($remindersIds)) {
                try {
                    Mage::helper('tm_reviewreminder')->sendReminders($remindersIds);
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d reminder(s) have been sent.', count($remindersIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }
    /**
     * Edit action
     */
    public function editAction()
    {
        $this->_title($this->__('Templates Master'))
             ->_title($this->__('Review Reminders'))
             ->_title($this->__('Manage Reminders'));
        $id = $this->getRequest()->getParam('entity_id');
        $model = Mage::getModel('tm_reviewreminder/entity');
        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('cms')->__('This page no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            $model->addData($model->getOrderInfo());
        }
        $this->_title($this->__('Reminder for ') . $model->getCustomerEmail());
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (! empty($data)) {
            $model->setData($data);
        }
        Mage::register('reminder_data', $model);
        $this->_initAction()
            ->_addBreadcrumb(
                Mage::helper('tm_reviewreminder')->__('Edit Reminder'),
                Mage::helper('tm_reviewreminder')->__('Edit Reminder'));
        $this->renderLayout();
    }
    /**
     * Delete action
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('entity_id')) {
            try {
                $model = Mage::getModel('tm_reviewreminder/entity');
                $model->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('tm_reviewreminder')->__('The reminder has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('entity_id' => $id));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tm_reviewreminder')->__('Unable to find a reminder to delete.'));
        $this->_redirect('*/*/');
    }
    /**
     * Send action
     */
    public function sendAction()
    {
        if ($id = $this->getRequest()->getParam('entity_id')) {
            try {
                Mage::helper('tm_reviewreminder')->sendReminders(array($id));
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('tm_reviewreminder')->__('The reminder has been sent.'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('entity_id' => $id));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tm_reviewreminder')->__('Unable to find a reminder to send.'));
        $this->_redirect('*/*/');
    }
    /**
     * Save action
     */
    public function saveAction()
    {
        if (!$data = $this->getRequest()->getPost('reviewreminder')) {
            $this->_redirect('*/*/');
            return;
        }
        $model = Mage::getModel('tm_reviewreminder/entity');
        if ($id = $this->getRequest()->getParam('entity_id')) {
            $model->load($id);
        }
        try {
            $model->addData($data);
            $model->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('tm_reviewreminder')->__('Reminder has been saved.')
            );
            Mage::getSingleton('adminhtml/session')->setFormData(false);
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('entity_id' => $model->getEntityId(), '_current' => true));
                return;
            }
            $this->_redirect('*/*/');
            return;
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_getSession()->setFormData($data);
        $this->_redirect('*/*/edit', array('entity_id' => $this->getRequest()->getParam('entity_id'), '_current'=>true));
    }

    protected function _initReminder()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $model = Mage::getModel('tm_reviewreminder/entity')->load($id);
        if (!$model->getId()) {
            return false;
        }
        Mage::register('reminder_data', $model);
        return $model;
    }
    public function productsAction()
    {
        if (!$this->_initReminder()) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('This reminder no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }
    /**
     * Reminder email preview action
     */
    public function previewAction()
    {
        $reminderId = $this->getRequest()->getParam('entityId');
        $reminderModel = Mage::getModel('tm_reviewreminder/entity')->load($reminderId);
        $emailData = array();
        Mage::helper('tm_reviewreminder')->collectOrderData($reminderModel->getOrderId(), $emailData);
        $emailData = array_map("unserialize", array_unique(array_map("serialize", $emailData)));
        $customerName = $emailData['customer_name'];
        $storeId = isset($emailData['store_id']) ?
            $emailData['store_id'] : Mage::app()->getDefaultStoreView()->getId();
        $productName = $emailData[0]['name'];
        unset($emailData['customer_name']);
        unset($emailData['store_id']);

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        $templateId = Mage::helper('tm_reviewreminder')->getEmailTemplate();
        if (is_numeric($templateId)) {
            $template = Mage::getModel('core/email_template')->load($templateId);
        } else {
            $localeCode = Mage::getStoreConfig('general/locale/code', $storeId);
            $template = Mage::getModel('core/email_template')->loadDefault($templateId, $localeCode);
        }
        $template->setDesignConfig(
            array(
                'area'  => 'frontend',
                'store' => $storeId
            ));

        $productsList = Mage::helper('tm_reviewreminder')->getProductsList($emailData);
        $subject = Mage::helper('tm_reviewreminder')->filterEmailSubject($customerName, $productName, $storeId);
        $result = $template->getProcessedTemplate(array(
            'subject' => $subject,
            'products' => $emailData,
            'customer_name' => $customerName,
            'products_list' => $productsList
        ));
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
           'completed' => true,
           'html'      => $result
       )));
    }
     /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());
        switch ($action) {
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tm_reviewreminder/save');
                break;
            case 'delete':
            case 'massdelete':
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tm_reviewreminder/delete');
                break;
            case 'send':
            case 'masssend':
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tm_reviewreminder/send');
                break;
            case 'massstatus':
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tm_reviewreminder/status');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tm_reviewreminder');
                break;
        }
    }
}
