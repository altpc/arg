<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Block_Adminhtml_Widget_Chooser_Handle extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct($arguments=array())
    {
        parent::__construct($arguments);

        if ($this->getRequest()->getParam('current_grid_id')) {
            $this->setId($this->getRequest()->getParam('current_grid_id'));
        } else {
            $this->setId('handleChooserGrid_'.$this->getId());
        }

        $form = $this->getJsFormObject();
        $this->setRowClickCallback("$form.chooserGridRowClick.bind($form)");
        $this->setCheckboxCheckCallback("$form.chooserGridCheckboxCheck.bind($form)");
        $this->setRowInitCallback("$form.chooserGridRowInit.bind($form)");
        $this->setDefaultSort('handle_name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_handles') {
            $selected = $this->_getSelectedHandles();
            if (empty($selected)) {
                $selected = '';
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('id', array('in'=>$selected));
            } else {
                $this->getCollection()->addFieldToFilter('id', array('nin'=>$selected));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('easybanner/handle_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_handles', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_handles',
            'values'    => $this->_getSelectedHandles(),
            'align'     => 'center',
            'index'     => 'id',
            'use_index' => true
        ));
        
        $this->addColumn('handle_name', array(
            'header'    => Mage::helper('easybanner')->__('Page'),
            'name'      => 'handle_name',
            'index'     => 'name'
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/chooser', array(
            '_current'          => true,
            'current_grid_id'   => $this->getId(),
            'collapse'          => null
        ));
    }

    protected function _getSelectedHandles()
    {
        return $this->getRequest()->getPost('selected', array());
    }

}

