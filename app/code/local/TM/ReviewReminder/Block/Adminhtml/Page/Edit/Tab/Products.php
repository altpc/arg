<?php
class TM_ReviewReminder_Block_Adminhtml_Page_Edit_Tab_Products
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $reviewStatuses = array();

    public function __construct()
    {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }
    protected function _prepareCollection()
    {
        $model = Mage::registry('reminder_data');
        $order = Mage::getModel('sales/order')->load($model->getOrderId());

        $orderWebsiteId = Mage::getModel('core/store')
            ->load($order->getStoreId())
            ->getWebsiteId();
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId($orderWebsiteId);
        $customer->loadByEmail($model->getCustomerEmail());
        $customerId = $customer->getId();

        $orderedItems = $order->getAllVisibleItems();
        $orderedProductIds = array();
        foreach ($orderedItems as $item) {
            $productId = $item->getData('product_id');
            if ($customerId) {
                $reviewsCollection = Mage::getSingleton('review/review')->getProductCollection();
                $reviewsCollection->addCustomerFilter($customerId);
                $reviewsCollection->addEntityFilter($productId);
                $reviewStatus = $reviewsCollection->load()->getSize() ?
                    TM_ReviewReminder_Model_Entity::REVIEWED :
                    TM_ReviewReminder_Model_Entity::NOT_REVIEWED;
            } else {
                $reviewStatus = TM_ReviewReminder_Model_Entity::NO_CUSTOMER;
            }
            $this->reviewStatuses[$productId] = $reviewStatus;
        }

        foreach ($orderedItems as $item) {
            $productId = $item->getData('product_id');
            array_push($orderedProductIds, $productId);
        }

        $productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addIdFilter($orderedProductIds)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('sku');

        $this->setCollection($productCollection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _afterLoadCollection()
    {
        $collection = $this->getCollection();
        $cond = $this->getColumn('review_status')->getFilter()->getCondition();

        foreach ($collection as $product) {
            $product->setReviewStatus($this->reviewStatuses[$product->getId()]);

            if ($cond && $product->getReviewStatus() != $cond['eq'])
            {
                $collection->removeItemByKey($product->getId());
            }
        }

        return $this;
    }

     /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'=> Mage::helper('catalog')->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));
        $this->addColumn('sku', array(
            'header'=> Mage::helper('catalog')->__('SKU'),
            'align' => 'left',
            'index' => 'sku',
        ));
        $this->addColumn('price', array(
            'header'=> Mage::helper('catalog')->__('Price'),
            'type'  => 'currency',
            'width' => '1',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index' => 'price'
        ));
        $this->addColumn('type', array(
            'header'    => Mage::helper('catalog')->__('Type'),
            'width'     => 100,
            'index'     => 'type_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));
        $this->addColumn('review_status', array(
            'header'    => Mage::helper('tm_reviewreminder')->__('Review Status'),
            'width'     => 200,
            'index'     => 'review_status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('tm_reviewreminder/entity')->getReviewStatuses(),
            'filter_condition_callback' => array($this, 'filterByReviewStatus')
        ));
    }
    public function filterByReviewStatus($collection, $column)
    {

    }
    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('tm_reviewreminder')->__('Order Products');
    }
    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('tm_reviewreminder')->__('Order Products');
    }
    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/products', array('_current'=>true));
    }
    public function getTabUrl()
    {
        return $this->getUrl('*/*/products', array('_current' => true));
    }
    public function getTabClass()
    {
        return 'ajax';
    }
    public function getSkipGenerateContent()
    {
        return true;
    }
    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return false;
    }
}