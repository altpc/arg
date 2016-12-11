<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * ProLabels module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Adminhtml_Prolabels_RulesController extends Mage_Adminhtml_Controller_Action
{
    protected $_productCount = 250;

    protected $_processTime = 20;

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/prolabels/rules')
            ->_addBreadcrumb(
                Mage::helper('prolabels')->__('Templates Master'),
                Mage::helper('prolabels')->__('Templates Master'))
            ->_addBreadcrumb(
                Mage::helper('prolabels')->__('ProLabels'),
                Mage::helper('prolabels')->__('ProLabels'))
            ->_addBreadcrumb(
                Mage::helper('prolabels')->__('Rules Manager'),
                Mage::helper('prolabels')->__('Labels Manager'));
        return $this;
    }

    /**
     * Banner list page
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent(
            $this->getLayout()->createBlock('prolabels/adminhtml_rules')
        );
        $this->renderLayout();
    }

    /**
     * Create new banner
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Banner edit form
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('prolabels/label');
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('prolabels')->__('This label no longer exists')
                );
                $this->_redirect('*/*/');
                return;
            }
        }

        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $model->getConditions()->setJsFormObject('rules_conditions_fieldset');

        Mage::register('prolabels_rules', $model);

        $this->loadLayout(array('default', 'editor'))
            ->_setActiveMenu('templates_master/prolabels/rules')
            ->_addBreadcrumb(Mage::helper('prolabels')->__('Templates Master'), Mage::helper('prolabels')->__('Templates Master'))
            ->_addBreadcrumb(Mage::helper('prolabels')->__('ProLabels'), Mage::helper('prolabels')->__('ProLabels'))
            ->_addBreadcrumb(Mage::helper('prolabels')->__('Rules Manager'), Mage::helper('prolabels')->__('Labels Manager'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->setCanLoadRulesJs(true)
            ->addItem('js', 'tm/adminhtml/prolabels/tabs.js');

        $this
            ->_addBreadcrumb($id ? Mage::helper('prolabels')->__('Edit Label') : Mage::helper('prolabels')->__('New Label'), $id ? Mage::helper('prolabels')->__('Edit Label') : Mage::helper('prolabels')->__('New Label'))
            ->_addContent(
                $this->getLayout()->createBlock('prolabels/adminhtml_rules_edit')
                    ->setData('action', $this->getUrl('*/*/save'))
                    ->setData('form_action_url', $this->getUrl('*/*/save'))
            )
            ->_addLeft($this->getLayout()->createBlock('prolabels/adminhtml_rules_edit_tabs'))
            ->renderLayout();
    }

    /**
     * Banner grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('prolabels/adminhtml_rules_grid')->toHtml()
        );
    }

    /**
     * Save label
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            try {
                $model = Mage::getModel('prolabels/label');

                $data['conditions'] = $data['rule']['conditions'];
                if (isset($data['rule'])) {
                    unset($data['rule']);
                }
                $model->loadPost($data);
                Mage::getSingleton('adminhtml/session')->setPageData($model->getData());
                if (array_key_exists('priority', $data)) {
                    $model->setPriority($data['priority']);
                }
                /*
                ** Save customer groups
                */
                if (Mage::getStoreConfig("prolabels/general/customer_group")) {
                    $model->setData('customer_group', serialize($data['customer_group_ids']));
                }
                $model->setData("product_custom_url", $data['product_custom_url']);
                $model->setData("category_custom_url", $data['category_custom_url']);
                $model->save();
                $this->getRequest()->setParam('rule_id', $model->getId());
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('prolabels')->__('Label was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('prolabels/label');
                $model->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('prolabels')->__('Label was successfully deleted'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('prolabels')->__('Unable to find a label to delete'));
        $this->_redirect('*/*/');
    }

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('prolabels/label'))
            ->setPrefix('conditions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    public function chooserAction()
    {
        $block = false;
        switch ($this->getRequest()->getParam('attribute')) {
            case 'product_ids':
                $block = $this->getLayout()->createBlock(
                    'prolabels/adminhtml_widget_chooser_product', 'prolabels_widget_chooser_product',
                    array('js_form_object' => $this->getRequest()->getParam('form'),
                ));
                break;

            case 'category_ids':
                $block = $this->getLayout()->createBlock(
                        'adminhtml/catalog_category_checkboxes_tree', 'prolabels_widget_chooser_category_ids',
                        array('js_form_object' => $this->getRequest()->getParam('form'))
                    )->setCategoryIds($this->getRequest()->getParam('selected', array()));
                break;
        }
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

    /**
     * Get tree node (Ajax version)
     */
    public function categoriesJsonAction()
    {
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_category_tree')
                    ->getTreeJson($category)
            );
        }
    }

    /**
     * Initialize category object in registry
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCategory()
    {
        $categoryId = (int) $this->getRequest()->getParam('id',false);

        $storeId    = (int) $this->getRequest()->getParam('store');

        $category = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    $this->_redirect('*/*/', array('_current'=>true, 'id'=>null));
                    return false;
                }
            }
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);
        return $category;
    }

    public function applyRulesAction()
    {
        try {
            $timeStart = time();
            $labelModel = Mage::getResourceModel('prolabels/label');

            if ($this->getRequest()->getParam('clear_session')) {
                $obj = new Varien_Object();
                Mage::getSingleton('adminhtml/session')->setData('prolabels_object', $obj);
                $labelModel->deleteAllLabelIndex();
                $obj->setQueryStep(0);
                $obj->setProcessed(0);
                $obj->setProductCount($this->_productCount);
            } else {
                $obj = Mage::getSingleton('adminhtml/session')->getData('prolabels_object');
                $queryOffset = $obj->getQueryStep();
            }

            if ($obj->getReindexData()) {
                $result = $this->processRelations($timeStart);
                return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                    'completed' => false,
                    'message'   => Mage::helper('prolabels')->__(
                        '%d customer(s) reindexed', $obj->getProcessed()
                    )
                )));
            }
            $reindexData = $labelModel->getItemsToProcess($obj->getProductCount(), $obj->getQueryStep());

            $obj->addData(array(
                'reindex_data'      => $reindexData,
                'query_step'        => $obj->getQueryStep(),
                'order_offset'      => 0,
            ));

            if ($obj->getReindexData()) {

                $result = $this->processRelations($timeStart);
                return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                    'completed' => false,
                    'message'   => Mage::helper('prolabels')->__(
                        '%d product(s) reindexed', $obj->getProcessed()
                    )
                )));
            }

            Mage::getSingleton('adminhtml/session')->unsetData('prolabels_object');
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('prolabels')->__(
                    '%d product(s) was successfully reindexed', $obj->getProcessed()
                 )
            );
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'processed' => $obj->getProcessed(),
                'completed' => true,
                'message'   => Mage::helper('prolabels')->__(
                    '%d product(s) reindexed', $obj->getProcessed()
                )
            )));
        } catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function processRelations($timeStart)
    {
        $obj = Mage::getSingleton('adminhtml/session')->getData('prolabels_object');
        $reindexData = $obj->getReindexData();

        $labelModel = Mage::getResourceModel('prolabels/label');
        $ruleModel = Mage::getModel('prolabels/label');
        for ($i = $obj->getOrderOffset(); $i < count($reindexData); $i++){
            for ($j = 1; $j <= 3; $j++){
                $ruleModel->load($j);
                $labelModel->apllySystemRule($ruleModel, $reindexData[$i]);
            }

            $timeEnd = time();
            $obj->setProcessed($obj->getProcessed() + 1);
            $obj->setOrderOffset($i + 1);
            $timeRes = $timeEnd - $timeStart;
            if ($timeRes > $this->_processTime){
                return array(
                    'processed' => $i + 1,
                    'completed' => false
                );
            }
        }
        $obj->setQueryStep($obj->getQueryStep() + 1);
        $obj->setReindexData(array());
        return array(
            'processed' => $i + 1,
            'completed' => true
        );
    }

    public function saveSystemRuleData($data)
    {
        if ($data) {
            try {
                $model = Mage::getModel('prolabels/label');

                if ((int)$data['label_status'] == 0) {
                    $indexModel = Mage::getModel('prolabels/index');
                    $indexModel->deleteDisableIndex($data['rules_id']);
                }
                $model->loadPost($data);
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('prolabels')->__('Label was successfully saved'));

                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $data['rules_id']));
                return;
            }
        }
    }

    public function applySystemRuleAction()
    {
        try {
            $timeStart = time();
            $labelModel = Mage::getResourceModel('prolabels/label');

            if ($this->getRequest()->getParam('clear_session')) {
                $this->saveSystemRuleData($this->getRequest()->getParams());
                $obj = new Varien_Object();
                Mage::getSingleton('adminhtml/session')->setData('prolabels_object', $obj);
                $indexModel = Mage::getResourceModel('prolabels/index');
                $indexModel->deleteIndexs($this->getRequest()->getParam('rules_id'));
                $obj->setQueryStep(0);
                $obj->setProcessed(0);
                $obj->setProductCount($this->_productCount);
                $obj->setLabelId($this->getRequest()->getParam('rules_id'));
            } else {
                $obj = Mage::getSingleton('adminhtml/session')->getData('prolabels_object');
                $queryOffset = $obj->getQueryStep();
            }

            if ($obj->getReindexData()) {
                $result = $this->processRelations($timeStart);
                return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                    'completed' => false,
                    'message'   => Mage::helper('prolabels')->__(
                        '%d customer(s) reindexed', $obj->getProcessed()
                    )
                )));
            }
            $reindexData = $labelModel->getItemsToProcess($obj->getProductCount(), $obj->getQueryStep());

            $obj->addData(array(
                'reindex_data'      => $reindexData,
                'query_step'        => $obj->getQueryStep(),
                'order_offset'      => 0,
            ));

            if ($obj->getReindexData()) {

                $result = $this->systemProcessRelations($timeStart);
                return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                    'completed' => false,
                    'message'   => Mage::helper('prolabels')->__(
                        '%d product(s) reindexed', $obj->getProcessed()
                    )
                )));
            }

            Mage::getSingleton('adminhtml/session')->unsetData('prolabels_object');
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('prolabels')->__(
                    '%d product(s) was successfully reindexed', $obj->getProcessed()
                 )
            );
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'processed' => $obj->getProcessed(),
                'completed' => true,
                'message'   => Mage::helper('prolabels')->__(
                    '%d product(s) reindexed', $obj->getProcessed()
                )
            )));
        } catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function systemProcessRelations($timeStart)
    {
        $obj = Mage::getSingleton('adminhtml/session')->getData('prolabels_object');
        $reindexData = $obj->getReindexData();

        $labelModel = Mage::getResourceModel('prolabels/label');
        $ruleModel = Mage::getModel('prolabels/label');
        for ($i = $obj->getOrderOffset(); $i < count($reindexData); $i++){
            $ruleModel->load($obj->getLabelId());
            $labelModel->apllySystemRule($ruleModel, $reindexData[$i]);

            $timeEnd = time();
            $obj->setProcessed($obj->getProcessed() + 1);
            $obj->setOrderOffset($i + 1);
            $timeRes = $timeEnd - $timeStart;
            if ($timeRes > $this->_processTime){
                return array(
                    'processed' => $i + 1,
                    'completed' => false
                );
            }
        }
        $obj->setQueryStep($obj->getQueryStep() + 1);
        $obj->setReindexData(array());
        return array(
            'processed' => $i + 1,
            'completed' => true
        );
    }

    public function applyUserRulesAction()
    {
        $errorMessage = Mage::helper('prolabels')->__('Unable to apply rules.');
        try {
            Mage::getModel('prolabels/label')->applyAll();
            $this->_getSession()->addSuccess(Mage::helper('prolabels')->__('The labels have been applied.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($errorMessage . ' ' . $e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($errorMessage);
        }
        $this->_redirect('*/*');
    }

    public function relatedGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('prolabels/label');
        $model->load($id);
        Mage::register('prolabels_rules', $model);
        $this->loadLayout();

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('prolabels/adminhtml_rules_edit_tab_products')->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/prolabels/rules');
    }
}
