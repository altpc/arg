<?php
class TM_LightboxPro_Model_System_Config_Source_Behavior_Aligment
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0,              'label' => Mage::helper('lightboxpro')->__('Auto')),
            array('value' => 'center',       'label' => Mage::helper('lightboxpro')->__('Center')),
            array('value' => 'top',          'label' => Mage::helper('lightboxpro')->__('Thumbnail top')),
            array('value' => 'top-rigth',    'label' => Mage::helper('lightboxpro')->__('Thumbnail top right')),            
            array('value' => 'right',        'label' => Mage::helper('lightboxpro')->__('Thumbnail right')),
            array('value' => 'bottom-right', 'label' => Mage::helper('lightboxpro')->__('Thumbnail bottom right')),
            array('value' => 'bottom',       'label' => Mage::helper('lightboxpro')->__('Thumbnail bottom')),
            array('value' => 'bottom-left',  'label' => Mage::helper('lightboxpro')->__('Thumbnail bottom left')),
            array('value' => 'left',         'label' => Mage::helper('lightboxpro')->__('Thumbnail left')),
            array('value' => 'top-left',     'label' => Mage::helper('lightboxpro')->__('Thumbnail top left')),
            
        );
    }

}
