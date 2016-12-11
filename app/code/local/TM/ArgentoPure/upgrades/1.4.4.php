<?php

class TM_ArgentoPure_Upgrade_1_4_4 extends TM_Core_Model_Module_Upgrade
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
