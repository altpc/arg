<?php

class TM_AskIt_Block_Adminhtml_AskIt_Edit_Tab_Assign_Pages extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('askItGrid_assigned_pages_items');
        $this->setDefaultSort('page_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $message = Mage::registry('askit_data');
        if ($message->getId()) {
            $this->setDefaultFilter(array('in_pages' => 1));
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
        if ($column->getId() == 'in_pages') {
            $pageIds = $this->_getSelecteds();
            if (empty($pageIds)) {
                $pageIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('page_id', array('in' => $pageIds));
            } else {
                if ($pageIds) {
                    $this->getCollection()->addFieldToFilter('page_id', array('nin' => $pageIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('cms/page')->getCollection();
        /* @var $collection Mage_Cms_Model_Mysql4_Page_Collection */
        $collection->setFirstStoreFlag(true);

        // $collection = Mage::getModel('catalog/product_link')->useRelatedLinks()
        //     ->getProductCollection()
        //     ->addAttributeToSelect('*');

        // if ($this->isReadonly()) {
            // $pageIds = $this->_getSelecteds();
            // if (empty($pageIds)) {
            //     $pageIds = array(0);
            // }
            // $collection->addFieldToFilter('entity_id', array('in' => $pageIds));
        // }

        $this->setCollection($collection);
        return parent::_prepareCollection();

        // $collection = Mage::getModel('askit/item')->getCollection();
        // $message = Mage::registry('askit_data');
        // $collection->addMessageIdFilter($message->getId())
        //     ->addItemTypeIdFilter(TM_AskIt_Model_Item_Type::PRODUCT_ID);
        // $this->setCollection($collection);

        // return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_pages', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'in_pages',
            'values'            => $this->_getSelecteds(),
            'align'             => 'center',
            'index'             => 'page_id'
        ));

        $this->addColumn('page_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'page_id'
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('catalog')->__('Title'),
            'index'     => 'title'
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
            : $this->getUrl('*/*/assignpagesGrid', array('_current' => true));
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
        $assigneds = $this->getAssignedPages();
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
            ->addItemTypeIdFilter(TM_AskIt_Model_Item_Type::CMS_PAGE_ID);
        foreach ($collection as $item) {
            $pages[] = $item->getItemId();
        }
        return $pages;
    }
}
