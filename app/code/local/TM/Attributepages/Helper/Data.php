<?php

class TM_Attributepages_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @deprecated Use the TM_Attributepages_Helper_Product instead
     */
    public function loadOptionByProductAndAttributeCode(
        Mage_Catalog_Model_Product $product, $attributeCode, $parentPageIdentifier = null)
    {
        Mage::log('You are using deprecated attributepages helper. Follow the new installation instructions please');

        Mage::helper('attributepages/product')->appendPages(
            $product, $attributeCode, $parentPageIdentifier
        );
        $options = $product->getAttributepages();
        if (!$options || empty($options[$attributeCode])) {
            return false;
        }
        return $options[$attributeCode];
    }

    public function canUseLayeredNavigation()
    {
        if (!Mage::getStoreConfigFlag('attributepages/product_list/use_layered_navigation')) {
            return false;
        }

        // for now we didn't test compatibility with third party extensions,
        // so just disable layer if it's not the magento standard navigation
        $layer = Mage::getModel('catalog/layer');
        $layerClassName = get_class($layer);
        $supportedClassNames = array(
            'Mage_Catalog_Model_Layer',
            'TM_AjaxLayeredNavigation_Model_Layer',
            'PKR_Catalog_Model_Layer' // sorting module
        );
        if (!in_array($layerClassName, $supportedClassNames)) {
            return false;
        }

        $filter = Mage::getModel('catalog/layer_filter_attribute');
        $allowedClassNames = array(
            'Mage_Catalog_Model_Layer_Filter_Attribute',
            'MageWorx_SeoFriendlyLN_Model_Catalog_Layer_Filter_Attribute'
        );
        if (!in_array(get_class($filter), $allowedClassNames)) {
            return false;
        }

        $helper = Mage::helper('core');
        $unsupportedModules = array('Amasty_Shopby');
        foreach ($unsupportedModules as $moduleName) {
            if ($helper->isModuleOutputEnabled($moduleName)) {
                return false;
            }
        }
        return true;
    }
}
