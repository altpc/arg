<?php

class TM_Attributepages_Block_Widget_Option_List extends Mage_Core_Block_Abstract
    implements Mage_Widget_Block_Interface
{
    protected function _prepareLayout()
    {
        if (null === $this->_getData('group_by_first_letter')) {
            $this->setData('group_by_first_letter', 0);
        }

        $optionList = $this->getLayout()
            ->createBlock('attributepages/option_list', null, $this->getData())
            ->setTemplate($this->getOptionListTemplate());

        if ($this->getRemoveBlockWrapper()) {
            $this->setChild('attribute_view', $optionList);
        } else {
            $attributeBlock = $this->getLayout()
                ->createBlock('attributepages/attribute_view', null, $this->getData())
                ->setTemplate($this->getAttributeBlockTemplate());
            $attributeBlock->setChild('children_list', $optionList);
            $this->setChild('attribute_view', $attributeBlock);
        }

        return parent::_prepareLayout();
    }

    public function getOptionListTemplate()
    {
        $key = 'option_list_template';
        $template = $this->_getData($key);
        if (null === $template) {
            if ($this->getIsSlider()) {
                $template = 'tm/attributepages/option/slider.phtml';
            } else {
                $template = 'tm/attributepages/option/list.phtml';
            }
            $this->setData($key, $template);
        }
        return $template;
    }

    public function getAttributeBlockTemplate()
    {
        $key = 'attribute_block_template';
        $template = $this->_getData($key);
        if (null === $template) {
            $template = 'tm/attributepages/attribute/block.phtml';
            $this->setData($key, $template);
        }
        return $template;
    }

    protected function _toHtml()
    {
        return $this->getChildHtml('attribute_view');
    }
}