<?php

class TM_ProLabels_Model_System_Config_Source_Position {
	public function toOptionArray() {
		return array(
                array(
                        'value' => 'top-left',
                        'label' => Mage::helper('adminhtml')->__('top-left')
                ),
                array(
                        'value' => 'top-center',
                        'label' => Mage::helper('adminhtml')->__('top-center')
                ),
                array(
                        'value' => 'top-right',
                        'label' => Mage::helper('adminhtml')->__('top-right')
                ),
                array(
                        'value' => 'middle-left',
                        'label' => Mage::helper('adminhtml')->__('middle-left')
                ),
                array(
                        'value' => 'middle-center',
                        'label' => Mage::helper('adminhtml')->__('middle-center')
                ),
                array(
                        'value' => 'middle-right',
                        'label' => Mage::helper('adminhtml')->__('middle-right')
                ),
                array(
                        'value' => 'bottom-left',
                        'label' => Mage::helper('adminhtml')->__('bottom-left')
                ),
                array(
                        'value' => 'bottom-center',
                        'label' => Mage::helper('adminhtml')->__('bottom-center')
                ),
                array(
                        'value' => 'bottom-right',
                        'label' => Mage::helper('adminhtml')->__('bottom-right')
                )
            );
	}
}