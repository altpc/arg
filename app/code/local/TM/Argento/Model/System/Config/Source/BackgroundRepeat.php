<?php

class TM_Argento_Model_System_Config_Source_BackgroundRepeat
{
    protected $_values = array(
        'no-repeat' => 'no-repeat',
        'repeat'    => 'repeat',
        'repeat-x'  => 'repeat-x',
        'repeat-y'  => 'repeat-y'
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
