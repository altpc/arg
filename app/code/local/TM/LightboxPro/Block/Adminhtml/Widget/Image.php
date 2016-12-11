<?php

class TM_LightboxPro_Block_Adminhtml_Widget_Image extends Mage_Adminhtml_Block_Template
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'tm/lightboxpro/widget/image.phtml';

    protected $_element = null;

    public function getElement()
    {
        return $this->_element;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }
}
