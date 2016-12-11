<?php

class TM_Argento_Block_Adminhtml_System_Config_Form_Field_Image
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        if ('none' === (string)$element->getValue()) {
            $element->setValue(''); // fix to prevent activating of 'Use default' checkbox, when image is deleted
        } else {
            $theme     = $this->getRequest()->getParam('section');
            $baseUrl   = str_replace('_', '/', $theme) . '/images';
            $config    = $element->getFieldConfig();
            $baseUrlEl = $config->addChild('base_url', $baseUrl);
            $baseUrlEl->addAttribute('type', 'media');
            $baseUrlEl->addAttribute('scope_info', 0);
        }

        return $element->getElementHtml();
    }
}
