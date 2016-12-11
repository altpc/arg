<?php

class TM_ArgentoMall_Upgrade_1_1_0 extends TM_Core_Model_Module_Upgrade
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
