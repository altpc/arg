<?php
class TM_LightboxPro_Model_System_Config_Source_Gallery_NumbersPosition
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0,         'label' => Mage::helper('lightboxpro')->__('None')), 
            array('value' => 'heading', 'label' => Mage::helper('lightboxpro')->__('heading')),
            array('value' => 'caption', 'label' => Mage::helper('lightboxpro')->__('caption')) 
        );
    }

}
