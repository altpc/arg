<?php

class TM_EasyBanner_Block_Adminhtml_Banner_Helper_Image extends Varien_Data_Form_Element_Image
{
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = Mage::getBaseUrl('media') . trim(Mage::getStoreConfig('easybanner/general/image_folder'), ' /\\') . '/' . $this->getValue();
        }
        return $url;
    }
}
