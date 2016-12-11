<?php

class TM_Email_Model_System_Config_Source_Gateway_Transport_Secure
{

    static public function toOptionHash()
    {
        return array(
            TM_Email_Model_Gateway_Transport::SECURE_NONE => 'None',
            TM_Email_Model_Gateway_Transport::SECURE_SSL  => 'SSL',
            TM_Email_Model_Gateway_Transport::SECURE_TLS  => 'TLS',
        );
    }

    /**
     * Options getter
     *
     * @return array
     */
    static public function toOptionArray()
    {
        $return = array();
        $helper = Mage::helper('tm_email');
        foreach (self::toOptionHash() as $key => $value) {
            $return[] = array(
                'value' => $key,
                'label' => $helper->__($value)
            );
        }

        return $return;
    }
}
