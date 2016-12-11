<?php

class TM_EasyBanner_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getBannerClassName($name)
    {
        return 'banner-' . $this->cleanupName($name);
    }

    public function getPlaceholderClassName($name)
    {
        return 'placeholder-' . $this->cleanupName($name);
    }

    public function cleanupName($name)
    {
        return preg_replace('/[^a-z0-9_]+/i', '-', $name);
    }
}
