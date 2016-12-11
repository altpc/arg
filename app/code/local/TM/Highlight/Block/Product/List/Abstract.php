<?php

if (Mage::helper('core')->isModuleOutputEnabled('TM_AjaxPro')) {
    class TM_Highlight_Block_Product_List_Abstract extends TM_AjaxPro_Block_Product_List {}
} else {
    class TM_Highlight_Block_Product_List_Abstract extends Mage_Catalog_Block_Product_List {}
}
