<?php
class TM_LightboxPro_Model_System_Config_Source_Behavior_Easing
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0,             'label' => Mage::helper('lightboxpro')->__('easyInQuad')),
            array('value' => 'linearTween', 'label' => Mage::helper('lightboxpro')->__('linearTween')),
            array('value' => 'easeInCirc',  'label' => Mage::helper('lightboxpro')->__('easyInCirc')),
            array('value' => 'easeInBack',  'label' => Mage::helper('lightboxpro')->__('easyInBack/easeOutBack')),
        );
    }

}
