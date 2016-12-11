<?php

class TM_EasyBanner_Block_Adminhtml_Banner_Edit_Tab_Statistics extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('easybanner_banner');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('banner_');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('easybanner')->__('Statistics'),
            'class'     => 'fieldset-wide'
        ));

        $fieldset->addField('display_count', 'text', array(
            'name'      => 'display_count',
            'label'     => Mage::helper('easybanner')->__('Display Count'),
            'title'     => Mage::helper('easybanner')->__('Display Count'),
            'value'     => $model->getDisplayCount(),
            'readonly'  => true,
            'disabled'  => true
        ));

        $fieldset->addField('clicks_count', 'text', array(
            'name'      => 'clicks_count',
            'label'     => Mage::helper('easybanner')->__('Clicks Count'),
            'title'     => Mage::helper('easybanner')->__('Clicks Count'),
            'value'     => $model->getClicksCount(),
            'readonly'  => true,
            'disabled'  => true
        ));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
