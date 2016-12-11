<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Block_Adminhtml_Rules_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('rules_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('prolabels')->__('Label Information'));
    }
    
    protected function _beforeToHtml()
    {
        $this->addTab('main_section', array(
            'label'     => Mage::helper('prolabels')->__('General'),
            'title'     => Mage::helper('prolabels')->__('General'),
            'content'   => $this->getLayout()->createBlock('prolabels/adminhtml_rules_edit_tab_main')->toHtml(),
            'active'    => true
        ));
        
        $this->addTab('content_p_section', array(
            'label'     => Mage::helper('prolabels')->__('Content (Product Page)'),
            'title'     => Mage::helper('prolabels')->__('Content (Product Page)'),
            'content'   => $this->getLayout()->createBlock('prolabels/adminhtml_rules_edit_tab_content')->toHtml()
        ));
        
        $this->addTab('content_c_section', array(
            'label'     => Mage::helper('prolabels')->__('Content (Category Page)'),
            'title'     => Mage::helper('prolabels')->__('Content (Category Page)'),
            'content'   => $this->getLayout()->createBlock('prolabels/adminhtml_rules_edit_tab_categoryContent')->toHtml()
        ));

        $this->addTab('conditions_section', array(
            'label'     => Mage::helper('prolabels')->__('Conditions'),
            'title'     => Mage::helper('prolabels')->__('Conditions'),
            'content'   => $this->getLayout()->createBlock('prolabels/adminhtml_rules_edit_tab_conditions')->toHtml()
        ));
        
        $this->addTab('products_section', array(
            'label'     => Mage::helper('prolabels')->__('Indexed Products'),
            'title'     => Mage::helper('prolabels')->__('Indexed Products'),
            'content'   => $this->getLayout()->createBlock('prolabels/adminhtml_rules_edit_tab_products')->toHtml()
        ));
        
        return parent::_beforeToHtml();
    }

}
