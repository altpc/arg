<?php

class TM_SuggestPage_Block_Cart extends Mage_Checkout_Block_Cart_Sidebar
{
    protected $_lastAddedQuoteItems = null;

    protected function _prepareLayout()
    {
        if (!Mage::registry('product')) {
            $productId = Mage::getSingleton('checkout/session')->getSuggestpageProductId();
            if (!$productId) {
                return false;
            }

            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if (!$product->getId()) {
                return false;
            }
            Mage::register('product', $product);
        }
    }

    public function getLastAddedQuoteItems()
    {
        if (null === $this->_lastAddedQuoteItems) {
            $itemIds = Mage::getSingleton('checkout/session')->getSuggestpageQuoteItemIds();
            if (!$itemIds) {
                return false;
            }

            $items = array();
            $cartItems = $this->getItems();
            foreach ($cartItems as $cartItem) {
                if (in_array($cartItem->getId(), $itemIds)) {
                    $items[] = $cartItem;
                }
            }
            if (!$items) {
                return false;
            }
            $this->_lastAddedQuoteItems = $items;
        }
        return $this->_lastAddedQuoteItems;
    }
}
