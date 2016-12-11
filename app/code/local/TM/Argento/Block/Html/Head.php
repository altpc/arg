<?php

class TM_Argento_Block_Html_Head extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $this->addData(array(
            'cache_lifetime' => 86400
        ));
        $this->_data['links'] = array();
    }

    public function getCacheKeyInfo()
    {
        $design = Mage::getDesign();
        return array(
            'TM_ARGENTO',
            Mage::app()->getStore()->getId(),
            Mage::app()->getStore()->isCurrentlySecure(),
            $design->getPackageName(),
            $design->getTheme('template')
        );
    }

    protected function _prepareLayout()
    {
        $head = $this->getLayout()->getBlock('head');
        if (!$head) {
            return $this;
        }

        $design = Mage::getDesign();
        $argentoSkin = $design->getSkinBaseUrl(array(
            '_theme'   => 'default',
            '_package' => 'argento'
        ));
        $argentoSkin = trim($argentoSkin, '/ ');
        $argentoSkin = str_replace('/argento/default', '/argento', $argentoSkin);
        $customFiles = array(
            'skin_css' => array(
                'css/custom.css'
            ),
            'skin_js' => array(
                'js/custom.js'
            )
        );
        foreach ($customFiles as $type => $files) {
            foreach ($files as $file) {
                $fileUrl = $design->getSkinUrl($file);
                // include file if it was found inside skin/argento folder
                if (strstr($fileUrl, $argentoSkin)) {
                    $head->addItem($type, $file, "");
                }
            }
        }
    }

    public function getLinks()
    {
        $design = Mage::getDesign();
        $theme  = $design->getPackageName() . '_' . $design->getTheme('template');
        $linksCount = count($this->_data['links']);
        return Mage::getStoreConfig($theme . '/head/link')
            . Mage::getStoreConfig($theme . '/font/head_link')
            . ($linksCount ? implode("\n", $this->_data['links'])."\n" : "");
    }

    /**
     * Finds backend.css to use for current argento theme
     *
     * Fallback rules are used to support Magento's configuration descending:
     *  media/[package]/[theme]/[website_store]_backend.css
     *  media/[package]/[theme]/[website]_backend.css
     *  media/[package]/[theme]/0_backend.css
     *
     * @return string of false
     */
    public function getBackendCss()
    {
        $design      = Mage::getDesign();
        $theme       = $design->getPackageName() . '_' . $design->getTheme('template');
        $storeCode   = Mage::app()->getStore()->getCode();
        $websiteCode = Mage::app()->getWebsite()->getCode();
        $args = array(
            array($theme, $storeCode, $websiteCode),
            array($theme, null, $websiteCode),
            array($theme, null, null)
        );
        $mediaDir = Mage::getBaseDir('media');
        $css      = Mage::getModel('argento/css');
        foreach ($args as $_args) {

            $filePath = $css->getFilePath($_args[0], $_args[1], $_args[2]);
            if (file_exists($mediaDir . DS . $filePath)) {
                $isSecure = $this->getRequest()->isSecure();
                $url = rtrim(Mage::getBaseUrl('media', $isSecure), '/')
                    . '/'
                    . str_replace(DS, '/', $filePath);
                $objectBackendCss = new Varien_Object(array(
                    'url' => $url,
                    'media_dir' => $mediaDir,
                    'file_path' => $filePath
                ));
                Mage::dispatchEvent(
                    'argento_block_head_backend_css_after',
                    array('backend_css' => $objectBackendCss)
                );
                return $objectBackendCss->getUrl();
            }

        }
        return false;
    }

    /**
     * Add Link element to HEAD entity
     *
     * @param string $rel forward link types
     * @param string $href URI for linked resource
     * @return TM_Argento_Block_Html_Head
     */
    public function addLink($rel, $href)
    {
        $this->_data['links'][$rel.'/'.$href] =
            sprintf('<link rel="%s" href="%s" />', $rel, $href);
        return $this;
    }
}
