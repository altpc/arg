<?php

class TM_Attributepages_Block_Adminhtml_Page_Helper_Image extends Varien_Data_Form_Element_Image
{
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = Mage::getBaseUrl('media')
                . TM_Attributepages_Model_Entity::IMAGE_PATH
                . $this->getValue();
        }
        return $url;
    }
}
