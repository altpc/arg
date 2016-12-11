<?php
class TM_LightboxPro_Model_System_Config_Source_Style_ExplanationLabel
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
            array('value' => 1, 'label' => Mage::helper('lightboxpro')->__('Text below')),
            array('value' => 2, 'label' => Mage::helper('lightboxpro')->__('Icon and text below')),
            
        );
    }

}
