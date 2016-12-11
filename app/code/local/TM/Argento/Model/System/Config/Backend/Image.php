<?php

class TM_Argento_Model_System_Config_Backend_Image extends Mage_Adminhtml_Model_System_Config_Backend_Image
{
    protected function _beforeSave()
    {
        if ($_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value']) {
            return parent::_beforeSave();
        }

        $value = $this->getValue();
        if (is_array($value) && !empty($value['delete'])) {
            $this->setValue('none'); // fix to prevent activating of 'Use default' checkbox, when image is deleted
        }
        // fix to save default config value on the first save
        /* else {
            $this->unsValue();
        }*/
        return $this;
    }

    /**
     * Automatic upload_dir detection added
     * Scope_info removed
     *
     * @return string
     * @throw Mage_Core_Exception
     */
    protected function _getUploadDir()
    {
        $path      = $this->getPath();
        $pathParts = explode('/', $path);
        $dir       = str_replace('_', DS, $pathParts[0]);
        $uploadDir = $dir . DS . 'images';
        return Mage::getBaseDir('media') . DS . $uploadDir;
    }
}
