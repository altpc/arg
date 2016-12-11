<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Model_Store extends Mage_Catalog_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('prolabels/store');
    }
}
