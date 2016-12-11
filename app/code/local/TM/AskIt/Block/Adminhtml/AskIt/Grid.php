<?php

class TM_AskIt_Block_Adminhtml_AskIt_Grid extends TM_AskIt_Block_Adminhtml_AskIt_Grid_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('askItGrid_question');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');

        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('askit/message')->getCollection();
        $collection->addQuestionFilter();
        $collection->addQuestionCountAnswersData();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
          'header'    => Mage::helper('askit')->__('ID'),
          'align'     => 'right',
          'width'     => '50px',
          'index'     => 'id',
          'type'      => 'number'
        ));
        $this->addColumn('text', array(
          'header'    => Mage::helper('askit')->__('Question'),
          'align'     => 'left',
          'index'     => 'text',
        ));

        $this->addColumn('item_type_id', array(
            'header'    => Mage::helper('askit')->__('Item Type'),
            'type'      => 'options',
            'index'     => 'item_type_id',
            'options'   => Mage::getSingleton('askit/item_type')->toOptionHash(),
        ));

        $this->addColumn('item_name', array(
            'header'    => Mage::helper('askit')->__('Item'),
            'index'     => 'item_id',
            'width'     => '100px',
            'frame_callback' => array($this, 'decorateItemName'),
            'filter_condition_callback' => array($this, '_filterItemNameCondition'),
        ));

        $this->addColumn('customer', array(
            'header'    => Mage::helper('askit')->__('Customer'),
            'index'     => 'customer_name'
        ));
        $this->addColumn('email', array(
            'header'    => Mage::helper('askit')->__('Email'),
            'index'     => 'email'
        ));

        $this->addColumn('hint', array(
            'header'    => Mage::helper('askit')->__('Votes'),
            'align'     =>'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'hint',
        ));
        $this->addColumn('count_answers', array(
            'header'    =>Mage::helper('askit')->__('Answers'),
            'width'     => '45px',
            'index'     => 'count_answers',
            'type'      => 'number',
            'sortable'      => false,
            'frame_callback' => array($this, 'decorateCountnumber'),
            'filter_condition_callback' => array($this, '_filterCountNumberCondition'),
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('askit')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('askit/status')->getQuestionOptionArray(),
            'frame_callback' => array($this, 'decorateStatus')
        ));

        $this->addColumn('private', array(
            'header'    => Mage::helper('askit')->__('Private'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'private',
            'type'      => 'options',
            'options'   => array(
                  0     => Mage::helper('askit')->__('Public'),
                  1     => Mage::helper('askit')->__('Private')
              ),
              'frame_callback' => array($this, 'decoratePrivate')
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('askit')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('action', array(
            'header'    =>  Mage::helper('askit')->__('Action'),
            'width'     => '100',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('askit')->__('Edit'),
                    'url'       => array('base'=> '*/*/edit'),
                    'field'     => 'id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('askit')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('askit')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('askit');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('askit')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('askit')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('askit/status')->getQuestionOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('askit')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('askit')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }
//////////////////
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    protected function _filterItemNameCondition($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (!$value) {
            return;
        }
        $this->getCollection()->addItemNameFilter($value);
    }

    protected function _filterCountNumberCondition($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (!$value) {
            return;
        }
        $this->getCollection()->addCountAnswerFilter(
            $value['from'],
            $value['to']
        );
    }

////////////////
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
