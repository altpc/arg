<?php

class TM_EasyBanner_Block_Adminhtml_Banner_Statistics_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('statisticsGrid');
        $this->setDefaultSort('banner_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('banner_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('easybanner/banner')->getCollection()
            ->joinLeft('banner_placeholder',
                'banner_placeholder.banner_id = main_table.banner_id',
                '')
            ->joinLeft('placeholder',
                'placeholder.placeholder_id = banner_placeholder.placeholder_id',
                array('placeholder' => 'placeholder.name'));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}