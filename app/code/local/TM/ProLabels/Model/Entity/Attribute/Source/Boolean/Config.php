<?php
class TM_ProLabels_Model_Entity_Attribute_Source_Boolean_Config extends Mage_Eav_Model_Entity_Attribute_Source_Boolean
{
    public function getAllOptions()
    {
        if (!$this->_options)
        {
            $this->_options = array(
                array(
                    'label' => Mage::helper('prolabels')->__('No'),
                    'value' =>  0
                ),
                array(
                    'label' => Mage::helper('prolabels')->__('Yes'),
                    'value' =>  1
                )
            );
        }
        return $this->_options;
    }
}
