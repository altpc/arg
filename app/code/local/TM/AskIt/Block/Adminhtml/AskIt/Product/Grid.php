<?php
class TM_AskIt_Block_Adminhtml_AskIt_Product_Grid extends TM_AskIt_Block_Adminhtml_AskIt_Grid_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('product_askit_question');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);

        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('askit/message')->getCollection();

        $product = Mage::registry('product');
        if ($product) {
            $collection->addProductIdFilter($product->getId());
        }
        $collection->addQuestionCountAnswersData();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/askIt_index/edit', array('id' => $row->getId()));
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
        return parent::_prepareColumns();
    }
}
