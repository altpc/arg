<?php

class TM_Email_Model_System_Config_Source_Gateway_Transport_Auth
{

    static public function toOptionHash()
    {
        return array(
            TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
            TM_Email_Model_Gateway_Transport::AUTH_PLAIN,
            TM_Email_Model_Gateway_Transport::AUTH_CRAMMD5
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
        foreach (self::toOptionHash() as $value) {
            $return[] = array(
                'value' => $value,
                'label' => ucfirst($value)
            );
        }

        return $return;
    }
}
