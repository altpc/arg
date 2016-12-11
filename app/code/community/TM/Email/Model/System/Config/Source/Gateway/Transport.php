<?php

class TM_Email_Model_System_Config_Source_Gateway_Transport
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $return = Mage::getModel('tm_email/gateway_transport')->toOptionArray();
        array_unshift($return, array(
            'value' => '',
            'label' => Mage::helper('tm_email')->__('Default Sendmail Sender')
        ));

        return $return;
    }
}
