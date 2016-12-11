<?php

class TM_EasyBanner_Model_Enterprise_PageCache_Container_Abstract
    extends Enterprise_PageCache_Model_Container_Abstract
{
    protected $_attributes = array();
    protected $_mapping    = array();

    public function applyWithoutApp(&$content)
    {
        return false;
    }

    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;
    }

    protected function _renderBlock()
    {
        $this->_initRegistryVariables();
        $this->_copyAttributes();
        $block = $this->_getPlaceHolderBlock();
        Mage::dispatchEvent('render_block', array('block' => $block, 'placeholder' => $this->_placeholder));
        return $block->toHtml();
    }

    protected function _initRegistryVariables()
    {
        if (Mage::registry('current_product') && Mage::registry('current_category')) {
            return;
        }

        $product = null;
        $productId = $this->_getProductId();
        if ($productId && !Mage::registry('current_product')) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product) {
                Mage::register('current_product', $product);
            }
        }

        $categoryId = $this->_getCategoryId();
        if ($product !== null && !$product->canBeShowInCategory($categoryId)) {
            $categoryId = null;
            Mage::unregister('current_category');
        }
        if ($categoryId && !Mage::registry('current_category')) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            if ($category) {
                Mage::register('current_category', $category);
            }
        }
    }

    protected function _copyAttributes()
    {
        $block = $this->_getPlaceHolderBlock();
        foreach ($this->_attributes as $attribute) {
            if (isset($this->_mapping[$attribute])) {
                $methodName = $this->_mapping[$attribute];
                if (method_exists($this, $methodName)) {
                    $this->{$methodName}();
                    continue;
                }
            }

            if (!$value = $this->_placeholder->getAttribute($attribute)) {
                continue;
            }
            $block->setData($attribute, $value);
        }
    }
}
