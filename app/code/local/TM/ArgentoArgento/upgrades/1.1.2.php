<?php

class TM_ArgentoArgento_Upgrade_1_1_2 extends TM_Core_Model_Module_Upgrade
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
            'facebooklb' => array(
                'category_products' => array(
                    'enabled'   => 0,
                    'send'      => 0,
                    'layout'    => 'button_count',
                    'showfaces' => 0,
                    'width'     => 350,
                    'color'     => 'light'
                ),
                'productlike' => array(
                    'enabled'   => 1,
                    'send'      => 1,
                    'layout'    => 'button_count',
                    'showfaces' => 0,
                    'width'     => 350,
                    'color'     => 'light'
                )
            )
        );
    }
}
