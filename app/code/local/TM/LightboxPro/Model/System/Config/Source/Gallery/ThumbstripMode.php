<?php
class TM_LightboxPro_Model_System_Config_Source_Gallery_ThumbstripMode
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'horizontal', 'label' => Mage::helper('lightboxpro')->__('Horizontal')), 
            array('value' => 'vertical',   'label' => Mage::helper('lightboxpro')->__('Vertical')),
            array('value' => 'float',      'label' => Mage::helper('lightboxpro')->__('Float')) 
        );
    }

}
