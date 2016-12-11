<?php

if (Mage::helper('core')->isModuleOutputEnabled('Fooman_SpeedsterAdvanced')) {
    class TM_Argento_Model_Core_Design_PackageAbstract extends Fooman_SpeedsterAdvanced_Model_Core_Design_Package {}
} else {
    class TM_Argento_Model_Core_Design_PackageAbstract extends Mage_Core_Model_Design_Package {}
}
