<?php

class TM_ArgentoPure_Upgrade_1_4_5 extends TM_Core_Model_Module_Upgrade
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
            'catalog/product_image/small_width' => 200
        );
    }
}
