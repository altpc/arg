<?php

class TM_ArgentoArgento_Upgrade_1_4_6 extends TM_Core_Model_Module_Upgrade
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
            'lightboxpro/size/popup' => '0x0'
        );
    }
}
