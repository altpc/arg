<?php

class TM_Attributepages_Block_Adminhtml_Page_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('attributepagesGrid');
        $this->setDefaultSort('identifier');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('attributepages/entity')
            ->getCollection();

        if ('option' === $this->getParentBlock()->getEntityType()) {
            $collection->addOptionOnlyFilter();
        } else {
            $collection->addAttributeOnlyFilter();
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getPagesCollection()
    {
        $collection = $this->_getData('pages_collection');
        if (!$collection) {
            $collection = Mage::getModel('attributepages/entity')
                ->getCollection()
                ->addAttributeOnlyFilter();

            $this->setData('pages_collection', $collection);
        }
        return $collection;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'index'  => 'entity_id',
            'width' => '50px',
            'type'  => 'number'
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'align'  => 'left',
            'index'  => 'name'
        ));

        $this->addColumn('title', array(
            'header' => Mage::helper('cms')->__('Title'),
            'align'  => 'left',
            'index'  => 'title'
        ));

        $this->addColumn('identifier', array(
            'header' => Mage::helper('cms')->__('URL Key'),
            'align'  => 'left',
            'index'  => 'identifier'
        ));

        $this->addColumn('root_template', array(
            'header'  => Mage::helper('cms')->__('Layout'),
            'index'   => 'root_template',
            'type'    => 'options',
            'options' => Mage::getSingleton('page/source_layout')->getOptions()
        ));

        $pages = $this->getPagesCollection();
        if ($pages->getSize()) {
            $attributes = Mage::getResourceModel('attributepages/catalog_product_attribute_collection')
                ->setFrontendInputTypeFilter(array('select', 'multiselect'))
                ->addOrder('frontend_label', 'ASC')
                ->addFieldToFilter('main_table.attribute_id', array(
                    'in' => array_unique($pages->getColumnValues('attribute_id'))
                ))
                ->toOptionHash();
            $this->addColumn('attribute_id', array(
                'header'       => Mage::helper('catalog')->__('Attribute'),
                'index'        => 'attribute_id',
                'filter_index' => 'main_table.attribute_id',
                'type'         => 'options',
                'options'      => $attributes
            ));
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('cms')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('use_for_attribute_page', array(
            'header'  => Mage::helper('cms')->__('Status'),
            'index'   => 'use_for_attribute_page',
            'type'    => 'options',
            'options' => Mage::getSingleton('cms/page')->getAvailableStatuses()
        ));

        return parent::_prepareColumns();
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

    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('entity_id' => $row->getId()));
    }
}
