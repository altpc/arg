<?php

abstract class TM_LightboxPro_Block_Abstract extends Mage_Core_Block_Template
{

    protected $_hsConfig;

    /**
     * Get Highslide config string
     * @return string
     */
    public function getHsConfig()
    {
        if (!$this->_hsConfig) {
            $this->_hsConfig = Mage::helper('lightboxpro')->configAsDataString();
        }
        return $this->_hsConfig;
    }

    public function getCssClass()
    {
        if ($this->getHsConfig()) {
            return Mage::helper('lightboxpro')->getHsConfigCssClassName();
        }
        return;
    }
}
