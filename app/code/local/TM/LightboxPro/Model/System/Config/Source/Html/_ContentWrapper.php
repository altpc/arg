<?php
class TM_LightboxPro_Model_System_Config_Source_Html_ContentWrapper
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('lightboxpro')->__('Icons only')),
            array('value' => 1, 'label' => Mage::helper('lightboxpro')->__('Text only')),
            array('value' => 2, 'label' => Mage::helper('lightboxpro')->__('Icons and Text')),
        );
    }

}
