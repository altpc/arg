<?php

class TM_Attributepages_Block_Adminhtml_Page_Edit_Tab_Main
    extends TM_Attributepages_Block_Adminhtml_Page_Edit_Tab_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $model = $this->getPage();
        $form  = new Varien_Data_Form();
        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $this->getTabLabel()
        ));

        if ($model->getEntityId()) {
            $fieldset->addField('entity_id', 'hidden', array(
                'name' => 'entity_id'
            ));
        }

        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $fieldset->addField('attribute_id', 'hidden', array(
            'required' => true,
            'name'     => 'attribute_id'
        ));
        if ($model->isOptionBasedPage()) {
            $fieldset->addField('option_id', 'hidden', array(
                'required' => true,
                'name'     => 'option_id'
            ));
        }
        $fieldset->addField('name', 'text', array(
            'name'     => 'name',
            'label'    => Mage::helper('catalog')->__('Name'),
            'title'    => Mage::helper('catalog')->__('Name'),
            'note'     => Mage::helper('attributepages')->__('Used to identify page in backend grid'),
            'required' => true,
            'disabled' => $isElementDisabled
        ));

        $fieldset->addField('title', 'text', array(
            'name'     => 'title',
            'label'    => Mage::helper('cms')->__('Title'),
            'title'    => Mage::helper('cms')->__('Title'),
            'disabled' => $isElementDisabled
        ));
        if (!$model->getId()) {
            $model->setData('title', $this->getDefaultPageTitle());
        }

        $fieldset->addField('identifier', 'text', array(
            'name'     => 'identifier',
            'label'    => Mage::helper('cms')->__('URL Key'),
            'title'    => Mage::helper('cms')->__('URL Key'),
            'required' => true,
            'note'     => Mage::helper('cms')->__('Relative to Website Base URL'),
            'disabled' => $isElementDisabled
        ));
        if (!$model->getId()) {
            $model->setData('identifier', $this->getDefaultPageIdentifier());
        }

        if ($model->isOptionBasedPage()) {
            $this->_addElementTypes($fieldset); //register own image element
            $fieldset->addField('image', 'image', array(
                'name'     => 'image',
                'label'    => Mage::helper('catalog')->__('Image'),
                'title'    => Mage::helper('catalog')->__('Image'),
                'disabled' => $isElementDisabled
            ));
            $fieldset->addField('thumbnail', 'image', array(
                'name'     => 'thumbnail',
                'label'    => Mage::helper('widget')->__('Thumbnail'),
                'title'    => Mage::helper('widget')->__('Thumbnail'),
                'disabled' => $isElementDisabled
            ));
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'multiselect', array(
                'name'     => 'stores[]',
                'label'    => Mage::helper('cms')->__('Store View'),
                'title'    => Mage::helper('cms')->__('Store View'),
                'required' => true,
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
                'disabled' => $isElementDisabled
            ));
            // $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            // $field->setRenderer($renderer);
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'  => 'stores[]',
                'value' => Mage_Core_Model_App::ADMIN_STORE_ID
            ));
            $model->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
        }

        $fieldset->addField('use_for_attribute_page', 'select', array(
            'label'  => Mage::helper('catalog')->__('Enabled'),
            'title'  => Mage::helper('catalog')->__('Enabled'),
            'name'   => 'use_for_attribute_page',
            'values' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No')
            ),
            'disabled' => $isElementDisabled
        ));
        if (!$model->getId()) {
            $model->setData('use_for_attribute_page', '1');
        }

        $form->addValues($model->getData());
        $form->setFieldNameSuffix('attributepage');
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _getAdditionalElementTypes()
    {
        return array(
            'image' => Mage::getConfig()->getBlockClassName('attributepages/adminhtml_page_helper_image')
        );
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        if ($this->getPage()->isAttributeBasedPage()) {
            return Mage::helper('cms')->__('Page Information');
        } else {
            return Mage::helper('attributepages')->__('Option Information');
        }
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        if ($this->getPage()->isAttributeBasedPage()) {
            return Mage::helper('cms')->__('Page Information');
        } else {
            return Mage::helper('attributepages')->__('Option Information');
        }
    }

    public function getDefaultPageTitle()
    {
        if ($this->getPage()->isAttributeBasedPage()) {
            return $this->getPage()->getAttribute()->getFrontendLabel();
        } else {
            return $this->getPage()->getOption()->getValue();
        }
    }

    public function getDefaultPageIdentifier()
    {
        if ($this->getPage()->isAttributeBasedPage()) {
            return $this->getPage()->getAttribute()->getAttributeCode();
        } else {
            return $this->getPage()->getOption()->getValue();
        }
    }
}
