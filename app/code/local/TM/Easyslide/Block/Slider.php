<?php

class TM_Easyslide_Block_Slider extends Mage_Core_Block_Template
{

    public function getSlider()
    {
        if (!$this->getData('slider')) {
            $slider = Mage::getModel('easyslide/easyslide')
                ->load($this->getSliderId());
            $this->setData('slider', $slider);
        }
        return $this->getData('slider');
    }

    public function getTemplate()
    {
        if (!$this->hasData('template')) {
            switch ($this->getSlider()->getSliderType()) {
                case '1':
                    $this->setData('template', 'tm/easyslide/nivoslider.phtml');
                    break;
                default:
                    $this->setData('template', 'tm/easyslide/easyslider.phtml');
                    break;
            }
        }
        return $this->getData('template');
    }

    public function _toHtml()
    {
        if (!$this->_beforeToHtml() || !$sliderId = $this->getSliderId()) {
            return '';
        }
        $slider = Mage::getModel('easyslide/easyslide')->load($sliderId);
        if (!$slider->getStatus()) {
            return '';
        }
        $slider->loadSlides(true);
        $this->setSlider($slider);
        return parent::_toHtml();
    }

    public function filterDescription($description)
    {
        $processor = Mage::helper('cms')->getPageTemplateProcessor();
        return $processor->filter($description);
    }

    public function getDescriptionClassName($position)
    {
        switch ($position) {
            case 1:
                return "easyslide-description-top";
                break;
            case 2:
                return "easyslide-description-right";
                break;
            case 3:
                return "easyslide-description-bottom";
                break;
            case 4:
                return "easyslide-description-left";
                break;
            case 5:
                return "easyslide-description-center";
                break;
        }
    }

    public function getBackgroundClassName($background)
    {
        switch ($background) {
            case 1:
                return "easyslide-background-light";
                break;
            case 2:
                return "easyslide-background-dark";
                break;
            case 3:
                return "easyslide-background-transparent";
                break;
        }
    }

    public function getSlideClassName($slide)
    {
        switch ($slide['target_mode']) {
            case 1:
                return 'target-blank';
            case 2:
                return 'target-popup';
            case 0:
            default:
                return 'target-self';
        }
    }

    public function getPrototypeSliderConfig()
    {
        $slider = $this->getSlider();
        $config['duration'] = (float)$slider->getDuration();
        $config['autoGlide'] = (int)$slider->getAutoglide();
        $config['frequency'] = (float)$slider->getFrequency();
        $config['effectType'] = $slider->getEffect();
        return json_encode($config);
    }

    public function getNivoSliderConfig()
    {
        $slider = $this->getSlider();
        $options = array(
            'slices' => FILTER_VALIDATE_INT,
            'boxCols' => FILTER_VALIDATE_INT,
            'boxRows' => FILTER_VALIDATE_INT,
            'animSpeed' => FILTER_VALIDATE_INT,
            'pauseTime' => FILTER_VALIDATE_INT,
            'directionNav' => FILTER_VALIDATE_BOOLEAN,
            'controlNav' => FILTER_VALIDATE_BOOLEAN,
            'pauseOnHover' => FILTER_VALIDATE_BOOLEAN,
            'manualAdvance' => FILTER_VALIDATE_BOOLEAN
        );
        $config['effect'] = implode(',', $slider->getNivoeffect());
        $config['prevText'] = Mage::helper('easyslide')->__('Prev');
        $config['nextText'] = Mage::helper('easyslide')->__('Next');
        foreach ($options as $key => $value) {
            $config[$key] = filter_var($slider->getData($key), $value);

        }
        return json_encode($config);
    }

    public function getSlideImageUrl($url)
    {
        if (strncmp($url,'http', 4) == 0 || empty($url)) {
            return $url;
        }
        return rtrim(Mage::getBaseUrl('media'), DS) . DS
            . 'easyslide' . DS
            . $url;
    }

}
