<?php
class TM_LightboxPro_Model_System_Config_Source_Html_TitleSource
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('lightboxpro')->__('None')),
            array('value' => 1, 'label' => Mage::helper('lightboxpro')->__('A given string')),
            array('value' => 2, 'label' => Mage::helper('lightboxpro')->__('Anchor\'rs title attribute')),
            array('value' => 3, 'label' => Mage::helper('lightboxpro')->__('Anchor\'rs inner HTML')),
        );
    }

}
