<?php

class TM_EasyBanner_Model_Placeholder extends Mage_Core_Model_Abstract
{
    const LAYOUT_NAME_PREFIX = 'easybanner.placeholder.';

    const MODE_ROTATOR    = 'rotator';
    const MODE_LIGHTBOX   = 'lightbox';
    const MODE_AWESOMEBAR = 'awesomebar';

    protected function _construct()
    {
        parent::_construct();
        $this->_init('easybanner/placeholder');
    }

    public function getBannerIds($isActive = false)
    {
        $key = $isActive ? 'banner_ids_active' : 'banner_ids';
        $ids = $this->_getData($key);
        if (is_null($ids)) {
            $this->_getResource()->loadBannerIds($this, $isActive);
            $ids = $this->getData($key);
        }
        return $ids;
    }

    public function getIsRandomSortMode()
    {
        return 'random' === $this->getSortMode();
    }

    public function isPopupMode()
    {
        return in_array($this->getMode(), array(
            self::MODE_LIGHTBOX,
            self::MODE_AWESOMEBAR
        ));
    }
}