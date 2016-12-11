<?php

class TM_Attributepages_Helper_Image extends Mage_Core_Helper_Abstract
{
    const CACHE_FOLDER = 'cache';

    /**
     * The page to take image from
     *
     * @var TM_Attributepages_Model_Entity
     */
    protected $_entity;

    /**
     * Image to resize
     *
     * @var string
     */
    protected $_mode;

    /**
     * Background color to fill the resized image
     *
     * @var array(red, green, blue)
     */
    protected $_backgroundColor = null;

    /**
     * Resize to width and height always
     *
     * @var boolean
     */
    protected $_keepFrame = true;

    /**
     * @param  TM_Attributepages_Model_Entity $entity
     * @param  string $mode [image|thumbnail]
     * @return TM_Attributepages_Helper_Image
     */
    public function init(TM_Attributepages_Model_Entity $entity, $mode = 'image')
    {
        $this->_entity = $entity;
        $this->_mode   = $mode;
        return $this;
    }

    /**
     * Set backgorund color to fill the resized image
     *
     * @param integer $r Red    [0-255]
     * @param integer $g Green  [0-255]
     * @param integer $b Blue   [0-255]
     */
    public function setBackgroundColor($r = 255, $g = 255, $b = 255)
    {
        $this->_backgroundColor = array($r, $g, $b);
        return $this;
    }

    public function setKeepFrame($flag)
    {
        $this->_keepFrame = (bool)$flag;
        return $this;
    }

    public function resize($width, $height)
    {
        $image = $this->_entity->getData($this->_mode);
        if (empty($image)) {
            return '';
        }

        if (!$width || !is_numeric($width)) {
            $width = 200;
        }
        if (!$height || !is_numeric($height)) {
            $height = $width;
        }

        $folderPath = Mage::getBaseDir('media') . '/' . TM_Attributepages_Model_Entity::IMAGE_PATH;
        $cacheDir   = $folderPath . '/' . self::CACHE_FOLDER;
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $pathinfo  = pathinfo($image);
        $imageName = implode('/', array(
            $pathinfo['dirname'],
            $width . 'x' . $height . '_' . implode(',', $this->getBackgroundColor()),
            $pathinfo['basename']
        ));

        $resizedImage  = $cacheDir . $imageName;
        $originalImage = $folderPath . $image;

        if (!file_exists($resizedImage) && file_exists($originalImage)) {
            $imageObj = new Varien_Image($originalImage);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepFrame($this->_keepFrame);
            $imageObj->backgroundColor($this->getBackgroundColor());
            $imageObj->resize($width, $height);
            $imageObj->save($resizedImage);
        }

        return Mage::getBaseUrl('media')
            . TM_Attributepages_Model_Entity::IMAGE_PATH
            . '/'
            . self::CACHE_FOLDER
            . $imageName;
    }

    /**
     * Retrieve background color to fill the resized image
     *
     * @return array(red, green, blue)
     */
    public function getBackgroundColor()
    {
        if (null === $this->_backgroundColor) {
            $rgb = Mage::getStoreConfig('attributepages/image/background');
            $rgb = explode(',', $rgb);
            foreach ($rgb as $i => $color) {
                $rgb[$i] = (int) $color;
            }
            $this->_backgroundColor = $rgb;
        }
        return $this->_backgroundColor;
    }
}
