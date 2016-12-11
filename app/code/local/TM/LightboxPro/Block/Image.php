<?php
class TM_LightboxPro_Block_Image extends TM_LightboxPro_Block_Abstract
    implements Mage_Widget_Block_Interface
{
    protected $_template = 'tm/lightboxpro/image.phtml';

    public function getImage()
    {
        $path  = $this->getData('path');
        return Mage::helper('lightboxpro/image')->setImage($path);
    }

    public function getThumbnailImage()
    {
        return $this
            ->getImage()
            ->resize(
                $this->getThumbnailWidth(),
                $this->getThumbnailHeight()
            );
    }

    public function getThumbnailWidth()
    {
        list($w, $h) = explode(
            'x',
            Mage::getStoreConfig('lightboxpro/size/thumbnail')
        );
        if ($this->getData('thumbnailwidth')) {
            $w = $this->getData('thumbnailwidth');
        }
        return $w;
    }

    public function getThumbnailHeight()
    {
        list($w, $h) = explode(
            'x',
            Mage::getStoreConfig('lightboxpro/size/thumbnail')
        );
        if ($this->getData('thumbnailheight')) {
            $h = $this->getData('thumbnailheight');
        }
        return $h;
    }
}