<?php

class TM_EasyBanner_Model_Mysql4_Layout_Link extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('easybanner/layout_link', 'layout_link_id');
    }
}