<?php

class TM_Email_Model_System_Config_Source_Gateway_Storage
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $return = Mage::getModel('tm_email/gateway_storage')->toOptionArray();
        array_unshift($return, array(
            'value' => '',
            'label' => ''
        ));

        return $return;
    }
}
