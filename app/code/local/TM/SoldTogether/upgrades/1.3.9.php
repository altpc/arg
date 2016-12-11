<?php

class TM_SoldTogether_Upgrade_1_3_9 extends TM_Core_Model_Module_Upgrade
{

    public function getOperations()
    {
        return array(
            'configuration' => $this->_getConfiguration(),
        );
    }

    private function _getConfiguration()
    {
        return array(
            'soldtogether' => array(
                'general' => array(
                    'enabled' => 1,
                    'random'  => 1
                ),
                'order' => array(
                    'enabled'           => 1,
                    'addtocartcheckbox' => 0,
                    'amazonestyle'      => 1
                ),
                'customer/enabled' => 1
            )
        );
    }
}
