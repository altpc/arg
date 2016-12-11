<?php

class TM_EasyBanner_Block_Adminhtml_Banner_Edit_Tab_Content extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('easybanner_banner');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('banner_');

        $fieldset = $form->addFieldset('content_fieldset', array(
            'legend' => Mage::helper('easybanner')->__('General Information')
        ));
        $fieldset->addField('url', 'text', array(
            'name'      => 'url',
            'label'     => Mage::helper('easybanner')->__('Url'),
            'title'     => Mage::helper('easybanner')->__('Url'),
            'required'  => true
        ));
        $fieldset->addField('class_name', 'text', array(
            'name'  => 'class_name',
            'label' => Mage::helper('easybanner')->__('Css Class'),
            'title' => Mage::helper('easybanner')->__('Css Class')
        ));
        $fieldset->addField('mode', 'select', array(
            'label'     => Mage::helper('easybanner')->__('Mode'),
            'title'     => Mage::helper('easybanner')->__('Mode'),
            'name'      => 'mode',
            'options'   => array(
                'image' => Mage::helper('easybanner')->__('Image'),
                'html'  => Mage::helper('easybanner')->__('Html')
            ),
            'required'  => true
        ));

        $fieldset = $form->addFieldset('image_fieldset', array(
            'legend' => Mage::helper('easybanner')->__('Image Options')
        ));
        $this->_addElementTypes($fieldset); //register own image element
        $fieldset->addField('title', 'text', array(
            'name'  => 'title',
            'label' => Mage::helper('easybanner')->__('Title'),
            'title' => Mage::helper('easybanner')->__('Title')
        ));
        $fieldset->addField('image', 'image', array(
            'name'  => 'image',
            'label' => Mage::helper('easybanner')->__('Image'),
            'title' => Mage::helper('easybanner')->__('Image')
        ));
        $fieldset->addField('width', 'text', array(
            'name'  => 'width',
            'label' => Mage::helper('sales')->__('Width'),
            'title' => Mage::helper('sales')->__('Width')
        ));
        $fieldset->addField('height', 'text', array(
            'name'  => 'height',
            'label' => Mage::helper('sales')->__('Height'),
            'title' => Mage::helper('sales')->__('Height')
        ));
        $yesno = Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray();
        $fieldset->addField('resize_image', 'select', array(
            'name'   => 'resize_image',
            'label'  => Mage::helper('easybanner')->__('Use image resizer'),
            'title'  => Mage::helper('easybanner')->__('Use image resizer'),
            'values' => $yesno
        ));
        $fieldset->addField('retina_support', 'select', array(
            'name'    => 'retina_support',
            'note'    => Mage::helper('easybanner')->__('Actual image size should be twice larger then entered width and height'),
            'label'   => Mage::helper('easybanner')->__('Retina support'),
            'title'   => Mage::helper('easybanner')->__('Retina support'),
            'values'  => $yesno
        ));
        $fieldset->addField('background_color', 'text', array(
            'name'  => 'background_color',
            'label' => Mage::helper('easybanner')->__('Background color'),
            'title' => Mage::helper('easybanner')->__('Background color')
        ));
        if (!$model->getBackgroundColor()) {
            $model->setBackgroundColor('255,255,255');
        }


        $fieldset = $form->addFieldset('html_fieldset', array(
            'legend' => Mage::helper('easybanner')->__('Html Content'),
            'class'  => 'fieldset-wide'
        ));
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array(
            'tab_id'                   => $this->getTabId(),
            'add_variables'            => true,
            'add_widgets'              => true,
            'files_browser_window_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
            'directives_url'           => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive')
        ));
        $fieldset->addField('html', 'editor', array(
            'name'   => 'html',
            'label'  => Mage::helper('easybanner')->__('Content'),
            'title'  => Mage::helper('easybanner')->__('Content'),
            'config' => $wysiwygConfig
        ));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getAdditionalElementTypes()
    {
        return array(
            'image' => Mage::getConfig()->getBlockClassName('easybanner/adminhtml_banner_helper_image')
        );
    }

}
