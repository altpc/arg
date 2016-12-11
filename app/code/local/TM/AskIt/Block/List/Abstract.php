<?php
abstract class TM_AskIt_Block_List_Abstract extends Mage_Core_Block_Template
{
    /**
     *
     * @var TM_AskIt_Model_Mysql4_Item_Collection
     */
    protected $_collection;

    public function getCount()
    {
        return $this->_getCollection()->getSize();
    }

    public function getItems()
    {
        return $this->_getCollection()->getItems();
    }

    protected function _getCollection($isForceLoad = true)
    {
        if (!$this->_collection) {
            $this->_collection = Mage::getModel('askit/message')
                ->getCollection()
                ->addStatusFilter(array(
                    TM_AskIt_Model_Status::STATUS_APROVED/*aprowed*/,
                    TM_AskIt_Model_Status::STATUS_CLOSE/*closed*/
                ))
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addPrivateFilter()
                ->addQuestionFilter()
                ->addQuestionAnswersData()
                ->addItemInfo()
                ->groupById()
                ->setOrder('created_time', 'DESC')
            ;

            $this->_prepareCollection();

            $limit = (int) $this->getQuestionLimit();
            if ($limit) {
                $this->_collection->getSelect()->limit($limit);
            }
            if ($isForceLoad) {
                $this->_collection->load();
            }
        }
        return $this->_collection;
    }

    /**
     *
     * @return bool
     */
    public function isProductViewPage()
    {
        $handles = $this->getLayout()->getUpdate()->getHandles();
        return in_array('catalog_product_view', $handles);
    }

    protected function _prepareCollection()
    {

    }

    public function getQuestionLimit()
    {
        return 10;
    }
}
