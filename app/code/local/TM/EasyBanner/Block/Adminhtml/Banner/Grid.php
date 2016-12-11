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
            'header'    => Mage::helper('easybanner')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'banner_id',
            'type'      => 'number'
        ));

        $this->addColumn('identifier', array(
            'header'    => Mage::helper('easybanner')->__('Name'),
            'align'     =>'left',
            'index'     => 'identifier'
        ));

        $this->addColumn('display_count', array(
            'header'    => Mage::helper('easybanner')->__('Display Count'),
            'align'     => 'left',
            'width'     => '130px',
            'default'   => '--',
            'type'      => 'number',
            'index'     => 'display_count'
        ));

        $this->addColumn('clicks_count', array(
            'header'    => Mage::helper('easybanner')->__('Clicks Count'),
            'align'     => 'left',
            'width'     => '130px',
            'default'   => '--',
            'type'      => 'number',
            'index'     => 'clicks_count'
        ));

        $this->addColumn('placeholder', array(
            'header'    => Mage::helper('easybanner')->__('Placeholder'),
            'align'     => 'left',
            'width'     => '200px',
            'default'   => '--',
            'index'     => 'placeholder',
            'sortable'  => false
        ));

        $this->addColumn('sort_order', array(
            'header'    => Mage::helper('easybanner')->__('Sort order'),
            'align'     => 'left',
            'width'     => '200px',
            'index'     => 'sort_order',
            'type'      => 'number'
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