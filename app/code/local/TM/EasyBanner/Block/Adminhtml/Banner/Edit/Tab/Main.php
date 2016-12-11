<?php

class TM_EasyBanner_Block_Adminhtml_Banner_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('easybanner_banner');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('banner_');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('easybanner')->__('General Information')
        ));

        if ($model->getBannerId()) {
            $fieldset->addField('banner_id', 'hidden', array(
                'name' => 'banner_id',
            ));
        }

        $fieldset->addField('identifier', 'text', array(
            'name'     => 'identifier',
            'label'    => Mage::helper('easybanner')->__('Name'),
            'title'    => Mage::helper('easybanner')->__('Name'),
            'required' => true,
            'class'    => 'validate-xml-identifier'
        ));

        $fieldset->addField('hide_url', 'select', array(
            'label'   => Mage::helper('easybanner')->__('Hide Url'),
            'title'   => Mage::helper('easybanner')->__('Hide Url'),
            'name'    => 'hide_url',
            'options' => array(
                '1' => Mage::helper('easybanner')->__('Yes'),
                '0' => Mage::helper('easybanner')->__('No')
            )
        ));

        $fieldset->addField('target', 'select', array(
            'label'   => Mage::helper('easybanner')->__('Target'),
            'title'   => Mage::helper('easybanner')->__('Target'),
            'name'    => 'target',
            'options' => array(
                'popup' => Mage::helper('easybanner')->__('Popup'),
                'blank' => Mage::helper('easybanner')->__('Blank'),
                'self'  => Mage::helper('easybanner')->__('Self')
            )
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name'  => 'sort_order',
            'label' => Mage::helper('easybanner')->__('Sort order'),
            'title' => Mage::helper('easybanner')->__('Sort order')
        ));

        $fieldset->addField('store_ids', 'multiselect', array(
            'name'     => 'store_ids',
            'label'    => Mage::helper('easybanner')->__('Store View'),
            'title'    => Mage::helper('easybanner')->__('Store View'),
            'required' => true,
            'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
        ));
        if (!$model->getStoreIds()) {
            $model->setStoreIds(array(0));
        }

        $fieldset->addField('placeholder_ids', 'multiselect', array(
            'name'   => 'placeholder_ids',
            'label'  => Mage::helper('easybanner')->__('Placeholder'),
            'title'  => Mage::helper('easybanner')->__('Placeholder'),
            'values' => Mage::getModel('easybanner/placeholder')->getCollection()->toOptionArray(),
            'value'  => $model->getPlaceholderIds()
        ));

        $fieldset->addField('status', 'select', array(
            'label'   => Mage::helper('easybanner')->__('Status'),
            'title'   => Mage::helper('easybanner')->__('Status'),
            'name'    => 'status',
            'options' => array(
                '1' => Mage::helper('easybanner')->__('Enabled'),
                '0' => Mage::helper('easybanner')->__('Disabled')
            )
        ));
        if (!$model->getId()) {
            $model->setStatus(1);
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
