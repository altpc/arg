<?php
class TM_LightboxPro_Model_System_Config_Source_Relative
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'image',    'label' => Mage::helper('lightboxpro')->__('Image')),
            array('value' => 'viewport', 'label' => Mage::helper('lightboxpro')->__('Viewport')),
            array('value' => 'expander', 'label' => Mage::helper('lightboxpro')->__('Expander'))
        );
    }
}
