<?php

class TM_NavigationPro_Block_Adminhtml_Menu_Tab_Content extends Mage_Adminhtml_Block_Widget_Form
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
        if (!$rootMenu = Mage::registry('root_menu')) {
            $rootMenu = $this->getMenu();
        }
        return $rootMenu;
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();

        $form = new Varien_Data_Form();
        $form->setDataObject($this->getMenu());

        /**
         * @var Varien_Data_Form_Element_Fieldset
         */
        $fieldset = $form->addFieldset('content_fieldset', array(
            'legend' => Mage::helper('navigationpro')->__('Dropdown Content'),
            'class'  => 'fieldset-wide'
        ));
        $fieldset->addType(
            'wysiwyg_popup',
            Mage::getConfig()->getBlockClassName('tmcore/adminhtml_widget_form_element_wysiwyg')
        );

        $content = $this->getMenu()->getContent();
        $useDefaults = false;

        if ($this->getMenu()->getStoreId()) {
            if (!$content || !$content['store_id']) {
                $useDefaults = true;
            }
            $fieldset->addField('use_default', 'checkbox', array(
                'label'  => Mage::helper('adminhtml')->__('Use Default Value'),
                'title'  => Mage::helper('adminhtml')->__('Use Default Value'),
                'name'   => 'use_default',
                'onclick' => "toggleValueElements(this, $(this).up('table'))",
                'checked' => $useDefaults
            ));
        }

        if ($this->getMenu()->isRoot()) {
            $fieldset->addField('title', 'wysiwyg_popup', array(
                'label'  => Mage::helper('catalog')->__('Title'),
                'title'  => Mage::helper('catalog')->__('Title'),
                'style'  => 'height:17px',
                'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
                'name'   => 'title',
                'disabled' => $useDefaults,
                'value' => Mage::helper('catalog')->__('Catalog')
            ));
        }

        $fieldset->addField('top', 'wysiwyg_popup', array(
            'label'  => Mage::helper('navigationpro')->__('Dropdown top content'),
            'title'  => Mage::helper('navigationpro')->__('Dropdown top content'),
            'style'  => 'height:15em',
            'name'   => 'top',
            'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
            'disabled' => $useDefaults
        ));

        $fieldset->addField('bottom', 'wysiwyg_popup', array(
            'label'  => Mage::helper('navigationpro')->__('Dropdown bottom content'),
            'title'  => Mage::helper('navigationpro')->__('Dropdown bottom content'),
            'style'  => 'height:15em',
            'name'   => 'bottom',
            'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
            'disabled' => $useDefaults
        ));

        $form->addValues($content);

        $form->setFieldNameSuffix('content');
        $this->setForm($form);
    }
}
