<?php

class TM_AskIt_Model_Item_Type   extends Varien_Object
{
    const PRODUCT_ID	      = 1;
    const PRODUCT_CATEGORY_ID = 2;
    const CMS_PAGE_ID	      = 3;
//    const CMS_CATEGORY_ID     = 4;


    static public function toOptionHash()
    {
        return array(
            self::PRODUCT_ID          => Mage::helper('askit')->__('Product'),
            self::PRODUCT_CATEGORY_ID => Mage::helper('askit')->__('Category'),
            self::CMS_PAGE_ID         => Mage::helper('askit')->__('Page')
        );
    }
}