<?php

class TM_Attributepages_Block_Widget_Attribute_List extends Mage_Core_Block_Abstract
    implements Mage_Widget_Block_Interface
{
    protected function _prepareLayout()
    {
        $list = $this->getLayout()
            ->createBlock('attributepages/attribute_list', null, $this->getData())
            ->setTemplate($this->getAttributeListTemplate());

        if ($this->getRemoveBlockWrapper()) {
            $this->setChild('attribute_list', $list);
        } else {
            $wrapper = $this->getLayout()
                ->createBlock('core/template', null, $this->getData())
                ->setTemplate($this->getWrapperTemplate());
            $wrapper->setChild('children_list', $list);
            $this->setChild('attribute_list', $wrapper);
        }

        return parent::_prepareLayout();
    }

    public function getAttributeListTemplate()
    {
        $key = 'attribute_list_template';
        $template = $this->_getData($key);
        if (null === $template) {
            $template = 'tm/attributepages/attribute/list.phtml';
            $this->setData($key, $template);
        }
        return $template;
    }

    public function getWrapperTemplate()
    {
        $key = 'wrapper_template';
        $template = $this->_getData($key);
        if (null === $template) {
            $template = 'tm/attributepages/attribute/block.phtml';
            $this->setData($key, $template);
        }
        return $template;
    }

    protected function _toHtml()
    {
        return $this->getChildHtml('attribute_list');
    }
}