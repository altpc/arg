<?php

class TM_SoldTogether_Block_Abstract extends Mage_Catalog_Block_Product_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->addData(array(
            'cache_lifetime'    => 86400,
            'cache_tags'        => array(Mage_Catalog_Model_Product::CACHE_TAG),
        ));
    }

    public function getCacheKeyInfo()
    {
        $productId = 0;
        if ($product = Mage::registry('product')) {
            $productId = $product->getId();
        }
        return array(
            $this->_cachePrefix,
            Mage::app()->getStore()->getId(),
            Mage::app()->getStore()->getCurrentCurrencyCode(),
            Mage::getDesign()->getPackageName(),
            $this->getTemplate(),
            $this->getProductsCount(),
            $this->getColumnsCount(),
            $this->getNameInLayout(),
            $productId
        );
    }

    /**
     * Process cached form_key and uenc params
     *
     * @param   string $html
     * @return  string
     */
    protected function _loadCache()
    {
        $cacheData = parent::_loadCache();
        if ($cacheData) {
            $search = array(
                '{{tm_soldtogether uenc}}'
            );
            $replace = array(
                Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED
                    . '/' . Mage::helper('core/url')->getEncodedUrl()
            );

            if (defined('Mage_Core_Model_Url::FORM_KEY')) {
                $formKey = Mage::getSingleton('core/session')->getFormKey();
                $search = array_merge($search, array(
                    '{{tm_soldtogether form_key_url}}',
                    '{{tm_soldtogether form_key_hidden}}'
                ));
                $replace = array_merge($replace, array(
                    Mage_Core_Model_Url::FORM_KEY . '/' . $formKey,
                    'value="' . $formKey . '"'
                ));
            }

            $cacheData = str_replace($search, $replace, $cacheData);
        }
        return $cacheData;
    }

    /**
     * Replace form_key and uenc with placeholders
     *
     * @param string $data
     * @return Mage_Core_Block_Abstract
     */
    protected function _saveCache($data)
    {
        if (is_null($this->getCacheLifetime())
            || !$this->getMageApp()->useCache(self::CACHE_GROUP)) {

            return false;
        }

        $search = array(
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED
                . '/' . Mage::helper('core/url')->getEncodedUrl()
        );
        $replace = array(
            '{{tm_soldtogether uenc}}'
        );

        if (defined('Mage_Core_Model_Url::FORM_KEY')) {
            $formKey = Mage::getSingleton('core/session')->getFormKey();
            $search = array_merge($search, array(
                Mage_Core_Model_Url::FORM_KEY . '/' . $formKey,
                'value="' . $formKey . '"'
            ));
            $replace = array_merge($replace, array(
                '{{tm_soldtogether form_key_url}}',
                '{{tm_soldtogether form_key_hidden}}'
            ));
        }

        $data = str_replace($search, $replace, $data);
        return parent::_saveCache($data);
    }

    /**
     * EE compatibility
     *
     * @return Mage_Core_Model_App
     */
    public function getMageApp()
    {
        if (method_exists($this, '_getApp')) {
            return $this->_getApp();
        }
        return Mage::app();
    }

    /**
     * Retrieve product final price in current currency
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  boolean $includingTax
     * @return float
     */
    public function getProductFinalPrice($product, $includingTax = false)
    {
        $basePrice = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), $includingTax);
        return round(Mage::helper('core')->currency($basePrice, false, false), 2);
    }

    protected function _toHtml()
    {
        if (!$this->getProductCollection()) {
            return '';
        }
        return parent::_toHtml();
    }

    public function getProductsCount()
    {
        if (!isset($this->_data['products_count'])) {
            $this->_data['products_count'] =
                Mage::getStoreConfig("soldtogether/{$this->_configGroup}/productscount");
        }
        return $this->_data['products_count'];
    }

    public function getColumnsCount()
    {
        if (!isset($this->_data['columns_count'])) {
            $this->_data['columns_count'] =
                Mage::getStoreConfig("soldtogether/{$this->_configGroup}/columns");
        }
        return $this->_data['columns_count'];
    }

    public function getProducts()
    {
        $products = array();
        if (Mage::registry('product')) {
            $products[] = Mage::registry('product');
        } elseif (Mage::registry('current_order')) {
            $order = Mage::registry('current_order');
            foreach ($order->getAllVisibleItems() as $item) {
                $products[] = $item->getProduct();
            }
        }
        return $products;
    }
}