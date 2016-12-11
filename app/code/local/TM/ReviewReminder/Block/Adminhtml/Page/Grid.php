<?php
class TM_ReviewReminder_Block_Adminhtml_Page_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('reviewreminderGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultFilter(array('status' => 'not_sent'));
        $this->setDefaultDir('ASC');
    }
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('tm_reviewreminder/entity')
            ->getCollection()
            ->joinOrderInfo();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection() && $column->getId() === 'status') {
            $condition = $column->getFilter()->getCondition();
            if (!empty($condition['eq']) && 'not_sent' === $condition['eq']) {
                $this->getCollection()->addFieldToFilter(
                    'main_table.status',
                    array(
                        'neq' => TM_ReviewReminder_Model_Entity::STATUS_SENT
                    )
                );
                return;
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'   => Mage::helper('adminhtml')->__('ID'),
            'index'    => 'entity_id',
            'width'    => 50
        ));
        $this->addColumn('fullname', array(
            'header'   => Mage::helper('adminhtml')->__('Customer Name'),
            'index'    => 'fullname',
            'filter_index' => new Zend_Db_Expr('(SELECT CONCAT(a.customer_firstname, \' \', a.customer_lastname))')
        ));
        $this->addColumn('email', array(
            'header'   => Mage::helper('sales')->__('Customer Email'),
            'index'    => 'customer_email',
            'filter_index' => 'main_table.customer_email'
        ));
        $this->addColumn('products', array(
            'header'       => Mage::helper('adminhtml')->__('Products'),
            'index'        => 'products',
            'filter_index' => new Zend_Db_Expr('(SELECT GROUP_CONCAT(\' \', x.name)
                                FROM ' . Mage::getResourceModel('sales/order_item')->getMainTable() . ' x
                                WHERE a.entity_id = x.order_id
                                    AND x.product_type != \'configurable\')')
        ));
        $this->addColumn('order_date', array(
            'header'   => Mage::helper('sales')->__('Order Date'),
            'index'    => 'order_date',
            'width'    => 150,
            'type' => 'datetime'
        ));

        $statuses = Mage::getSingleton('tm_reviewreminder/entity')->getAvailableStatuses();
        $statuses = array('not_sent' => Mage::helper('tm_reviewreminder')->__('Not Sent')) + $statuses;
        $this->addColumn('status', array(
            'header'   => Mage::helper('cms')->__('Status'),
            'index'    => 'status',
            'filter_index' => 'main_table.status',
            'type'     => 'options',
            'width'    => 150,
            'options'  => $statuses
        ));
        return parent::_prepareColumns();
    }
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('reviewreminder');
        if ($this->_isAllowedAction('delete')) {
            $this->getMassactionBlock()->addItem('delete', array(
                 'label'=> Mage::helper('catalog')->__('Delete'),
                 'url'  => $this->getUrl('*/*/massDelete'),
                 'confirm' => Mage::helper('catalog')->__('Are you sure?')
            ));
        }
        if ($this->_isAllowedAction('send')) {
            $this->getMassactionBlock()->addItem('send', array(
                 'label'=> Mage::helper('tm_reviewreminder')->__('Send'),
                 'url'  => $this->getUrl('*/*/massSend'),
                 'confirm' => Mage::helper('catalog')->__('Are you sure?')
            ));
        }
        if ($this->_isAllowedAction('status')) {
            $statuses = Mage::getSingleton('tm_reviewreminder/entity')->getAvailableStatuses();
            array_unshift($statuses, '');
            $this->getMassactionBlock()->addItem('status', array(
                 'label'=> Mage::helper('catalog')->__('Change status'),
                 'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                 'additional' => array(
                        'visibility' => array(
                             'name' => 'status',
                             'type' => 'select',
                             'class' => 'required-entry',
                             'label' => Mage::helper('catalog')->__('Status'),
                             'values' => $statuses
                         )
                 )
            ));
        }

        return $this;
    }
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
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('entity_id' => $row->getEntityId()));
    }
    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/tm_reviewreminder/' . $action);
    }
}
