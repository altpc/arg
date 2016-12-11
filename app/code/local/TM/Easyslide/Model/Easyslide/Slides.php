<?php

class TM_Easyslide_Model_Easyslide_Slides extends Mage_Core_Model_Abstract
{
    const TARGET_SELF  = 0;
    const TARGET_BLANK = 1;
    const TARGET_POPUP = 2;

    const DESCRIPTION_TOP    = 1;
    const DESCRIPTION_RIGHT  = 2;
    const DESCRIPTION_BOTTOM = 3;
    const DESCRIPTION_LEFT   = 4;
    const DESCRIPTION_CENTER = 5;

    const BACKGROUND_LIGHT       = 1;
    const BACKGROUND_DARK        = 2;
    const BACKGROUND_TRANSPARENT = 3;

    public function _construct()
    {
        parent::_construct();
        $this->_init('easyslide/easyslide_slides');
    }

    public function getTargetModes()
    {
        return array(
            self::TARGET_SELF  => Mage::helper('easyslide')->__('Same window'),
            self::TARGET_BLANK => Mage::helper('easyslide')->__('New window'),
            self::TARGET_POPUP => Mage::helper('easyslide')->__('Popup')
        );
    }
}