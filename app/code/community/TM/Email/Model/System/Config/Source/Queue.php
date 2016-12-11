<?php

class TM_Email_Model_System_Config_Source_Queue
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $return = Mage::getModel('tm_email/queue_queue')->toOptionArray();
        array_unshift($return, array(
            'value' => '',
            'label' => ''
        ));

        return $return;
    }
}
