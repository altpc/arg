<?php

class TM_ArgentoPure_Upgrade_1_3_1 extends TM_Core_Model_Module_Upgrade
{
    public function getOperations()
    {
        if (!Mage::helper('argento')->isEnterprise()) {
            return array();
        }
        return array(
            'configuration' => array(
                'design/theme/after_default' => 'enterprise/default'
            )
        );
    }
}
