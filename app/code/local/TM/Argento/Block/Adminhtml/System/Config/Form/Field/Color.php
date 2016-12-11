<?php

class TM_Argento_Block_Adminhtml_System_Config_Form_Field_Color
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $element
            ->setClass(
                $element->getClass()
                . ' color {hash:1,required:0,pickerClosable:1,adjust:0,pickerPosition:\'right\'}'
            )
            ->getElementHtml();
    }
}