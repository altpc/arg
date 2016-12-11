<?php

class TM_EasyBanner_Block_Adminhtml_Banner_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('bannerGrid');
        $this->setDefaultSort('banner_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('banner_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('easybanner/banner')->getCollection()
            ->addStatistics();

        $this->setCollection($collection);

        parent::_prepareCollection();

        $this->getCollection()
            ->addPlaceholderNamesToResult();

        return $this;
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            switch ($column->getId()) {
                case 'placeholder':
                    $this->getCollection()->distinct(true)->joinLeft('banner_placeholder',
                        'banner_placeholder.banner_id = main_table.banner_id',
                        '')
                    ->joinLeft('placeholder',
                        'placeholder.placeholder_id = banner_placeholder.placeholder_id',
                        '');
                    break;
                case 'display_count':
                case 'clicks_count':
                    $mapping = array(
                        'display_count' => 'SUM(display_count)',
                        'clicks_count'  => 'SUM(clicks_count)'
                    );
                    $condition = $column->getFilter()->getCondition();
                    if (isset($condition['from']) && isset($condition['to'])) {
                        $this->getCollection()->getSelect()->having(
                            $mapping[$column->getId()] . ' > ' . $condition['from']
                                . ' AND ' . $mapping[$column->getId()] . ' < ' . $condition['to']
                        );
                    } elseif (isset($condition['from'])) {
                        $this->getCollection()->getSelect()->having(
                            $mapping[$column->getId()] . ' > ' . $condition['from']);
                    } elseif (isset($condition['to'])) {
                        $this->getCollection()->getSelect()->having(
                            $mapping[$column->getId()] . ' < ' . $condition['to']);
                    }
                    return $this;
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareColumns()
    {
        $this->addColumn('banner_id', array(
            'header'    => $this->__('ID'),
            'index'     => 'banner_id',
            'type'      => 'number'
        ));

        $this->addColumn('identifier', array(
            'header'    => $this->__('Name'),
            'index'     => 'identifier'
        ));

        $this->addColumn('display_count', array(
            'header'    => $this->__('Display Count'),
            'default'   => '--',
            'type'      => 'number',
            'index'     => 'display_count'
        ));

        $this->addColumn('clicks_count', array(
            'header'    => $this->__('Clicks Count'),
            'default'   => '--',
            'type'      => 'number',
            'index'     => 'clicks_count'
        ));

        $this->addColumn('placeholder', array(
            'header'    => $this->__('Placeholder'),
            'default'   => '--',
            'index'     => 'placeholder',
            'sortable'  => false
        ));

        $this->addColumn('sort_order', array(
            'header'    => $this->__('Sort order'),
            'index'     => 'sort_order',
            'type'      => 'number'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'     => $this->__('Store View'),
                'index'      => 'store_id',
                'type'       => 'store',
                'store_all'  => true,
                'store_view' => true,
                'sortable'   => false,
                'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('status', array(
            'header'    => $this->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                1 => 'Enabled',
                0 => 'Disabled'
            )
        ));

        $this->addColumn('content', array(
            'header'    => $this->__('Content'),
            'index'     => 'content',
            'width'     => '400',
            'renderer'  => 'easybanner/adminhtml_widget_grid_column_renderer_content',
            'image_css' => 'max-height: 50px; max-width: 400px',
            'filter'    => false,
            'sortable'  => false
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

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addStoreFilter($value);
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

}
