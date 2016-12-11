<?php

class TM_ArgentoMall_Upgrade_1_0_1 extends TM_Core_Model_Module_Upgrade
{
    public function getOperations()
    {
        return array(
            'configuration' => $this->_getConfiguration()
        );
    }

    private function _getConfiguration()
    {
        return array(
            'testimonials/general/enabled' => 1
        );
    }
}
