<?php
class TM_SoldTogether_Adminhtml_Soldtogether_OrderController extends Mage_Adminhtml_Controller_Action
{
    protected $_orderCount = 20;
    protected $_processTime = 20;

    /**
     * Initialize product from request parameters
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('id');
        $product   = Mage::getModel('catalog/product')
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

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('soldtogether/order')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Order Manager'),
                Mage::helper('adminhtml')->__('Order Manager'));
        return $this;
    }

    public function indexAction() {
        $this->_initAction();
        $this->_addContent(
            $this->getLayout()->createBlock('soldtogether/adminhtml_order')
        );
        $this->renderLayout();
    }

    public function editAction()
    {
        $relationId     = $this->getRequest()->getParam('id');
        $relationModel  = Mage::getModel('soldtogether/order')->load($relationId);

        if ($relationModel->getRelationId() || $relationId == 0) {

            Mage::register('soldtogether_data', $relationModel);

            $this->loadLayout();
            $this->_setActiveMenu('soldtogether/items');

            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Item Manager'),
                Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Item News'),
                Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('soldtogether/adminhtml_order_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('soldtogether')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    /**
     * Banner grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('soldtogether/adminhtml_order_grid')->toHtml()
        );
    }

    public function massDeleteAction()
    {
        $relationIds = $this->getRequest()->getParam('relation_id');
        if (!is_array($relationIds)) {
            $this->_getSession()->addError($this->__('Please select items.'));
        }
        else {
            try {
                foreach ($relationIds as $relationId) {
                    $relation = Mage::getSingleton('soldtogether/order')->load($relationId);
                    $relation->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been deleted.', count($relationIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function saveAction()
    {
        if ( $this->getRequest()->getPost() ) {
            try {
                $postData = $this->getRequest()->getPost();
                $relationModel = Mage::getModel('soldtogether/order');

                $relationModel
                    ->setId($this->getRequest()->getParam('relation_id'))
                    ->setWeight($postData['weight'])
                    ->setIsAdmin(1)
                    ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setsoldtogetherData(false);

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setsoldtogetherData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('related_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $soldtogetherModel = Mage::getModel('soldtogether/order');

                $soldtogetherModel->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function reindexAction()
    {
        try {
            $timeStart = time();
            $saleorder = Mage::getResourceModel('soldtogether/order');

            if ($this->getRequest()->getParam('clear_session')) {
                $obj = new Varien_Object();
                Mage::getSingleton('adminhtml/session')->setData('soldtogether_object', $obj);
                $saleorder->clearTable();
                $obj->setQueryStep(0);
                $obj->setProcessed(0);
                $obj->setOrderCount($this->_orderCount);
            } else {
                $obj = Mage::getSingleton('adminhtml/session')->getData('soldtogether_object');
                $queryOffset = $obj->getQueryStep();
            }

            if ($obj->getReindexData()) {
                $result = $this->processRelations($timeStart);
                return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                    'completed' => false,
                    'message'   => Mage::helper('soldtogether')->__(
                        '%d order(s) reindexed', $obj->getProcessed()
                    )
                )));
            }
            $reindexData = $saleorder->getXZ($obj->getOrderCount(), $obj->getQueryStep());

            $i = 0;
            $newReindexData = array();
            foreach ($reindexData as $reindexArr => $values){
                $newReindexData[$i] = array();
                $temp = $values;
                $j = 0;
                foreach ($temp as $productIds) {
                    $newReindexData[$i][$j] = $productIds;
                    $j++;
                }
                $i++;
            }

            $obj->addData(array(
                'reindex_data'      => $newReindexData,
                'query_step'        => $obj->getQueryStep(),
                'order_offset'      => 0,
                'parent_offset'     => 0,
                'relation_offset'   => 0,
                'relation_i_offset' => 0,
                'relation_j_offset' => 0
            ));

            if ($obj->getReindexData()) {
                $result = $this->processRelations($timeStart);
                return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                    'completed' => false,
                    'message'   => Mage::helper('soldtogether')->__(
                        '%d order(s) reindexed', $obj->getProcessed()
                    )
                )));
            }

            Mage::getSingleton('adminhtml/session')->unsetData('soldtogether_object');
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('soldtogether')->__(
                    '%d order(s) was successfully reindexed', $obj->getProcessed()
                 )
            );
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'processed' => $obj->getProcessed(),
                'completed' => true,
                'message'   => Mage::helper('soldtogether')->__(
                    '%d order(s) reindexed', $obj->getProcessed()
                )
            )));
        } catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function processRelations($timeStart)
    {
        $obj = Mage::getSingleton('adminhtml/session')->getData('soldtogether_object');
        $obj->setCurrentParentOffset(0);
        $reindexData = $obj->getReindexData();

        $saleorder = Mage::getResourceModel('soldtogether/order');
        for ($i = $obj->getOrderOffset(), $limit = count($reindexData); $i < $limit;) {
            for ($j = $obj->getParentOffset(), $limit2 = count($reindexData[$i]); $j < $limit2;){
                $relations = $this->getRelations($reindexData[$i][$j], $timeStart);
                $incompleted = false;
                if (isset($relations['incompleted'])) {
                    $incompleted = true;
                    unset($relations['incompleted']);
                }
                for ($k = $obj->getRelationOffset(), $limit3 = count($relations); $k < $limit3;) {
                    $saleorder->saveData($relations[$k]);
                    $timeEnd = time();
                    $obj->setRelationOffset(++$k);
                    $timeRes = $timeEnd - $timeStart;
                    if ($timeRes > $this->_processTime || $obj->getCurrentParentOffset() > 25){
                        return array(
                            'processed' => $i,
                            'completed' => false
                        );
                    }
                }
                $obj->setRelationOffset(0);
                $currOffset = $obj->getCurrentParentOffset();
                $obj->setCurrentParentOffset(++$currOffset);
                if ($incompleted) {
                    return array(
                        'processed' => $i,
                        'completed' => false
                    );
                }
                $obj->setParentOffset(++$j);

            }
            $obj->setProcessed($obj->getProcessed() + 1);
            $obj->setOrderOffset(++$i);
            $obj->setParentOffset(0);
        }
        $obj->setQueryStep($obj->getQueryStep() + 1);
        $obj->setReindexData(array());
        return array(
            'processed' => $i,
            'completed' => true
        );;
    }

    public function getRelations($productIds, $timeStart)
    {
        $obj = Mage::getSingleton('adminhtml/session')->getData('soldtogether_object');
        $obj->setCurrentJOffset(0);
        $result = array();
        $limit = count($productIds);
        $k = 0;

        for ($i = $obj->getData('relation_i_offset'); $i < $limit;) {

            for ($j = $obj->getData('relation_j_offset'); $j < $limit; $j++) {
                $obj->setData('relation_j_offset', $j + 1);
                $currOffset = $obj->getCurrentJOffset();
                $obj->setCurrentJOffset(++$currOffset);
                if (!$this->productsExist($productIds[$i], $productIds[$j])) {
                    continue ;
                }

                if ($productIds[$i] == $productIds[$j]){
                    continue;
                }

                $result[$k]['product_id'] = $productIds[$i];
                $result[$k]['related_product_id'] = $productIds[$j];
                $k++;
                $timeEnd = time();

                $timeRes = $timeEnd - $timeStart;
                if ($timeRes > $this->_processTime || $obj->getCurrentJOffset() > 250){
                    $result['incompleted'] = true;
                    return $result;
                }
            }
            $obj->setData('relation_j_offset', 0);
            $obj->setData('relation_i_offset', ++$i);
        }
        return $result;
    }

    public function relatedAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('soldtogether.product.edit.tab.order')
            ->setProductsRelated($this->getRequest()->getPost('soldtogether_order', null));

        $this->renderLayout();
    }

    public function relatedGridAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('soldtogether.product.edit.tab.order')
            ->setProductsRelated($this->getRequest()->getPost('soldtogether_order', null));

        $this->renderLayout();
    }

    public function productsExist($product1, $product2)
    {
        $model1 = Mage::getModel('catalog/product');
        $model2 = Mage::getModel('catalog/product');
        $model1->load($product1);
        $model2->load($product2);
        if ($model1->getId() && $model2->getId()) {
            return true;
        }
        return false;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/soldtogether/order');
    }
}
