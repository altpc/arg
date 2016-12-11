<?php

class TM_SoldTogether_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCheckoutSuccessColCount()
    {
        return Mage::getStoreConfig('checkoutsuccess/mockupsets/soldtogether_columns');
    }

    public function getCheckoutSuccessProductsCount()
    {
        return Mage::getStoreConfig('checkoutsuccess/mockupsets/soldtogether_productscount');
    }
}
