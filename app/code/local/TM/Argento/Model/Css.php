<?php

class TM_Argento_Model_Css
{
    const MODE_CREATE_AND_SAVE = 'create_save';
    const MODE_UPDATE          = 'update';

    public function getStorage()
    {
        return Mage::getSingleton('core/file_storage_file');
    }

    /**
     * @param string $theme
     * @param string $storeCode
     * @param string $websiteCode
     * @param string $mode
     * @return void
     */
    public function generateAndSave($theme, $storeCode, $websiteCode, $mode)
    {
        $filePath = $this->getFilePath($theme, $storeCode, $websiteCode);
        if (self::MODE_UPDATE === $mode) {
            if (!file_exists($this->getStorage()->getMediaBaseDirectory() . DS . $filePath)) {
                return;
            }
        }
        $config = $this->getThemeConfig($theme, $storeCode, $websiteCode);
        $css    = $this->convertConfigToCss($config);

        try {
            $this->getStorage()->saveFile(array(
                'content'  => $css,
                'filename' => $filePath
            ), true);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    /**
     * @param string $theme
     * @param string $storeCode
     * @param string $websiteCode
     * @return void
     */
    public function removeFile($theme, $storeCode, $websiteCode)
    {
        $filePath = $this->getFilePath($theme, $storeCode, $websiteCode);
        @unlink($this->getStorage()->getMediaBaseDirectory() . DS . $filePath);
    }

    /**
     * Retrieve css filepath, relative to media folder
     *
     * @param string $theme
     * @param string $storeCode
     * @param string $websiteCode
     * @return string
     */
    public function getFilePath($theme, $storeCode, $websiteCode)
    {
        $suffix = '_backend.css';
        if ($storeCode) {
            $prefix = implode('_', array(
                $websiteCode,
                $storeCode
            ));
        } elseif ($websiteCode) {
            $prefix = $websiteCode;
        } else {
            $prefix = 'admin';
        }
        return str_replace('_', DS, $theme) . DS . 'css' . DS . $prefix . $suffix;
    }

    /**
     * @param string $theme
     * @param string $storeCode
     * @param string $websiteCode
     * @return array
     */
    public function getThemeConfig($theme, $storeCode, $websiteCode)
    {
        if ($storeCode) {
            $scope     = 'stores';
            $scopeCode = $storeCode;
        } elseif ($websiteCode) {
            $scope     = 'websites';
            $scopeCode = $websiteCode;
        } else {
            $scope     = 'default';
            $scopeCode = null;
        }
        $node = Mage::getConfig()->getNode($theme, $scope, $scopeCode);
        if (!$node) {
            return array();
        }
        $config = array();
        foreach ($node->children() as $k=>$v) {
            $config[$k] = $v;
        }
        foreach ($config as $group => $values) {
            if ($values instanceof Varien_Simplexml_Element) {
                $config[$group] = $values->asCanonicalArray();
            }
        }
        return $config;
    }

    /**
     * @param array $config
     * <pre>
     *  background
     *      body_background-color => #fff
     *      ...
     *  font
     *      body_font-family      => Helvetica,Arial,sans-serif
     *      page-header_color     => #000
     *      page-header_font-size => 12px
     *      ...
     *  style
     *      css => inline css
     *  css_selector
     *      body => body
     *      page-header => h1
     * </pre>
     */
    public function convertConfigToCss($config)
    {
        $groupedCss   = array();
        $groupsToSkip = array('css_selector', 'head');
        $propsToSkip  = array('heading', 'head_link', 'sticky_header');
        foreach ($config as $groupName => $groupValues) {
            if (in_array($groupName, $groupsToSkip)) {
                continue;
            }
            foreach ($groupValues as $name => $value) {
                $value = (string)$value;
                list($key, $prop) = explode('_', $name);
                if (in_array($prop, $propsToSkip)) {
                    continue;
                }
                if ($method = $this->_getExtractorMethodName($prop)) {
                    $value = $this->$method($value);
                }
                if (false === $value || strlen($value) === 0) {
                    continue; // feature to keep default theme styles from theme.css
                }
                $groupedCss[$key][] = "{$prop}:{$value};";
            }
        }
        $css = '';
        foreach ($groupedCss as $key => $cssArray) {
            if (empty($config['css_selector'])
                || !is_array($config['css_selector'])
                || empty($config['css_selector'][$key])) {

                $selector = Mage::getStoreConfig('argento/css_selector/' . $key);
                if (empty($selector)) {
                    continue;
                }
            } else {
                $selector = $config['css_selector'][$key];
            }
            $styles   = implode('', $cssArray);
            $css .= "{$selector}{{$styles}}\n";
        }

        if (!empty($config['head']['css'])) {
            $css .= $config['head']['css'];
        }
        return $css;
    }

    /**
     * @param string $property
     * @return string|false
     */
    protected function _getExtractorMethodName($property)
    {
        $property = str_replace('-', ' ', $property);
        $property = ucwords($property);
        $property = str_replace(' ', '', $property);
        $method = '_extract' . $property;
        if (method_exists($this, $method)) {
            return $method;
        }
        return false;
    }

    protected function _extractBackgroundImage($value)
    {
        // fix to prevent activating of 'Use default' checkbox, when image is deleted
        if (empty($value) || 'none' === $value) {
            $value = 'none';
        } else {
            $value = 'url(../images/' . $value . ')';
        }
        return $value;
    }

    protected function _extractBackgroundColor($value)
    {
        if (empty($value)) {
            $value = 'transparent';
        }
        return $value;
    }

    protected function _extractBackgroundPosition($value)
    {
        return str_replace(',', ' ', $value);
    }
}
