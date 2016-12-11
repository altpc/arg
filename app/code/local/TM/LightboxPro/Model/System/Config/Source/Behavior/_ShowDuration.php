<?php
class TM_LightboxPro_Model_System_Config_Source_Behavior_ShowDuration
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('lightboxpro')->__('Never')),
            array('value' => 1, 'label' => Mage::helper('lightboxpro')->__('Always')),
            array('value' => 2, 'label' => Mage::helper('lightboxpro')->__('HTML expanders only'))
        );
    }

}
