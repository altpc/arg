<?php
class TM_Attributepages_Adminhtml_Attributepages_OptionController extends
    Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/attributepages_option/index')
            ->_addBreadcrumb(
                Mage::helper('attributepages')->__('Attribute Options'),
                Mage::helper('attributepages')->__('Attribute Options')
            );
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Attribute Options'));
        $this->_initAction();
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
                    Mage::helper('attributepages')->__('This option no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        } else {
            $optionId    = $this->getRequest()->getParam('option_id');
            $attributeId = $this->getRequest()->getParam('attribute_id');
            if (!$optionId || !$attributeId) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('attributepages')->__('Invalid link recieved.'));
                $this->_redirect('*/*/');
                return;
            }
            $model->setOptionId($optionId);
            $model->setAttributeId($attributeId);
        }

        $this->_title($model->getId() ? $model->getName() : $model->getOption()->getValue());

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('attributepages_page', $model);

        $this->_initAction()
            ->_addBreadcrumb(
                Mage::helper('attributepages')->__('Edit Option'),
                Mage::helper('attributepages')->__('Edit Option')
            );

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

        if (!$this->_validatePostData($data)) {
            $this->_redirect('*/*/edit', array('entity_id' => $model->getId(), '_current' => true));
            return;
        }

        try {
            $mediaPath = Mage::getBaseDir('media') . DS . TM_Attributepages_Model_Entity::IMAGE_PATH;
            foreach (array('image', 'thumbnail') as $key) {
                if (isset($_FILES[$key]) && $_FILES[$key]['error'] == 0) {
                    try {
                        $uploader = new Varien_File_Uploader($key);
                        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png', 'bmp'));
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(true);
                        $res = $uploader->save($mediaPath);
                        $data[$key] = $uploader->getUploadedFileName();
                    } catch (Exception $e) {
                        $this->_getSession()->addError($e->getMessage());
                    }
                }

                if (isset($data[$key]) && is_array($data[$key])) {
                    if (!empty($data[$key]['delete'])) {
                        @unlink($mediaPath . $data[$key]['value']);
                        $data[$key] = null;
                    } else {
                        $data[$key] = $data[$key]['value'];
                    }
                }
            }

            $model->addData($data);
            $model->save();

            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('cms')->__('The page has been saved.')
            );
            Mage::getSingleton('adminhtml/session')->setFormData(false);
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('entity_id' => $model->getId(), '_current' => true));
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
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/attributepages/attributepages_option/save');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/attributepages/attributepages_option/delete');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/attributepages/attributepages_option');
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
}
