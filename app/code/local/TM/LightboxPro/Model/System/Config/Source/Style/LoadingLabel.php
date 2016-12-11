<?php
class TM_LightboxPro_Model_System_Config_Source_Style_LoadingLabel
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('lightboxpro')->__('White: icon and text')),
            array('value' => 1, 'label' => Mage::helper('lightboxpro')->__('White: text only')),
            array('value' => 2, 'label' => Mage::helper('lightboxpro')->__('White: small icon')),
            array('value' => 3, 'label' => Mage::helper('lightboxpro')->__('White: big icon')),
            array('value' => 4, 'label' => Mage::helper('lightboxpro')->__('Black: icon and text')),
            array('value' => 5, 'label' => Mage::helper('lightboxpro')->__('Black: text only')),
            array('value' => 6, 'label' => Mage::helper('lightboxpro')->__('Black: small icon')),
            array('value' => 7, 'label' => Mage::helper('lightboxpro')->__('Black: big icon')),
            
        );
    }

}
