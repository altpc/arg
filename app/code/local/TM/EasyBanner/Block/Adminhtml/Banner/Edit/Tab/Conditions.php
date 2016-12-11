<?php

class TM_EasyBanner_Block_Adminhtml_Banner_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('easybanner_banner');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('banner_');

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('tm/easybanner/banner/filters.phtml')
            ->setNewChildUrl($this->getUrl('*/*/newConditionHtml/form/banner_conditions_fieldset'));

        $fieldset = $form->addFieldset('conditions_fieldset', array(
            'legend'=>Mage::helper('easybanner')->__('Conditions (leave blank to show on all pages)'))
        )->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('easybanner')->__('Conditions'),
            'title' => Mage::helper('easybanner')->__('Conditions'),
            'required' => true,
        ))->setRule($model)
        ->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
