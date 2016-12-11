<?php

class TM_AskIt_Upgrade_2_2_1 extends TM_Core_Model_Module_Upgrade
{

    public function getOperations()
    {
        return array(
            'configuration' => $this->_getConfiguration(),
        );
    }

    private function _getConfiguration()
    {
        return array('askit/general/enabled' => 1);
    }
}
