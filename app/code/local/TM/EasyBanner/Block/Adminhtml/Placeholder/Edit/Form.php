<?php

class TM_EasyBanner_Block_Adminhtml_Placeholder_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('easybanner_placeholder');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setHtmlIdPrefix('placeholder_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('easybanner')->__('General Information'), 'class' => 'fieldset-wide'));

        if ($model->getPlaceholderId()) {
            $fieldset->addField('placeholder_id', 'hidden', array(
                'name' => 'placeholder_id',
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => Mage::helper('easybanner')->__('Placeholder Name'),
            'title'     => Mage::helper('easybanner')->__('Placeholder Name'),
            'required'  => true,
            'class'     => 'validate-xml-identifier'
        ));

        $fieldset->addField('parent_block', 'text', array(
            'name'      => 'parent_block',
            'label'     => Mage::helper('easybanner')->__('Parent Block'),
            'title'     => Mage::helper('easybanner')->__('Parent Block'),
            'required'  => true
        ));

        $fieldset->addField('position', 'text', array(
            'name'      => 'position',
            'label'     => Mage::helper('easybanner')->__('Position'),
            'title'     => Mage::helper('easybanner')->__('Position')
        ));

        $fieldset->addField('limit', 'text', array(
            'name'      => 'limit',
            'label'     => Mage::helper('easybanner')->__('Banners limit per rotate'),
            'title'     => Mage::helper('easybanner')->__('Banners limit per rotate'),
            'required'  => true
        ));

        $fieldset->addField('sort_mode', 'select', array(
            'name'      => 'sort_mode',
            'label'     => Mage::helper('easybanner')->__('Sort mode'),
            'title'     => Mage::helper('easybanner')->__('Sort mode'),
            'required'  => true,
            'options'   => array(
                'sort_order' => Mage::helper('easybanner')->__('By Banners Sort Order'),
                'random'     => Mage::helper('easybanner')->__('Random')
            )
        ));

        /*$fieldset->addField('mode', 'select', array(
            'name'      => 'mode',
            'label'     => Mage::helper('easybanner')->__('Mode'),
            'title'     => Mage::helper('easybanner')->__('Mode'),
            'required'  => true,
            'options'   => array(
                //'slider' => Mage::helper('easybanner')->__('Slider'),
                'rotator' => Mage::helper('easybanner')->__('Rotator')
            )
        ));*/

        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('easybanner')->__('Status'),
            'title'     => Mage::helper('easybanner')->__('Status'),
            'name'      => 'status',
            'required'  => true,
            'options'   => array(
                '1' => Mage::helper('easybanner')->__('Enabled'),
                '0' => Mage::helper('easybanner')->__('Disabled')
            )
        ));

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}