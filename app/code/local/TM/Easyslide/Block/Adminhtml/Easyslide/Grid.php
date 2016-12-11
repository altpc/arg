<?php

class TM_Easyslide_Block_Adminhtml_Easyslide_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('easyslideGrid');
        $this->setDefaultSort('easyslide_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
 
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('easyslide/easyslide')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
        $this->addColumn('easyslide_id', array(
            'header'    => Mage::helper('easyslide')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'easyslide_id'
        ));
 
        $this->addColumn('title', array(
            'header'    => Mage::helper('easyslide')->__('Title'),
            'align'     =>'left',
            'index'     => 'title'
        ));

        $this->addColumn('created_time', array(
            'header'    => Mage::helper('easyslide')->__('Created Time'),
            'align'     => 'left',
            'width'     => '160px',
            'type'      => 'datetime',
            'default'   => '--',
            'index'     => 'created_time'
        ));
        
        $this->addColumn('modified_time', array(
            'header'    => Mage::helper('easyslide')->__('Modified Time'),
            'align'     => 'left',
            'width'     => '160px',
            'type'      => 'datetime',
            'default'   => '--',
            'index'     => 'modified_time'
        ));
        $this->addColumn('slider_type', array(
            'header'    => Mage::helper('easyslide')->__('Slider Type'),
            'align'     => 'left',
            'width'     => '150px',
            'index'     => 'slider_type',
            'type'      => 'options',
            'options'   => array(
                1 => 'Nivo Slider (jQuery)',
                0 => 'Prototype Slider'
            )
        ));
        $this->addColumn('status', array(
            'header'    => Mage::helper('easyslide')->__('Status'),
            'align'     => 'left',
            'width'     => '100px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                1 => 'Enabled',
                0 => 'Disabled'
            )
        ));
 
        return parent::_prepareColumns();
    }
 
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}