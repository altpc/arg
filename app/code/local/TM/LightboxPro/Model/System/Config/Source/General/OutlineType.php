<?php
class TM_LightboxPro_Model_System_Config_Source_General_OutlineType
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'beveled',       'label' => Mage::helper('lightboxpro')->__('beveled')),
            array('value' => 'drop-shadow',   'label' => Mage::helper('lightboxpro')->__('drop-shadow')),
            array('value' => 'glossy-dark',   'label' => Mage::helper('lightboxpro')->__('glossy-dark')),
            array('value' => 'outer-glow',    'label' => Mage::helper('lightboxpro')->__('outer-glow')),
            array('value' => 'rounded-black', 'label' => Mage::helper('lightboxpro')->__('rounded-black')),
            array('value' => 'rounded-white', 'label' => Mage::helper('lightboxpro')->__('rounded-white'))
        );
    }

}
