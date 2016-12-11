<?php
class TM_ProLabels_Adminhtml_Model_System_Config_Source_RoundMethod
{
     public function toOptionArray()
    {
        return array(
            array('value'=>'round'  , 'label'=>Mage::helper('prolabels')->__('Math')),
            array('value'=>'ceil', 'label'=>Mage::helper('prolabels')->__('Ceil')),
            array('value'=>'floor' , 'label'=>Mage::helper('prolabels')->__('Floor'))
        );
    }
}
