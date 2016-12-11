<?php
class TM_LightboxPro_Model_System_Config_Source_TitleSource
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'this.a.title',     'label' => Mage::helper('lightboxpro')->__('Anchor\'s title ')),
            array('value' => 'this.thumb.alt',   'label' => Mage::helper('lightboxpro')->__('Thumbnail\'s alt')),
            array('value' => 'this.thumb.title', 'label' => Mage::helper('lightboxpro')->__('Thumbnail\'s title')),
            array('value' => '',                 'label' => Mage::helper('lightboxpro')->__('Subsequent div'))
        );
    }

}
