<?php

class TM_LightboxPro_Adminhtml_Lightboxpro_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function uploadAction()
    {
        $path = Mage::getBaseDir('media') . '/lightboxpro/';
        if (!$this->getRequest()->isPost()){
            return;
        }

        try{
            $uploader = new Varien_File_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($path);
        } catch (Exception $e) {
            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(array(
                    'success' => false,
                    'message' => $e->getMessage()
            )));
            return;
        }
        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode(array(
                'success' => true,
                'path'    => $result['file']
        )));
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('templates_master/lightboxpro/index');
    }
}
