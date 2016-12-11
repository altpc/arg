<?php

class TM_ReviewReminder_Model_Config_OrderStatus extends Varien_Object
{
    public function toOptionArray()
    {
        $statuses = Mage::getModel('sales/order_config')->getStatuses();
        $values = array();
        foreach ($statuses as $code => $label) {
            $values[] = array(
                'label' => Mage::helper('reports')->__($label),
                'value' => $code
            );
        }
        return $values;
    }
}