<?php

class TM_AskIt_Block_Adminhtml_AskIt_Edit_Tab_Assign_Products extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('askItGrid_assigned_products_items');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $message = Mage::registry('askit_data');
        if ($message->getId()) {
            $this->setDefaultFilter(array('in_products' => 1));
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
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('catalog/product_link')->useRelatedLinks()
            ->getProductCollection()
            ->addAttributeToSelect('*');

        // if ($this->isReadonly()) {
            // $productIds = $this->_getSelectedProducts();
            // if (empty($productIds)) {
            //     $productIds = array(0);
            // }
            // $collection->addFieldToFilter('entity_id', array('in' => $productIds));
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
        $this->addColumn('in_products', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'in_products',
            'values'            => $this->_getSelectedProducts(),
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
            : $this->getUrl('*/*/assignproductsGrid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return '#';
    }

    /**
     * Retrieve selected assigned products
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getAssignedProducts();
        if (!is_array($products)) {
            $products = $this->getSelectedAssignedProducts();
        }
        return $products;
    }

    /**
     * Retrieve products
     *
     * @return array
     */
    public function getSelectedAssignedProducts()
    {
        $products = array();
        $collection = Mage::getModel('askit/item')->getCollection();
        $message = Mage::registry('askit_data');
        $collection->addMessageIdFilter($message->getId())
            ->addItemTypeIdFilter(TM_AskIt_Model_Item_Type::PRODUCT_ID);
        foreach ($collection as $item) {
            $products[] = $item->getItemId();
        }
        return $products;
    }
}
