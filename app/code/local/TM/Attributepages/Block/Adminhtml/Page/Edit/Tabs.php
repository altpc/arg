<?php

class TM_Attributepages_Block_Adminhtml_Page_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');

        $title = Mage::registry('attributepages_page')->isAttributeBasedPage() ?
            Mage::helper('cms')->__('Page Information')
                : Mage::helper('attributepages')->__('Option Information');
        $this->setTitle($title);
    }
}
