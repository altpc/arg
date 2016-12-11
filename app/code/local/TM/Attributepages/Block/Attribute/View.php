<?php

class TM_Attributepages_Block_Attribute_View extends TM_Attributepages_Block_Abstract
{
    protected function _beforeToHtml()
    {
        $list = $this->getChild('children_list');
        if ($list) {
            $list->setCurrentPage($this->getCurrentPage());
        }
        return parent::_beforeToHtml();
    }

    public function getPageDescription()
    {
        $helper    = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();
        $html      = $processor->filter($this->getCurrentPage()->getContent());
        return $html;
    }

    public function getHideDescriptionWhenFilterIsUsed()
    {
        $section = $this->getCurrentPage()->isAttributeBasedPage() ? 'option_list' : 'product_list';
        return Mage::getStoreConfigFlag("attributepages/{$section}/hide_description_when_filter_is_used");
    }

    public function canShowDescription()
    {
        $page = $this->getCurrentPage();
        if ($page->isChildrenMode()) {
            return false;
        }
        $hasContent = (bool)$page->getContent();
        if (!$hasContent) {
            return false;
        }

        /**
         * don't show the block:
         *  if pagination is used
         *  if filter is applied
         */
        $page = (int) $this->getRequest()->getParam('p', 1);
        if ($this->getHideDescriptionWhenFilterIsUsed()
            && ($page > 1
                || Mage::getSingleton('catalog/layer')->getState()->getFilters())
        ) {
            return false;
        }
        return $hasContent;
    }

    public function canShowChildren()
    {
        return !(bool)$this->getCurrentPage()->isDescriptionMode();
    }
}
