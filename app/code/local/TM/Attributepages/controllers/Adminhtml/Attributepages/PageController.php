<?php
class TM_Attributepages_Adminhtml_Attributepages_PageController extends
    Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/attributepages_page/index')
            ->_addBreadcrumb(
                Mage::helper('attributepages')->__('Attribute Pages'),
                Mage::helper('attributepages')->__('Attribute Pages')
            );
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Attribute Pages'));
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Forward to edit action is not used to hide store switcher
     */
    public function newAction()
    {
        if ($this->getRequest()->getParam('attribute_id')) {
            $this->_forward('edit');
            return;
        }

        $model = Mage::getModel('attributepages/entity');
        Mage::register('attributepages_page', $model);

        $this->_title($this->__('Attribute Pages'));
        $this->_initAction()
            ->_addBreadcrumb(
                Mage::helper('cms')->__('New Page'),
                Mage::helper('cms')->__('New Page')
            );
        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_title($this->__('Attribute Pages'));

        $id = $this->getRequest()->getParam('entity_id');
        $model = Mage::getModel('attributepages/entity');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('cms')->__('This page no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        } elseif ($attributeId = $this->getRequest()->getParam('attribute_id')) {
            $model->setAttributeId($attributeId);
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Page'));

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('attributepages_page', $model);

        $this->_initAction()
            ->_addBreadcrumb(
                $id ? Mage::helper('cms')->__('Edit Page')
                    : Mage::helper('cms')->__('New Page'),
                $id ? Mage::helper('cms')->__('Edit Page')
                    : Mage::helper('cms')->__('New Page'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$data = $this->getRequest()->getPost('attributepage')) {
            $this->_redirect('*/*/');
            return;
        }

        $model = Mage::getModel('attributepages/entity');
        if ($id = $this->getRequest()->getParam('entity_id')) {
            $model->load($id);
        }
        $model->addData($data);

        if (!$this->_validatePostData($data)) {
            $this->_redirect('*/*/edit', array('entity_id' => $model->getId(), '_current' => true));
            return;
        }

        try {
            $model->save();

            // save options
            $optionData = $this->getRequest()->getPost('option', array());
            $existingOptions = Mage::getModel('attributepages/entity')
                ->getCollection()
                ->addOptionOnlyFilter()
                ->addFieldToFilter('attribute_id', $model->getAttributeId())
                ->addStoreFilter(Mage::app()->getStore());
            $optionToEntity = array();
            foreach ($existingOptions as $entity) {
                $optionToEntity[$entity->getOptionId()] = $entity->getId();
            }

            $messages  = array();
            $mediaPath = Mage::getBaseDir('media') . DS . TM_Attributepages_Model_Entity::IMAGE_PATH;
            foreach ($model->getRelatedOptions() as $option) {
                $optionId = $option->getId();
                // skip if already exists and no changes are made
                if (isset($optionToEntity[$optionId]) && !isset($optionData[$optionId])) {
                    continue;
                }

                $_data = isset($optionData[$optionId]) ? $optionData[$optionId] : array();

                foreach (array('image', 'thumbnail') as $key) {
                    if (isset($_FILES['option_' . $optionId . '_' . $key])
                        && $_FILES['option_' . $optionId . '_' . $key]['error'] == 0) {

                        try {
                            $uploader = new Varien_File_Uploader('option_' . $optionId . '_' . $key);
                            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png', 'bmp'));
                            $uploader->setAllowRenameFiles(true);
                            $uploader->setFilesDispersion(true);
                            $uploader->save($mediaPath);
                            $_data[$key] = $uploader->getUploadedFileName();
                        } catch (Exception $e) {
                            $this->_getSession()->addError($e->getMessage());
                        }
                    }
                }

                $entity = Mage::getModel('attributepages/entity');
                if (!empty($_data['entity_id'])) {
                    $entity->load($_data['entity_id']);
                }
                unset($_data['entity_id']);

                if (!$entity->getId()) {
                    $entity->importOptionData($option);
                }
                $entity->addData($_data);
                try {
                    $urlChanged = false;
                    if (!$entity->getResource()->getIsUniquePageToStores($entity)) {
                        $urlChanged = true;
                        $entity->setIdentifier(
                            $entity->getIdentifier() . '-' . $option->getId()
                        );
                    }

                    $entity->save();

                    // show notice if url was changed
                    if ($urlChanged) {
                        $notice = Mage::helper('attributepages')->__('The following urls where automatically changed because of duplicates.');
                        $messages['addNotice'][$notice][] = $entity->getIdentifier();
                    }
                } catch (Exception $e) {
                    $messages['addError'][$e->getMessage()][] = $option->getValue();
                }
            }
            // display grouped error and notice messages
            foreach ($messages as $method => $groupedMessage) {
                foreach ($groupedMessage as $message => $ids) {
                    $this->_getSession()->{$method}($message . '<br/>' . implode(', ', $ids));
                }
            }

            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('cms')->__('The page has been saved.')
            );
            Mage::getSingleton('adminhtml/session')->setFormData(false);
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('entity_id' => $model->getId(), '_current'=>true));
                return;
            }
            $this->_redirect('*/*/');
            return;
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_getSession()->setFormData($data);
        $this->_redirect('*/*/edit', array(/*'entity_id' => $this->getRequest()->getParam('entity_id')*/ '_current'=>true));
    }

    public function duplicateAction()
    {
        if ($id = $this->getRequest()->getParam('entity_id')) {
            try {
                $model = Mage::getModel('attributepages/entity');
                $model->load($id);
                if (!$model->getId()) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cms')->__('This page no longer exists.'));
                    $this->_redirect('*/*/');
                    return;
                }
                $newModel = $model->duplicate();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('attributepages')->__('The page has been duplicated.')
                );
                $this->_redirect('*/*/edit', array('_current' => true, 'entity_id' => $newModel->getId()));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('entity_id' => $id));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('attributepages')->__('Unable to find a page to duplicate.'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('entity_id')) {
            try {
                $model = Mage::getModel('attributepages/entity');
                $model->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('cms')->__('The page has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('entity_id' => $id));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cms')->__('Unable to find a page to delete.'));
        $this->_redirect('*/*/');
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
            case 'new':
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/attributepages/attributepages_page/save');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/attributepages/attributepages_page/delete');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/attributepages/attributepages_page');
                break;
        }
    }

    /**
     * Validate post data
     *
     * @param array $data
     * @return bool     Return FALSE if someone item is invalid
     */
    protected function _validatePostData($data)
    {
        $errorNo = true;
        if (!empty($data['layout_update_xml'])) {
            /** @var $validatorCustomLayout Mage_Adminhtml_Model_LayoutUpdate_Validator */
            $validatorCustomLayout = Mage::getModel('adminhtml/layoutUpdate_validator');
            if (!$validatorCustomLayout->isValid($data['layout_update_xml'])) {
                $errorNo = false;
            }
            foreach ($validatorCustomLayout->getMessages() as $message) {
                $this->_getSession()->addError($message);
            }
        }
        return $errorNo;
    }

    public function optionsAction()
    {
        $model = Mage::getModel('attributepages/entity');
        if ($id = $this->getRequest()->getParam('entity_id')) {
            $model->load($id);
        } elseif ($attributeId = $this->getRequest()->getParam('attribute_id')) {
            $model->setAttributeId($attributeId);
        }
        Mage::register('attributepages_page', $model);

        $this->loadLayout();
        $this->getLayout()->getBlock('attributepages_page_edit_tab_options')
            ->setOptionsExcluded($this->getRequest()->getPost('options_excluded', null));
        $this->renderLayout();
    }

    public function optionsGridAction()
    {
        $model = Mage::getModel('attributepages/entity');
        if ($id = $this->getRequest()->getParam('entity_id')) {
            $model->load($id);
        } elseif ($attributeId = $this->getRequest()->getParam('attribute_id')) {
            $model->setAttributeId($attributeId);
        }
        Mage::register('attributepages_page', $model);

        $this->loadLayout();
        $this->getLayout()->getBlock('attributepages_page_edit_tab_options')
            ->setOptionsExcluded($this->getRequest()->getPost('options_excluded', null));
        $this->renderLayout();
    }
}
