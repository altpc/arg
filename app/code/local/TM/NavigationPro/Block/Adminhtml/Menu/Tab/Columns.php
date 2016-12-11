<?php

class TM_NavigationPro_Block_Adminhtml_Menu_Tab_Columns extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        //$this->setShowGlobalIcon(true);
        $this->setTemplate('tm/navigationpro/menu/edit/columns.phtml');
    }

    public function _prepareLayout()
    {
         $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('navigationpro')->__('Add New Column'),
                    'class' => 'add',
                    'id'    => 'add_new_column'
                ))
        );

        $this->setChild('columns',
            $this->getLayout()->createBlock('navigationpro/adminhtml_menu_tab_columns_column')
        );

        return parent::_prepareLayout();
    }

    public function getColumnsModeSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id'    => 'columns_mode',
                'class' => 'select'
            ))
            ->setName('general[columns_mode]')
            ->setValue($this->getMenu()->getColumnsMode())
            ->setOptions(
                Mage::getModel('navigationpro/adminhtml_system_config_source_columnsMode')
                    ->toOptionArray()
            );

        return $select->getHtml();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getColumnsHtml()
    {
        return $this->getChildHtml('columns');
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

