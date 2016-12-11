<?php

class TM_EasyBanner_Block_Adminhtml_Placeholder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('placeholderGrid');
        $this->setDefaultSort('placeholder_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('placeholder_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('easybanner/placeholder')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('placeholder_id', array(
            'header'    => Mage::helper('easybanner')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'placeholder_id',
            'type'      => 'number'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('easybanner')->__('Name'),
            'align'     =>'left',
            'index'     => 'name'
        ));

        $this->addColumn('parent_block', array(
            'header'    => Mage::helper('easybanner')->__('Parent Block'),
            'align'     => 'left',
            'width'     => '200px',
            'index'     => 'parent_block'
        ));

        $this->addColumn('position', array(
            'header'    => Mage::helper('easybanner')->__('Position'),
            'align'     => 'left',
            'width'     => '180px',
            'index'     => 'position'
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('easybanner')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                1 => 'Enabled',
                0 => 'Disabled'
            )
        ));

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