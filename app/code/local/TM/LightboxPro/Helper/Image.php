<?php
class TM_LightboxPro_Helper_Image extends Mage_Catalog_Helper_Image
{
    public function setImage($image)
    {
        $this->_reset();
        $this->_setModel(Mage::getModel('lightboxpro/image'));
        $this->_getModel()->setDestinationSubdir('cache');
        $this->setImageFile($image);
        return $this;
    }
}