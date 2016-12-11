<?php

class TM_Argento_Block_Page_Html_Footer extends Mage_Page_Block_Html_Footer
{
    /**
     * $this->getTemplate added
     */
    public function getCacheKeyInfo()
    {
        return array(
            'PAGE_FOOTER',
            Mage::app()->getStore()->getId(),
            (int)Mage::app()->getStore()->isCurrentlySecure(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            Mage::getSingleton('customer/session')->isLoggedIn(),
            $this->getTemplate()
        );
    }
}
