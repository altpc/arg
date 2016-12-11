<?php

class TM_AskIt_Adminhtml_AskIt_IndexController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction() {

        $this->loadLayout();
        $this->_setActiveMenu('askit/items')
            ->_addBreadcrumb(
                Mage::helper('askit')->__('Questions Manager'),
                Mage::helper('askit')->__('Question')
            );

        return $this;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
          ->isAllowed('templates_master/askit');
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');

        $model = Mage::getModel('askit/message')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('askit_data', $model);

            $this->loadLayout(array('default', 'editor'));
            $this->_setActiveMenu('askit/items');

            $this->_addBreadcrumb(
                Mage::helper('askit')->__('Question Manager'),
                Mage::helper('askit')->__('Question Manager')
            );
            $this->_addBreadcrumb(
                Mage::helper('askit')->__('Item News'),
                Mage::helper('askit')->__('Item News')
            );

            $this->getLayout()->getBlock('head')
                ->setCanLoadExtJs(true)
                ->setCanLoadRulesJs(true)
                ;
//            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $parentId = (int)$model->getParentId();
            if (0 == $parentId) {
                $this->_addContent(
                    $this->getLayout()->createBlock('askit/adminhtml_askIt_edit')
                )->_addLeft(
                    $this->getLayout()->createBlock('askit/adminhtml_askIt_edit_tabs')
                );
            } else {
                $this->_addContent(
                    $this->getLayout()->createBlock('askit/adminhtml_askIt_editAnswer')
                )->_addContent(
                    $this->getLayout()->createBlock('askit/adminhtml_askIt_editAnswer_form')
                );
            }
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('askit')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        // $data = $this->getRequest()->getPost();
        // \Zend_Debug::dump($data);
        // die;
        if ($data = $this->getRequest()->getPost()) {
//            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('askit/message');

            //update exist
            if (!empty($data['id'])) {

                $model->setData($data);
                $id = (int)$data['id'];
                $model->setId($id);
            } else {
                //new question create
                $model
                    ->setText(strip_tags($data['text']))
                    ->setHint((int)$data['hint'])
                    ->setParentId(null)
                    ->setStoreId($data['store_id'])
                    ->setEmail($data['email'])
                    ->setStatus((int)$data['status'])
                    ->setCreatedTime($data['created_time'])
//                    ->setUpdateTime(now())
                    ;

                $customer = Mage::getModel('customer/customer')->getCollection()
                    ->addAttributeToFilter(array(
                        array('attribute' => 'email', 'like' => $data['email'])
                    ))->getFirstItem();


                if ($customer) {
                    $model->setCustomerName($customer->getName())
                        ->setCustomerId($customer->getId())
                    ;
                }
                $model->save();

                $id = (int)$model->getId();

            }
            try {
                if ($model->getCreatedTime() == null) {
                    $model->setCreatedTime(now());
                }
                $model->setUpdateTime(now());

                $model->save();
                //add new answer
                if (!empty($data['new_answer_text'])) {

                    $newAnswer = Mage::getModel('askit/message');
                    $adminUser = Mage::getSingleton('admin/session')->getUser();
                    $newAnswer
                        ->setParentId($id)
                        ->setStatus(TM_AskIt_Model_Status::STATUS_APROVED)
                        ->setStoreId($data['store_id'])
                        ->setText($data['new_answer_text'])
                        ->setHint(0)
                        ->setCustomerName($adminUser->getFirstname() . ' ' . $adminUser->getLastname())
                        ->setEmail($adminUser->getEmail())
                        ->setCreatedTime(now())
                        ->setUpdateTime(now())
                        ->save()
                    ;

                    Mage::dispatchEvent(
                        'askit_item_customer_notify_save_after',
                        array('data_object' => $newAnswer)
                    );
                }

                //delete and add assign
                Mage::getModel('askit/message')->addAssignsFromPost($id, $data);

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('askit')->__('Item was successfully saved')
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
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('askit')->__('Unable to find item to save')
        );
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id > 0) {
            try {
                $model = Mage::getModel('askit/message');

                $model->setId($id)->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Item was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $askitIds = $this->getRequest()->getParam('askit');
        if (!is_array($askitIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('Please select item(s)')
            );
        } else {
            try {
                foreach ($askitIds as $askitId) {
                    $askit = Mage::getModel('askit/message')->load($askitId);
                    $askit->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted',
                        count($askitIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $askitIds = $this->getRequest()->getParam('askit');
        if (!is_array($askitIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('askit')->__('Please select item(s)')
            );
        } else {
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($askitIds as $askitId) {
                    $askit = Mage::getSingleton('askit/message')
                        ->load($askitId)
                        ->setStatus($status)
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    Mage::helper('askit')->__(
                        'Total of %d record(s) were successfully updated',
                        count($askitIds)
                    )
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName   = 'askit.csv';
        $content    = $this->getLayout()->createBlock('askit/adminhtml_askit_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'askit.xml';
        $content    = $this->getLayout()->createBlock('askit/adminhtml_askit_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    public function productAction()
    {
        $query = $this->getRequest()->getParam('query');

        $model = new Mage_Adminhtml_Model_Search_Catalog();
        $results = $model->setStart(1)
            ->setLimit(10)
            ->setQuery($query)
            ->load()
            ->getResults();

        $html = '';
        foreach ($results as $item) {
            list( , , $productId) = explode('/', $item['id']);
            $html .= "<li id=\"{$productId}\" title=\"{$item['name']}\">
                <strong>{$item['name']}</strong><br/>
                <span class=\"informal\">{$item['description']}</span>
            </li>"
            ;
        }
        echo '<ul>' . $html . '</ul>';
    }

    /**
     * Get related products grid and serializer block
     */
    public function assignproductsAction()
    {
        // \Zend_Debug::dump($this->getFullActionName());
        // die;
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('askit/message')->load($id);
        Mage::register('askit_data', $model);

        $assignedProducts = $this->getRequest()->getPost('assigned_products', null);
        $this->loadLayout();
        $this->getLayout()->getBlock('askit.item.edit.tab.assigned.products')
            ->setAssignedProducts($assignedProducts);
        $this->renderLayout();
    }

    public function assignproductsGridAction()
    {
        $this->assignproductsAction();
    }

    public function assignpagesAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('askit/message')->load($id);
        Mage::register('askit_data', $model);

        $assigneds = $this->getRequest()->getPost('assigned_pages', null);
        $this->loadLayout();
        $this->getLayout()->getBlock('askit.item.edit.tab.assigned.pages')
            ->setAssignedPages($assigneds);
        $this->renderLayout();
    }

    public function assignpagesGridAction()
    {
        $this->assignpagesAction();
    }

    public function assigncatsAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('askit/message')->load($id);
        Mage::register('askit_data', $model);

        $assigneds = $this->getRequest()->getPost('assigned_cats', null);
        $this->loadLayout();
        $this->getLayout()->getBlock('askit.item.edit.tab.assigned.cats')
            ->setAssignedCats($assigneds);
        $this->renderLayout();
    }

    public function assigncatsGridAction()
    {
        $this->assigncatsAction();
    }

    /**
     * Initialize product from request parameters
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function initProduct()
    {
        $productId  = (int) $this->getRequest()->getParam('id');
        $product    = Mage::getModel('catalog/product')
            ->setStoreId($this->getRequest()->getParam('store', 0));

        if (!$productId) {
            if ($setId = (int) $this->getRequest()->getParam('set')) {
                $product->setAttributeSetId($setId);
            }

            if ($typeId = $this->getRequest()->getParam('type')) {
                $product->setTypeId($typeId);
            }
        }

        $product->setData('_edit_mode', true);
        if ($productId) {
            $product->load($productId);
        }

        $attributes = $this->getRequest()->getParam('attributes');
        if ($attributes && $product->isConfigurable() &&
            (!$productId || !$product->getTypeInstance()->getUsedProductAttributeIds())) {
            $product->getTypeInstance()->setUsedProductAttributeIds(
                explode(",", base64_decode(urldecode($attributes)))
            );
        }

        Mage::register('product', $product);
        Mage::register('current_product', $product);
        return $product;
    }

    public function questionbyproductAction()
    {
        $this->initProduct();
        $this->loadLayout();
        $this->renderLayout();
    }
}
