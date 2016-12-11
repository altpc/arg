<?php

class TM_LightboxPro_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $_hsConfigRendered = false;

    public function getGraphicDir()
    {
        $_graphicsDir = 'tm/lib/highslide/graphics/';
        $isSecure = $this->_getRequest()->isSecure();
        $graphicsDir = Mage::getBaseUrl('js', $isSecure) . $_graphicsDir;
        if (Mage::helper('core')->isModuleOutputEnabled('TM_CDN')) {
            $cdn = Mage::helper('cdn')->getAdapter();
            if ($cdn) {
                $filename = implode(
                    DS,
                    array(Mage::getBaseDir(), 'js', $_graphicsDir)
                );
                $_graphicsDir = $cdn->getUrl($filename);
                if ($_graphicsDir) {
                    $graphicsDir = $_graphicsDir;
                }
            }
        }
        return $graphicsDir;
    }

    /**
     * Render HTML data-attribures string (renders only once)
     * @param  boolean $runSilenthly Allows render multiply times
     * @return string
     */
    public function configAsDataString($runSilenthly = false)
    {
        if ($this->_hsConfigRendered && !$runSilenthly) {
            return '';
        }

        $config = array();
        $config['graphics-dir'] = $this->getGraphicDir();
        // section General
        $config['outline-type'] =
           $this->getConfig('general/outlineType');
        // Section Image Size
        if ($this->getConfig('size/maxWindow')) {
            list($_maxWindowWidth, $_maxWindowHeight) =
                explode('x', $this->getConfig('size/maxWindow'));
            if (!empty($_maxWindowWidth)) {
                $config['max-width'] = $_maxWindowWidth;
            }
            if (!empty($_maxWindowHeight)) {
                $config['max-height'] = $_maxWindowHeight;
            }
        }
        // section Style
        if ($this->getConfig('style/dimming_enable')) {
            $config['dimming-opacity'] =
                $this->getConfig('style/dimmingOpacity');
        }
        // section Behavior
        $anchor = $this->getConfig('behavior/anchor');
        if ($anchor) {
            $config['anchor'] = $anchor;
        }
        if ($this->getConfig('behavior/align')) {
            $config['align'] = 'center';
        }
        $config['width'] = $this->getConfig('behavior/width');
        $config['height'] = $this->getConfig('behavior/height');
        if (!$this->getConfig('behavior/allowSizeReduction')) {
            $config['allow-size-reduction'] = 'false';
        }
        if ($this->getConfig('behavior/padToMinWidth')) {
            $config['pad-to-min-width'] = 'true';
        }
        if (!$this->getConfig('behavior/allowMultipleInstances')) {
            $config['allow-multiple-instances'] = 'false';
        }
        if ($this->getConfig('behavior/blockRightClick')) {
            $config['block-right-click'] = 'true';
        }
        if (!$this->getConfig('behavior/enableKeyListener')) {
            $config['enable-key-listener'] = 'false';
        }
        $config['number-of-images-to-preload'] =
            $this->getConfig('behavior/numberOfImagesToPreload');

        // section Overlay
        $source = $this->getConfig('overlays/captionEval');
        if (!empty($source)) {
            $config['caption-eval'] = $source;
        }
        $config['caption-overlay'] = $this->arrayToJson(
            array(
                'position' => $this->getConfig('overlays/captionOverlay_position'),
                'relativeTo' => $this->getConfig('overlays/captionOverlay_relativeTo')
            )
        );
        if ($this->getConfig('overlays/heading_enable')) {
            $source = $this->getConfig('overlays/headingEval');
            if (!empty($source)) {
                $config['heading-eval'] = $source;
            }
            $config['heading-overlay'] = $this->arrayToJson(
                array(
                    'position' => $this->getConfig('overlays/headingOverlay_position'),
                    'relativeTo' => $this->getConfig('overlays/headingOverlay_relativeTo')
                )
            );
        }
        $config['close-button-enabled'] =
            $this->getConfig('overlays/closebutton_enable') ? 'true': 'false';

        if (!$runSilenthly) {
            $this->_hsConfigRendered = true;
        }

        return $this->arrayToDataString($config);
    }

    public function slideshowSettingsAsDataString()
    {
        $sets = array();
        $sets['interval'] = $this->getConfig('gallery/behaviour_interval');
        $sets['repeat'] =
            $this->getConfig('gallery/behaviour_repeat') ? 'true' : 'false';
        $controlsStyle = $this->getConfig('gallery/controls_presetstyle');
        if ($controlsStyle) {
            $sets['use-controls'] = 'true';
            $sets['fixed-controls'] =
                $this->getConfig('gallery/behaviour_fixedControls') ? 'fit' : 'false';
            $sets['overlay-options'] = $this->arrayToJson(
                array(
                    'className' => $controlsStyle,
                    'opacity' => $this->getConfig('gallery/controls_opacity'),
                    'position' => $this->getConfig('gallery/controls_position'),
                    'offsetX' => $this->getConfig('gallery/controls_xOffset'),
                    'offsetY' => $this->getConfig('gallery/controls_yOffset'),
                    'hideOnMouseOut' => $this->getConfig('gallery/controls_hideOnMouseOut') ? 'true' : 'false'
                )
            );
        }
        if ($this->getConfig('gallery/thumbstrip_enable')) {
            $sets['thumbstrip'] = $this->arrayToJson(
                array(
                    'mode' => $this->getConfig('gallery/thumbstrip_mode'),
                    'position' => $this->getConfig('gallery/thumbstrip_position'),
                    'relativeTo' => $this->getConfig('gallery/thumbstrip_relative'),
                )
            );

        }

        $custom = array();
        $position = $this->getConfig('gallery/numbers_position');
        if ($position) {
            $custom['numberPosition'] = $position;
        }
        if ($this->getConfig('gallery/behaviour_autoplay')) {
            $custom['autoplay'] = 'true';
        }
        if (count($custom) > 0) {
            $sets['custom-configs'] = $this->arrayToJson($custom);
        }

        return $this->arrayToDataString($sets);
    }

    public function arrayToJson($arr)
    {
        $str = '{';
        foreach ($arr as $key => $value) {
            $str .= '\'' . $key . '\': \'' . $value . '\', ';
        }
        $str = rtrim($str, ' ,') . '}';
        return $str;
    }

    public function arrayToDataString($arr)
    {
        $str = '';
        foreach ($arr as $key => $value) {
            $str .= 'data-' . $key . '="' . $value . '" ';
        }
        return $str;
    }

    public function getHsConfigCssClassName()
    {
        return 'lightbox-highslide-config';
    }

    protected function getConfig($node) {
        return Mage::getStoreConfig('lightboxpro/'.$node);
    }
}
