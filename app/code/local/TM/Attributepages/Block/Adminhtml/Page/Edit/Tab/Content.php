<?php

class TM_Attributepages_Block_Adminhtml_Page_Edit_Tab_Content
    extends TM_Attributepages_Block_Adminhtml_Page_Edit_Tab_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    protected function _prepareForm()
    {
        $model = $this->getPage();
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('cms')->__('Content'),
            'class'  => 'fieldset-wide'
        ));

        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $fieldset->addField('page_title', 'text', array(
            'name'     => 'page_title',
            'label'    => Mage::helper('cms')->__('Page Title'),
            'title'    => Mage::helper('cms')->__('Page Title'),
            'disabled' => $isElementDisabled
        ));

        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
            array('tab_id' => $this->getTabId())
        );
        $contentField = $fieldset->addField('content', 'editor', array(
            'label'    => Mage::helper('cms')->__('Description'),
            'title'    => Mage::helper('cms')->__('Description'),
            'name'     => 'content',
            'style'    => 'height:15em;',
            'disabled' => $isElementDisabled,
            'config'   => $wysiwygConfig
        ));

        $fieldset->addField('meta_keywords', 'textarea', array(
            'name'     => 'meta_keywords',
            'label'    => Mage::helper('cms')->__('Meta Keywords'),
            'title'    => Mage::helper('cms')->__('Meta Keywords'),
            'disabled' => $isElementDisabled
        ));
        $fieldset->addField('meta_description', 'textarea', array(
            'name'     => 'meta_description',
            'label'    => Mage::helper('cms')->__('Meta Description'),
            'title'    => Mage::helper('cms')->__('Meta Description'),
            'disabled' => $isElementDisabled
        ));

        $form->addValues($model->getData());
        $form->setFieldNameSuffix('attributepage');
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('cms')->__('Content');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('cms')->__('Content');
    }
}
