<?php

class TM_Argento_Block_Adminhtml_System_Config_Form_Field_CssSelectorHeading
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $useContainerId = $element->getData('use_container_id');

        if ($selector = $this->_getCssSelector($element)) {
            return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h4 id="%s">%s</h4></td></tr>',
                $element->getHtmlId(), $element->getHtmlId(), ($element->getLabel() . ' (' . $selector . ')')
            );
        } else {
            return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h4 id="%s">%s</h4></td></tr>',
                $element->getHtmlId(), $element->getHtmlId(), $element->getLabel()
            );
        }
    }

    protected function _getCssSelector($element)
    {
        $parts    = explode('_', $element->getId());
        $node     = $parts[count($parts) - 2];
        $section  = $parts[0] . '_'. $parts[1];
        $selector = Mage::getStoreConfig($section . '/css_selector/' . $node);
        if (!empty($selector)) {
            return $selector;
        }

        $selector = Mage::getStoreConfig('argento/css_selector/' . $node);
        if (!empty($selector)) {
            return $selector;
        }
        return false;
    }
}
