<?php

class TM_Highlight_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Retreive highlight page urls as associative array
     *     PAGE_TYPE => url_key
     *
     * @return array
     */
    public function getPageUrls()
    {
        $urls = array();
        $config = Mage::getStoreConfig('highlight/pages');
        foreach ($config as $key => $value) {
            if (!strpos($key, '_url')) {
                continue;
            }
            $urls[str_replace('_url', '', $key)] = $value;
        }
        return $urls;
    }

    /**
     * Retrieve page url for specific block type. See the PAGE_TYPE constant
     * in some of highlight blocks
     *
     * @param  string $type
     * @return string
     */
    public function getPageUrlKey($type)
    {
        return Mage::getStoreConfig("highlight/pages/{$type}_url");
    }
}
