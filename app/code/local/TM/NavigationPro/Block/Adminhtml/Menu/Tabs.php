<?php

class TM_NavigationPro_Block_Adminhtml_Menu_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('menu_info_tabs');
        $this->setDestElementId('menu_tab_content');
        $this->setTitle(Mage::helper('navigationpro')->__('Menu Data'));
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    public function getMenu()
    {
        return Mage::registry('menu');
    }

    public function getRootMenu()
    {
        return Mage::registry('root_menu');
    }

    /**
     * Retrieve cattegory object
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory()
    {
        return Mage::registry('current_category');
    }


    /**
     * Prepare Layout Content
     *
     * @return Mage_Adminhtml_Block_Catalog_Category_Tabs
     */
    protected function _prepareLayout()
    {
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        $this->addTab('settings', array(
            'label'   => Mage::helper('catalog')->__('General Information'),
            'content' => $this->getLayout()->createBlock(
                'navigationpro/adminhtml_menu_tab_general',
                'menu.general'
            )->toHtml()
        ));

        if ($this->getMenu()->isRoot() || ($this->getCategory() && ($this->getCategory()->getLevel() > 1))) {
            $this->addTab('siblings', array(
                'label'     => Mage::helper('navigationpro')->__('Siblings'),
                'content'   => $this->getLayout()->createBlock(
                    'navigationpro/adminhtml_menu_tab_siblings',
                    'menu.siblings'
                )->toHtml()
            ));
            $this->addTab('content', array(
                'label'   => Mage::helper('navigationpro')->__('Dropdown Content'),
                'content' => $this->getLayout()->createBlock(
                    'navigationpro/adminhtml_menu_tab_content',
                    'menu.content'
                )->toHtml()
            ));
        }

        if (!$this->getCategory() || ($this->getCategory()->getLevel() > 1)) {
            if ($this->getMenu()->isRoot()) {
                $label = Mage::helper('navigationpro')->__('Submenu global preferences');
            } else {
                $label = Mage::helper('navigationpro')->__('Dropdown columns');
            }
            $this->addTab('columns', array(
                'label'   => $label,
                'content' => $this->getLayout()->createBlock(
                    'navigationpro/adminhtml_menu_tab_columns',
                    'menu.columns'
                )->toHtml()
            ));
        }
        return parent::_prepareLayout();
    }
}
