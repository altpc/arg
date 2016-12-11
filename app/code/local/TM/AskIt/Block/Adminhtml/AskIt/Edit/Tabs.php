<?php
class TM_AskIt_Block_Adminhtml_AskIt_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Load Wysiwyg on demand and Prepare layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    public function __construct()
    {
        parent::__construct();
        $this->setId('ask_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('askit')->__('Ask Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('askit')->__('General'),
            'title'     => Mage::helper('askit')->__('General'),
            'content'   => $this->getLayout()->createBlock('askit/adminhtml_askIt_edit_tab_form')->toHtml(),
        ));

        $this->addTab('childs_section', array(
            'label'     => Mage::helper('askit')->__('Answers'),
            'title'     => Mage::helper('askit')->__('Answers'),
            'content'   =>
               $this->getLayout()->createBlock('askit/adminhtml_askIt_edit_tab_addAnswers')->toHtml() .
               $this->getLayout()->createBlock('askit/adminhtml_askIt_edit_tab_grid')->toHtml(),
        ));

        $this->addTab('assign_product_section', array(
            'label'     => Mage::helper('askit')->__('Assigned Products'),
            'url'       => $this->getUrl('*/*/assignproducts', array('_current' => true)),
            'class'     => 'ajax',
        ));

        // $this->addTab('assign_pro_section', array(
        //     'label'     => Mage::helper('askit')->__('All Assigned Products'),
        //     'title'     => Mage::helper('askit')->__('All Assigned Products'),
        //     'content'   =>
        //        $this->getLayout()->createBlock('askit/adminhtml_askIt_edit_tab_assign_products')->toHtml(),
        // ));
        //
        $this->addTab('assign_pages_section', array(
            'label'     => Mage::helper('askit')->__('Assigned Cms pages'),
            'url'       => $this->getUrl('*/*/assignpages', array('_current' => true)),
            'class'     => 'ajax',
        ));

        $this->addTab('assign_cats_section', array(
            'label'     => Mage::helper('askit')->__('Assigned Categories'),
            'url'       => $this->getUrl('*/*/assigncats', array('_current' => true)),
            'class'     => 'ajax',
        ));

        return parent::_beforeToHtml();
    }
}
