<?php
/* <!-- AjaxSearch --> */
class TM_AjaxSearch_Helper_Data extends Mage_CatalogSearch_Helper_Data
{

    /**
     * generate JSON string with AjaxSearch config
     * @param  array  $additional aadditional settings
     * @return string
     */
    public function getConfigAsJsonString($additional = array())
    {
        $config = array(
            'serviceUrl' => $this->_getUrl(
                'ajaxsearch',
                array('_secure' => $this->_getRequest()->isSecure())
            ),
            'enableloader' => (bool)$this->getConfig('general/enableloader'),
            'minChars' => (int)$this->getConfig('general/minchars'),
            'maxHeight' => $this->getConfig('general/maxheight'),
            'width' => $this->getConfig('general/width'),
            'searchtext' => $this->getConfig('general/searchfieldtext')
        );
        return json_encode(array_merge($config, $additional));
    }

    public function getConfig($key)
    {
        return Mage::getStoreConfig('tm_ajaxsearch/' . $key);
    }
}
