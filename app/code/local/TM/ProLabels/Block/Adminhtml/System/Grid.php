<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Block_Adminhtml_System_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('prolabelsSystemGrid');
        $this->setDefaultSort('rules_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('pro_system_rules_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('prolabels/system')->getCollection();

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    protected function _afterLoadCollection() {
        $this->getCollection()->walk('afterLoad'); parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('system_id', array(
            'header'    => Mage::helper('prolabels')->__('ID'),
            'align'     =>'right',
            'width'     => '20px',
            'index'     => 'system_id',
            'type'      => 'number'
        ));

        $this->addColumn('system_label_name', array(
            'header'    => Mage::helper('prolabels')->__('Name'),
            'align'     =>'left',
            'width'     => '450px',
            'index'     => 'system_label_name'
        ));

        $this->addColumn('product_position', array(
            'header'    => Mage::helper('prolabels')->__('Product Label Position'),
            'align'     => 'left',
            'width'     => '120px',
            'index'     => 'product_position',
            'type'      => 'options',
            'options'   => array(
                'top-left'      => Mage::helper('prolabels')->__('top-left'),
                'top-center'    => Mage::helper('prolabels')->__('top-center'),
                'top-right'     => Mage::helper('prolabels')->__('top-right'),
                'middle-left'   => Mage::helper('prolabels')->__('middle-left'),
                'middle-center' => Mage::helper('prolabels')->__('middle-center'),
                'middle-right'  => Mage::helper('prolabels')->__('middle-right'),
                'bottom-left'   => Mage::helper('prolabels')->__('bottom-left'),
                'bottom-center' => Mage::helper('prolabels')->__('bottom-center'),
                'bottom-right'  => Mage::helper('prolabels')->__('bottom-right'),
                'content'       => Mage::helper('prolabels')->__('content')
            )
        ));

        $this->addColumn('product_image', array(
            'header'    => Mage::helper('prolabels')->__('Product Label Image'),
            'align'     => 'center',
            'index'     => 'product_image',
            'width'     => '150',
            'renderer'  => 'TM_ProLabels_Block_Adminhtml_System_Grid_Renderer_Pimg'
        ));

        $this->addColumn('product_image_text', array(
            'header'    => Mage::helper('prolabels')->__('Product Label Text'),
            'align'     =>'left',
            'width'     => '150px',
            'index'     => 'product_image_text'
        ));

        $this->addColumn('category_position', array(
            'header'    => Mage::helper('prolabels')->__('Category Label Position'),
            'align'     => 'left',
            'width'     => '120px',
            'index'     => 'category_position',
            'type'      => 'options',
            'options'   => array(
                'top-left'      => Mage::helper('prolabels')->__('top-left'),
                'top-center'    => Mage::helper('prolabels')->__('top-center'),
                'top-right'     => Mage::helper('prolabels')->__('top-right'),
                'middle-left'   => Mage::helper('prolabels')->__('middle-left'),
                'middle-center' => Mage::helper('prolabels')->__('middle-center'),
                'middle-right'  => Mage::helper('prolabels')->__('middle-right'),
                'bottom-left'   => Mage::helper('prolabels')->__('bottom-left'),
                'bottom-center' => Mage::helper('prolabels')->__('bottom-center'),
                'bottom-right'  => Mage::helper('prolabels')->__('bottom-right'),
                'content'       => Mage::helper('prolabels')->__('content')
            )
        ));

        $this->addColumn('category_image', array(
            'header'    => Mage::helper('prolabels')->__('Category Label Image'),
            'align'     => 'center',
            'index'     => 'category_image',
            'width'     => '150',
            'renderer'  => 'TM_ProLabels_Block_Adminhtml_System_Grid_Renderer_Cimg'
        ));

        $this->addColumn('category_image_text', array(
            'header'    => Mage::helper('prolabels')->__('Category Label Text'),
            'align'     =>'left',
            'width'     => '150px',
            'index'     => 'category_image_text'
        ));

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

        $this->addColumn('rules_id', array(
            'header'    => Mage::helper('prolabels')->__('System Label'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'rules_id',
            'type'      => 'options',
            'options'   => array(
                '1' => Mage::helper('prolabels')->__('On Sale'),
                '2' => Mage::helper('prolabels')->__('In Stock'),
                '3' => Mage::helper('prolabels')->__('Is New')
            )
        ));

        $this->addColumn('priority', array(
            'header'    => Mage::helper('prolabels')->__('Priority'),
            'align'     =>'left',
            'width'     => '40px',
            'index'     => 'priority'
        ));

        $this->addColumn('l_status', array(
            'header'    => Mage::helper('prolabels')->__('Enabled'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'l_status',
            'type'      => 'options',
            'options'   => array(
                1 => 'Enabled',
                0 => 'Disabled'
            ),
            'frame_callback' => array($this, 'decorateStatus')
        ));

        return parent::_prepareColumns();
    }

    public function decorateStatus($value, $row, $column, $isExport)
    {
        $class = '';
        switch ($row->getLStatus()) {
            case 1 :
                $class = 'grid-severity-notice';
                break;
            case 0 :
                $class = 'grid-severity-critical';
                break;
        }
        return '<span class="'.$class.'"><span>'.$value.'</span></span>';
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getSystemId()));
    }
}
