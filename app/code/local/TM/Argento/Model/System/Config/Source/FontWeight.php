<?php

class TM_Argento_Model_System_Config_Source_FontWeight
{
    protected $_values = array(
        'normal' => 'normal',
        'bold'   => 'bold'
    );

    public function toOptionArray()
    {
        $result  = array();
        foreach ($this->_values as $value => $label) {
            $result[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $result;
    }
}
