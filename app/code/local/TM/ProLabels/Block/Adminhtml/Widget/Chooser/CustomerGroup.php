<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Block_Adminhtml_Widget_Chooser_CustomerGroup extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct($arguments=array())
    {
        parent::__construct($arguments);
        
        if ($this->getRequest()->getParam('current_grid_id')) {
            $this->setId($this->getRequest()->getParam('current_grid_id'));
        } else {
            $this->setId('customerGroupChooserGrid'.$this->getId());
        }
        
        $form = $this->getJsFormObject();
        $this->setRowClickCallback("$form.chooserGridRowClick.bind($form)");
        $this->setCheckboxCheckCallback("$form.chooserGridCheckboxCheck.bind($form)");
        $this->setRowInitCallback("$form.chooserGridRowInit.bind($form)");
        $this->setDefaultSort('customer_group_id ASC');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }
    
    /**
     * Init customer groups collection
     * @return void
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/group_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Configuration of grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_groups', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_groups',
            'values'    => $this->_getSelectedGroups(),
            'align'     => 'center',
            'index'     => 'customer_group_id',
            'use_index' => true,
        ));
        
        $this->addColumn('customer_group_id', array(
            'header' => Mage::helper('customer')->__('ID'),
            'width' => '50px',
            'align' => 'right',
            'index' => 'customer_group_id',
        ));
        
        $this->addColumn('customer_group_code', array(
            'header' => Mage::helper('customer')->__('Group Name'),
            'index' => 'customer_group_code',
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

    protected function _getSelectedGroups()
    {
        return $this->getRequest()->getPost('selected', array());
    }

}
