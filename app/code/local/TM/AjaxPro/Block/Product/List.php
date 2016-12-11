<?php

class TM_AjaxPro_Block_Product_List extends TM_AjaxPro_Block_Product_List_Abstract
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!Mage::getStoreConfig('ajax_pro/catalogProductView/enabled')) {
            return $this;
        }
        /* @var $block TM_AjaxPro_Block_Head */
        $block = $this->getLayout()->getBlock('ajaxpro.head');
        if (!$block) {
            return $this;
        }
        // tm_ajaxpro_catalog_product_view handle
        $block->addItem('js', 'varien/product.js')
            ->addItem('skin_js', 'js/bundle.js')
            ->addItem('js_css', 'calendar/calendar-win2k-1.css')
            ->addItem('js', 'calendar/calendar.js')
            ->addItem('js', 'calendar/calendar-setup.js')
        ;

        return $this;
    }

    public function getCurrentUrl()
    {
        return Mage::getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true));
    }

    protected function _getRequest()
    {
        if (!$this->_request) {
            $this->_request = Mage::app()->getRequest();
        }
        return $this->_request;
    }

    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = array())
    {
        if (!Mage::getStoreConfig('ajax_pro/general/enabled')
            || !Mage::getStoreConfig('ajax_pro/catalogProductView/enabled')
            || TM_AjaxPro_Model_UserAgent::isSearchBot()
            || (TM_AjaxPro_Model_UserAgent::isMobile()
                    && Mage::getStoreConfig('ajax_pro/general/disabledOnMobileDevice'))
            ) {
            return parent::getAddToCartUrl($product, $additional);
        }

        if ($this->helper('core')->isModuleOutputEnabled('Sitewards_B2BProfessional')) {
            if (!$this->helper('sitewards_b2bprofessional')->isProductActive($product)) {
                return parent::getAddToCartUrl($product, $additional);
            }
        }

        if (defined('Mage_Core_Model_Url::FORM_KEY')) {
            $formKey = Mage::getSingleton('core/session')->getFormKey();
            if (!empty($formKey)) {
                $additional = array_merge(
                    $additional,
                    array(Mage_Core_Model_Url::FORM_KEY => $formKey)
                );
            }
        }

        $typeInstance = $product->getTypeInstance(true);
        $productTypeId = $product->getTypeId();

        if (Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE == $productTypeId
            && !$typeInstance->getProduct($product)->getLinksPurchasedSeparately()
            ) {

            $_product = $product->load($product->getId());

            $_options = $typeInstance->getProduct($_product)->getOptions();
            if (0 == count($_options)) {
                return $this->helper('checkout/cart')->getAddUrl($product, $additional);
            }
        }

        if ($typeInstance->hasRequiredOptions($product)
            || $typeInstance->hasOptions($product)
            || Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE === $productTypeId) {

            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = array();
            }
            $additional['_query']['options'] = 'cart';

            $_url = $product->getUrl();
            $product->setUrl(null);
            $product->setData('request_path', null);
            $url = $this->getProductUrl($product, $additional);
            $product->setUrl($_url);
            return $url;
        }

        return parent::getAddToCartUrl($product, $additional);
    }
}