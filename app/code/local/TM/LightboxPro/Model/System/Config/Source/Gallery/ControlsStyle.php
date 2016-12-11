<?php
class TM_LightboxPro_Model_System_Config_Source_Gallery_ControlsStyle
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0,                     'label' => Mage::helper('lightboxpro')->__('None')), //useControls: false,
            array('value' => 1,                     'label' => Mage::helper('lightboxpro')->__('Large white buttons')), //useControls: true, fixedControls: 'fit',
            array('value' => 'controls-in-heading', 'label' => Mage::helper('lightboxpro')->__('Small white buttons')), // +className: 'controls-in-heading',
            array('value' => 'large-dark',          'label' => Mage::helper('lightboxpro')->__('Large dark buttons')), // +className: 'large-dark',
            array('value' => 'text-controls',       'label' => Mage::helper('lightboxpro')->__('White buttons with text')) // + className: 'text-controls',
        );
    }

}
