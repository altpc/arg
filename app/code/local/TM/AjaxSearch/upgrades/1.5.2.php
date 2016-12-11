<?php

class TM_AjaxSearch_Upgrade_1_5_2 extends TM_Core_Model_Module_Upgrade
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
            'tm_ajaxsearch/general' => array(
                'enabled'              => 1,
                'show_category_filter' => 1,
                'attributes'           => 'name,sku'
            )
        );
    }
}
