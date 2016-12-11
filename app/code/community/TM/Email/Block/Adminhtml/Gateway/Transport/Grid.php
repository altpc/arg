<?php

class TM_Email_Block_Adminhtml_Gateway_Transport_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('tm_email_gateway_transport_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');

        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('tm_email/gateway_transport')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('tm_email')->__('ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'id',
            'type'      => 'number'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('tm_email')->__('Title'),
            'align'     => 'left',
            'index'     => 'name',
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('tm_email')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                1     => Mage::helper('tm_email')->__('Enabled'),
                0     => Mage::helper('tm_email')->__('Disabled')
            )
        ));
        $types = TM_Email_Model_System_Config_Source_Gateway_Transport_Provider::toOptionHash();
        $this->addColumn('type', array(
            'header'    => Mage::helper('tm_email')->__('Type'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'type',
            'type'      => 'options',
            'options'   => $types
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('tm_email')->__('Email'),
            'align'     => 'left',
            'index'     => 'email',
        ));

        $this->addColumn('host', array(
            'header'    => Mage::helper('tm_email')->__('Host'),
            'align'     => 'left',
            'index'     => 'host',
        ));

        $this->addColumn('user', array(
            'header'    => Mage::helper('tm_email')->__('User'),
            'align'     => 'left',
            'index'     => 'user',
        ));

        $this->addColumn('port', array(
            'header'    => Mage::helper('tm_email')->__('Port'),
            'align'     => 'left',
            'index'     => 'port',
        ));

        $this->addColumn('secure', array(
            'header'    => Mage::helper('tm_email')->__('Secure'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'secure',
            'type'      => 'options',
            'options'   => array(
                0     => Mage::helper('tm_email')->__('None'),
                1     => Mage::helper('tm_email')->__('SSL'),
                2     => Mage::helper('tm_email')->__('TLS')
            )
        ));

        /*
        $this->addColumn('remove', array(
          'header'    => Mage::helper('tm_email')->__('Remove'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'remove',
          'type'      => 'options',
          'options'   => array(
                1     => Mage::helper('tm_email')->__('Enabled'),
                0     => Mage::helper('tm_email')->__('Disabled')
            )
        ));
        */
        $this->addColumn('action', array(
            'header'    =>  Mage::helper('tm_email')->__('Action'),
            'width'     => '100',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('tm_email')->__('Edit'),
                    'url'       => array('base'=> '*/*/edit'),
                    'field'     => 'id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('tm_email')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('tm_email')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('tm_email');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('tm_email')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('tm_email')->__('Are you sure?')
        ));

        return $this;
    }
////////////////////
//    protected function _afterLoadCollection()
//    {
//        $this->getCollection()->walk('afterLoad');
//        parent::_afterLoadCollection();
//    }
//
//    protected function _filterStoreCondition($collection, $column)
//    {
////        Zend_Debug::dump($column->getFilter()->getValue());
////        die;
//        if (!$value = $column->getFilter()->getValue()) {
//            return;
//        }
//
//        $this->getCollection()->addStoreFilter($value);
//    }
////////////////
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
