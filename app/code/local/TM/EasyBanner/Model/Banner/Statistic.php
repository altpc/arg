<?php

class TM_EasyBanner_Model_Banner_Statistic extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('easybanner/banner_statistic');
    }
}