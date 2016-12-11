<?php
class TM_SoldTogether_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customerGrid');
        // This is the primary key of the database
        $this->setDefaultSort('relation_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('soldtogether/customer')->getCollection();

        $resource = Mage::getSingleton('core/resource');

        $collection->getSelect()
		    ->distinct(true)
            ->join(array('cpev1' => $resource->getTableName('catalog_product_entity_varchar')),
                'cpev1.entity_id = main_table.product_id',
                 array('product_name' => 'value'))
            ->join(array('cpev2' => $resource->getTableName('catalog_product_entity_varchar')),
                'cpev2.entity_id = main_table.related_product_id',
                array('related_product_name' => 'value'))
            ->join(array('ea' => Mage::getResourceModel('eav/entity_attribute')->getMainTable()),
                'cpev1.attribute_id = ea.attribute_id and cpev2.attribute_id = ea.attribute_id')
            ->join(array('eet' => Mage::getResourceModel('eav/entity_type')->getMainTable()),
                'eet.entity_type_id = ea.entity_type_id')
            ->where('ea.attribute_code = ?', 'name')
            ->where('eet.entity_type_code = ?', 'catalog_product')
            ->group('main_table.relation_id');

        $this->setCollection($collection);
		parent::_prepareCollection();

        return $this;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('relation_id');
        $this->getMassactionBlock()->setFormFieldName('relation_id');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('catalog')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('relation_id', array(
            'header'    => Mage::helper('soldtogether')->__('ID'),
            'type'      => 'number',
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'relation_id',
        ));

        $this->addColumn('product_name', array(
            'header'    => Mage::helper('soldtogether')->__('Product Name'),
            'align'     => 'left',
            'index'     => 'product_name',
        ));

        $this->addColumn('related_product_name', array(
            'header'    => Mage::helper('soldtogether')->__('Related Product Name'),
            'align'     => 'left',
            'index'     => 'related_product_name',
        ));

        $this->addColumn('weight', array(
            'header'    => Mage::helper('soldtogether')->__('Weight'),
            'type'      => 'number',
            'align'     => 'left',
            'index'     => 'weight',
        ));

        $this->addColumn('is_admin', array(
            'header'    => Mage::helper('soldtogether')->__('Is Admin'),
            'align'     => 'left',
            'width'     => '60px',
            'index'     => 'is_admin',
            'type'      => 'options',
            'options'   => array(
                1 => 'True',
                0 => 'False'
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
        return $this->getUrl('*/*/edit', array('id' => $row->getRelationId()));
    }
}
