<?php

class TM_ProLabels_Upgrade_2_7_2 extends TM_Core_Model_Module_Upgrade
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
            'prolabels/general' => array(
                'enabled' => 1,
                'mobile'  => 0
            )
        );
    }
}
