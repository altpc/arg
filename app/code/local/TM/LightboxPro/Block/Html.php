<?php
class TM_LightboxPro_Block_Html extends TM_LightboxPro_Block_Abstract
    implements Mage_Widget_Block_Interface
{
    protected $_template = 'tm/lightboxpro/html.phtml';

    public function customConfigsAsDataStr(){
        $custom = array();
        if ($this->getWidth()) {
            $custom['width'] = $this->getWidth();
            $custom['maxWidth'] = $this->getWidth();
        }
        $custom['wrapperClassName'] = 'draggable-header';
        return Mage::helper('lightboxpro')->arrayToDataString(
            array(
                'custom-configs' =>
                Mage::helper('lightboxpro')->arrayToJson($custom)
            )
        );
    }

}
