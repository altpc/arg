<?php
class TM_ProLabels_Adminhtml_Model_System_Config_Source_Position
{
     public function toOptionArray()
    {
        return array(
            array('value'=>'top-left'     , 'label'=>Mage::helper('prolabels')->__('top-left')),
            array('value'=>'top-center'   , 'label'=>Mage::helper('prolabels')->__('top-center')),
            array('value'=>'top-right'    , 'label'=>Mage::helper('prolabels')->__('top-right')),
            array('value'=>'middle-left'  , 'label'=>Mage::helper('prolabels')->__('middle-left')),
            array('value'=>'middle-center', 'label'=>Mage::helper('prolabels')->__('middle-center')),
            array('value'=>'middle-right' , 'label'=>Mage::helper('prolabels')->__('middle-right')),
            array('value'=>'bottom-left'  , 'label'=>Mage::helper('prolabels')->__('bottom-left')),
            array('value'=>'bottom-center', 'label'=>Mage::helper('prolabels')->__('bottom-center')),
            array('value'=>'bottom-right' , 'label'=>Mage::helper('prolabels')->__('bottom-right'))
        );
    }
}
