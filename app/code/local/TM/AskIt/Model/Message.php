<?php
class TM_AskIt_Model_Message extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('askit/message');
    }

    /**
     *
     * @return mixed
     */
    public function getItem()
    {
        $this->addFirstItemData();
        $_item = Mage::helper('askit')->getItem($this->getData());
        return $_item->getRawItem();
    }

    public function isVoted($customerId = null)
    {
        // if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
        //     return false;
        // }
        if (null == $customerId) {
            $customerId = (int) Mage::getSingleton('customer/session')->getCustomerId();
        }

        return Mage::getModel('askit/vote')->isVoted($this->getId(), $customerId);
    }

    protected function addFirstItemData()
    {
        if ($this->getId() && !$this->getItemTypeId()) {

            $collection = Mage::getModel('askit/item')->getCollection()
                ->addMessageIdFilter($this->getId())
                ->addEmptyFilter()
            ;
            $assign = $collection->getFirstItem();

            if ($assign->getId()) {
                $keys = array(
                    'item_id',
                    'item_type_id'
                );

                foreach ($keys as $key) {
                    $value = $assign->getData($key);
                    $this->setData($key, $value);
                }
            }
        }
        return $this;
    }

    protected function _afterLoad()
    {
        $this->addFirstItemData();
        return parent::_afterLoad();
    }

    public function addAssignsFromPost($id, $data)
    {
        if (!isset($data['item_id']) || !isset($data['item_type_id'])) {
            return false;
        }
        $types = array(
            TM_AskIt_Model_Item_Type::PRODUCT_CATEGORY_ID => 'assigned_cats',
            TM_AskIt_Model_Item_Type::CMS_PAGE_ID => 'assigned_pages',
            TM_AskIt_Model_Item_Type::PRODUCT_ID => 'assigned_products'
        );
        foreach ($types as $type => $param) {
            if (!isset($data[$param])) {
                continue;
            }
            $itemCollection = Mage::getModel('askit/item')->getCollection()
                ->addItemTypeIdFilter($type)
                ->addMessageIdFilter($id);

            foreach ($itemCollection as $item) {
                $item->delete();
            }
        }

        $itemCollection = Mage::getModel('askit/item')->getCollection()
            ->addEmptyFilter()
            ->addMessageIdFilter($id)
            ->addItemIdFilter((int) $data['item_id'])
            ->addItemTypeIdFilter((int) $data['item_type_id'])
        ;

        if (0 == $itemCollection->getSize()) {
            $item = Mage::getModel('askit/item');
            $item->setData(array(
                'message_id'   => $id,
                'item_id'      => (int) $data['item_id'],
                'item_type_id' => (int) $data['item_type_id']
            ))->save();
        }

        foreach ($types as $type => $param) {
            if (!isset($data[$param])) {
                continue;
            }

            $_items = Mage::helper('adminhtml/js')->decodeGridSerializedInput(
                $data[$param]
            );

            foreach ($_items as $_item) {
                if ($_item == intval($data['item_id'])
                    && $type == intval($data['item_type_id'])) {

                    continue;
                }
                $item = Mage::getModel('askit/item');
                $item->setData(array(
                    'message_id'   => $id,
                    'item_id'      => $_item,
                    'item_type_id' => $type
                ))->save();
            }
        }
        return $this;
    }
}
