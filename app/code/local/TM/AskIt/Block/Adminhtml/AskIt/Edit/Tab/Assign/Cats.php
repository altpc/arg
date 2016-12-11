<?php

class TM_AskIt_Block_Adminhtml_AskIt_Edit_Tab_Assign_Cats extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('askItGrid_assigned_cats_items');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $message = Mage::registry('askit_data');
        if ($message->getId()) {
            $this->setDefaultFilter(array('in_cats' => 1));
        }
    }

    /**
     * Add filter
     *
     * @param object $column
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Related
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_cats') {
            $catIds = $this->_getSelecteds();
            if (empty($catIds)) {
                $catIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $catIds));
            } else {
                if ($catIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $catIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('*');


        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_cats', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'in_cats',
            'values'            => $this->_getSelecteds(),
            'align'             => 'center',
            'index'             => 'entity_id'
        ));

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'entity_id'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('*/*/assigncatsGrid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return '#';
    }

    /**
     * Retrieve selected assigned pages
     *
     * @return array
     */
    protected function _getSelecteds()
    {
        $assigneds = $this->getAssignedCats();
        if (!is_array($assigneds)) {
            $assigneds = $this->getSelectedAssigneds();
        }
        return $assigneds;
    }

    /**
     * Retrieve pages
     *
     * @return array
     */
    public function getSelectedAssigneds()
    {
        $pages = array();
        $collection = Mage::getModel('askit/item')->getCollection();
        $message = Mage::registry('askit_data');
        $collection->addMessageIdFilter($message->getId())
            ->addItemTypeIdFilter(TM_AskIt_Model_Item_Type::PRODUCT_CATEGORY_ID);
        foreach ($collection as $item) {
            $pages[] = $item->getItemId();
        }
        return $pages;
    }
}
