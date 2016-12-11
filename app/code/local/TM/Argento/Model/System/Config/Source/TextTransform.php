<?php

class TM_Argento_Model_System_Config_Source_TextTransform
{
    protected $_values = array(
        'none'       => 'none',
        'uppercase'  => 'uppercase',
        'capitalize' => 'capitalize',
        'lowercase'  => 'lowercase'
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
