<?php

class TM_EasyBanner_Block_Banner extends Mage_Core_Block_Template
{
    public function getTemplate()
    {
        $template = parent::getTemplate();
        if ($template) {
            $this->setData('template', $template);
        } else if (!$this->hasData('template')) {
            $this->setData('template', "tm/easybanner/banner/{$this->getMode()}.phtml");
        }
        return $this->_getData('template');
    }

    protected function _toHtml()
    {
        if (!$this->getBannerId() || !$this->getData('url')) {
            if (!$this->getBannerId() && !$name = $this->getBannerName()) {
                return '';
            }
            if ($this->getBannerId()) {
                // EE_PageCache
                $banner = Mage::getModel('easybanner/banner')->load($this->getBannerId());
            } else {
                // inline banner call
                $banner = Mage::getModel('easybanner/banner')->load($name, 'identifier');
            }
            if ($banner->getId() && $banner->check(Mage::app()->getStore()->getId())) {
                $this->addData($banner->getData());
            } else {
                return '';
            }
        }

        $html = parent::_toHtml();
        $html = str_replace('{{tm_banner_url}}', $this->getUrl($this->getBannerUrl()), $html);

        $statRes = Mage::getResourceModel('easybanner/banner_statistic')
            ->incrementDisplayCount($this->getBannerId());

        $processor = Mage::helper('cms')->getBlockTemplateProcessor();
        return $processor->filter($html);
    }

    public function getBannerUrl()
    {
        $url = 'click/id/' . $this->_getData('banner_id');
        if (!$this->getHideUrl()) {
            $url .= '/url/' . trim($this->_getData('url'), '/');
        }
        if (Mage::getStoreConfig('aitpagecache')) { ///aitpagecache_config_cron/aitpagecache_config_cron_frequency')) {
            $url .= '?noMagentoBoosterCache';
        }
        return $url;
    }

    public function getHtml()
    {
        return $this->getData('html');
    }

    public function getImage()
    {
        $url = false;
        $prefix = Mage::getBaseUrl('media').trim(Mage::getStoreConfig('easybanner/general/image_folder'), ' /\\').'/';
        if (!$image = $this->getData('image')) {
            return false;
        }
        return $prefix . $image;
    }

    public function getBackgroundColor()
    {
        $rgb = $this->_getData('background_color');
        if (!$rgb) {
            return array(255, 255, 255);
        }

        $rgb = explode(',', $rgb);
        foreach ($rgb as $i => $color) {
            $rgb[$i] = (int) $color;
        }
        return $rgb;
    }

    public function getClassName()
    {
        return $this->_getData('class_name') . ' ' . $this->getHtmlId();
    }

    public function getHtmlId()
    {
        return Mage::helper('easybanner')->getBannerClassName($this->getIdentifier());
    }

    public function resizeImage($width, $height)
    {
        $imageUrl = $this->getImage();
        if (!$imageUrl) {
            return '';
        }

        if (!$height) {
            $height = null;
        }

        $dir = implode(DS, array(
            Mage::getBaseDir('media'),
            trim(Mage::getStoreConfig('easybanner/general/image_folder'), ' /\\'),
            'resized'
        ));
        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        };

        $imageName    = substr(strrchr($imageUrl, "/"), 1);
        $imageName    = $width . 'x' . $height . '_' . $imageName;
        $imageResized = $dir . DS . $imageName;
        $imagePath    = Mage::getBaseDir('media')
            . DS . trim(Mage::getStoreConfig('easybanner/general/image_folder'), ' /\\')
            . DS . str_replace("/", DS, $this->getData('image'));

        if (!file_exists($imageResized) && file_exists($imagePath)) {
            $imageObj = new Varien_Image($imagePath);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepFrame(true);
            // $imageObj->keepTransparency(true);
            $imageObj->backgroundColor($this->getBackgroundColor());
            $imageObj->resize($width, $height);
            $imageObj->save($imageResized);
        }

        $imageUrl = Mage::getBaseUrl('media')
            . trim(Mage::getStoreConfig('easybanner/general/image_folder'), ' /\\')
            . "/resized/"
            . $imageName;

        return $imageUrl;
    }

    /**
     * EE_PageCache integration
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $items = array(
            'banner_id'   => $this->getBannerId(),
            'banner_name' => $this->getBannerName()
        );
        $items = parent::getCacheKeyInfo() + $items;
        return $items;
    }
}
