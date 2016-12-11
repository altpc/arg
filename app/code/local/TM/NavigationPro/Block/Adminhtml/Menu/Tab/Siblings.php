<?php

class TM_NavigationPro_Block_Adminhtml_Menu_Tab_Siblings extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        //$this->setShowGlobalIcon(true);
        $this->setTemplate('tm/navigationpro/menu/edit/siblings.phtml');
    }

    public function _prepareLayout()
    {
         $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('navigationpro')->__('Add New Sibling'),
                    'class' => 'add',
                    'id'    => 'add_new_sibling'
                ))
        );

        $this->setChild('siblings',
            $this->getLayout()->createBlock('navigationpro/adminhtml_menu_tab_siblings_sibling')
        );

        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getSiblingsHtml()
    {
        return $this->getChildHtml('siblings');
    }

    public function getCategory()
    {
        return Mage::registry('category');
    }

    public function getMenu()
    {
        return Mage::registry('menu');
    }

    public function getRootMenu()
    {
        if (!$rootMenu = Mage::registry('root_menu')) {
            $rootMenu = $this->getMenu();
        }
        return $rootMenu;
    }
}
