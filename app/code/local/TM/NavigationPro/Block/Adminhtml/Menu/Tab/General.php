<?php

class TM_NavigationPro_Block_Adminhtml_Menu_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setShowGlobalIcon(true);
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
        return Mage::registry('root_menu');
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $form = new Varien_Data_Form();
        $form->setDataObject($this->getCategory());

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('catalog')->__('General Information')));

        if ($this->getMenu()->getId()) {
            $fieldset->addField('menu_id', 'hidden', array(
                'label' => 'menu_id',
                'name'  => 'menu_id',
                'value' => $this->getMenu()->getId()
            ));
        }

        if ($this->getCategory()) {
            $fieldset->addField('category_id', 'hidden', array(
                'label' => 'category_id',
                'name'  => 'category_id',
                'value' => $this->getCategory() ? $this->getCategory()->getId() : ''
            ));
            if ($this->getRootMenu()->getId()) {
                $fieldset->addField('root_menu_id', 'hidden', array(
                    'label' => 'root_menu_id',
                    'name'  => 'root_menu_id',
                    'value' => $this->getRootMenu()->getId()
                ));
            }
        } else {
            $fieldset->addField('name', 'text', array(
                'label'    => 'Name',
                'name'     => 'name',
                'value'    => $this->getMenu()->getName(),
                'required' => true,
                'class'    => 'validate-xml-identifier'
            ));
        }

        $fieldset->addField('is_active', 'select', array(
            'label'  => Mage::helper('cms')->__('Status'),
            'title'  => Mage::helper('cms')->__('Status'),
            'name'   => 'is_active',
            'value'  => 1,
            'values' => array(
                '1' => Mage::helper('cms')->__('Enabled'),
                '0' => Mage::helper('cms')->__('Disabled')
            )
        ));

        if ($this->getMenu()->isRoot()) {
            $fieldset->addField('display_in_navigation', 'hidden', array(
                'label' => Mage::helper('navigationpro')->__('Display in navigation'),
                'title' => Mage::helper('navigationpro')->__('Display in navigation'),
                'name'  => 'display_in_navigation',
                'onchange' => "syncTabsWithRootMenu();", // see tm/navigationpro/menu/edit/form.phtml
                'value' => 0,
//                'values' => Mage::getModel('adminhtml/system_config_source_yesno')
//                    ->toArray(),
                'note' => Mage::helper('navigationpro')->__("Useful, if you want to make 'Catalog' menu item with list of all categories in dropdown.")
            ));

            $topLevelFieldset = $form->addFieldset('top_level_fieldset', array(
                'legend' => Mage::helper('navigationpro')->__("Top Level Categories Options (See the 'Submenu global preferences' tab to setup nested levels)"))
            );
            $topLevelFieldset->addField('levels_per_dropdown', 'text', array(
                'label'    =>  Mage::helper('navigationpro')->__('Levels Per Dropdown'),
                'name'     =>  Mage::helper('navigationpro')->__('levels_per_dropdown'),
                'value'    => 1,
                'required' => true,
                'note' => Mage::helper('navigationpro')->__("Set this value to max level count, to use menu as fully expanded menu without dropdowns (100 - whould be enough)")
            ));
            $topLevelFieldset->addField('style', 'select', array(
                'label' => Mage::helper('navigationpro')->__('Style'),
                'title' => Mage::helper('navigationpro')->__('Style'),
                'name'  => 'style',
                'value' => 'dropdown',
                'values' => Mage::getModel('navigationpro/adminhtml_system_config_source_style')
                    ->toOptionArray()
            ));
        }

        $form->addValues($this->getMenu()->getData());

        $form->setFieldNameSuffix('general');
        $this->setForm($form);
    }
}

