<?php
class TM_EasyBanner_Block_Adminhtml_Banner_Edit_Tab_Statistics_Diagrams
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('diagram_tab');
        $this->setDestElementId('banner_tabs_statistics_section_content');
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    protected function _prepareLayout()
    {
        $this->addTab('display', array(
            'label'     => $this->__('Display'),
            'content'   => $this->getLayout()->createBlock('easybanner/adminhtml_banner_edit_tab_statistics_tab_display')->toHtml(),
            'active'    => true
        ));

        $this->addTab('click', array(
            'label'     => $this->__('Clicks'),
            'content'   => $this->getLayout()->createBlock('easybanner/adminhtml_banner_edit_tab_statistics_tab_click')->toHtml(),
        ));
        return parent::_prepareLayout();
    }
}