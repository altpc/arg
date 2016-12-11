<?php

class TM_Argento_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isArgentoAndEnterpriseUsed()
    {
        return Mage::helper('tmcore')->isDesignPackageEquals('argento')
            && $this->isEnterpriseUsed();
    }

    public function isEnterprise()
    {
        return (bool)Mage::getConfig()->getModuleConfig('Enterprise_Enterprise');
    }

    public function isEnterpriseUsed()
    {
        if (!$this->isEnterprise()) {
            return false;
        }

        $package = Mage::getSingleton('core/design_package');
        $themes  = $package->getTheme('after_default');
        if ($themes && $themes !== $package->getTheme('default')) {
            $themes = explode(',', $themes);
            return in_array('enterprise/default', $themes);
        }
        return false;
    }

    /**
     * Returns logo url with @2x suffix
     *
     * @return string
     */
    public function getLogo2xSrc()
    {
        $header = Mage::app()->getLayout()->getBlock('header');
        $logoSrc = '';
        if ($header) {
            $logoSrc = $header->getData('logo_src');
        }

        if (empty($logoSrc)) {
            $logoSrc = Mage::getStoreConfig('design/header/logo_src');
        }
        if (!empty($logoSrc)) {
            $pathinfo = pathinfo($logoSrc);
            $logo2xSrc = $pathinfo['dirname']
                . '/'
                . $pathinfo['filename']
                . '@2x.'
                . $pathinfo['extension'];

            $logoUrl = Mage::getDesign()->getSkinUrl($logo2xSrc, array());
            if (false === strpos($logoUrl, 'skin/frontend/base/default/')) {
                // logo2x is found in some theme
                return $logoUrl;
            }
        }
        return false;
    }

    /**
     * Used in product list template to show "add to cart" or "view details" button.
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return boolean
     */
    public function canShowAddToCart($product)
    {
        $coreHelper = Mage::helper('core');
        if ($coreHelper->isModuleOutputEnabled('Sitewards_B2BProfessional')) {
            if (!Mage::helper('sitewards_b2bprofessional')->isProductActive($product)) { // hide add to cart
                return false;
            }
        }
        if ($coreHelper->isModuleOutputEnabled('TM_AjaxPro')) {
            if (Mage::getStoreConfig('ajax_pro/general/enabled')
                && Mage::getStoreConfig('ajax_pro/catalogProductView/enabled')) { // can add configurable products

                return true;
            }
        }
        return !$product->canConfigure() && $product->isSaleable();
    }
}
