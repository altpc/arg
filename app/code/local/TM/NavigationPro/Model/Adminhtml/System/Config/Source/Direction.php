<?php

class TM_NavigationPro_Model_Adminhtml_System_Config_Source_Direction
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => TM_NavigationPro_Model_Column::DIRECTION_HORIZONTAL,
                'label' => Mage::helper('navigationpro')->__('Horizontal')
            ),
            array(
                'value' => TM_NavigationPro_Model_Column::DIRECTION_VERTICAL,
                'label' => Mage::helper('navigationpro')->__('Vertical')
            )
        );
    }
}
