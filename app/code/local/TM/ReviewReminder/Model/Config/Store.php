<?php

class TM_ReviewReminder_Model_Config_Store extends Varien_Object
{
     public function toOptionArray() {
        return Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);
    }
}