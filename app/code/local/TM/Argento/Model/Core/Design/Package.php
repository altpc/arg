<?php

class TM_Argento_Model_Core_Design_Package extends TM_Argento_Model_Core_Design_PackageAbstract
{
    /**
     * Added additional fallback rules:
     * package/[active_theme]_custom
     * package/[active_theme]
     * package/[default_theme]
     * enterprise/default,package/theme[configured with backend rules] - After default
     * base/default
     *
     * Check for files existence by specified scheme
     *
     * If fallback enabled, the first found file will be returned. Otherwise the base package / default theme file,
     *   regardless of found or not.
     * If disabled, the lookup won't be performed to spare filesystem calls.
     *
     * @param string $file
     * @param array &$params
     * @param array $fallbackScheme
     * @return string
     */
    protected function _fallback($file, array &$params, array $fallbackScheme = array(array()))
    {
        if ($this->_shouldFallback) {
            // tm modification #1
            $package = $this->getPackageName();
            if ('argento' !== $package) {
                return parent::_fallback($file, $params, $fallbackScheme);
            }

            $suffixes = array(
                '_custom_' . Mage::app()->getStore()->getCode(),
                '_custom'
            );
            foreach ($suffixes as $suffix) {
                $customParams = $params;
                $customParams['_theme'] .= $suffix;
                $filename = $this->validateFile($file, $customParams);
                if ($filename) {
                    $params['_theme'] .= $suffix;
                    return $filename;
                }
            }
            // tm modification #1

            foreach ($fallbackScheme as $try) {
                $params = array_merge($params, $try);
                $filename = $this->validateFile($file, $params);
                if ($filename) {
                    return $filename;
                }
            }

            // tm modification #2 After Default support. Used for Enterprise edition.
            $themes = $this->getTheme('after_default');
            if ($themes && $themes !== $this->getTheme('default')) {
                foreach (explode(',', $themes) as $theme) {
                    $themeParts = explode('/', $theme);
                    if (count($themeParts) === 2) {
                        $params['_package'] = $themeParts[0];
                        $params['_theme']   = $themeParts[1];
                    } else {
                        $params['_package'] = $package;
                        $params['_theme']   = $themeParts[0];
                    }
                    $filename = $this->validateFile($file, $params);
                    if ($filename) {
                        return $filename;
                    }
                }
            }
            // tm modification #2

            $params['_package'] = self::BASE_PACKAGE;
            $params['_theme']   = self::DEFAULT_THEME;
        }
        return $this->_renderFilename($file, $params);
    }

    // public function getSkinUrl($file = null, array $params = array())
    // {
    //     Varien_Profiler::start(__METHOD__);
    //     if (empty($params['_type'])) {
    //         $params['_type'] = 'skin';
    //     }
    //     if (empty($params['_default'])) {
    //         $params['_default'] = false;
    //     }
    //     $this->updateParamDefaults($params);
    //     if (!empty($file)) {
    //         $result = $this->_fallback(
    //             $file,
    //             $params,
    //             $this->_fallback->getFallbackScheme(
    //                 $params['_area'],
    //                 $params['_package'],
    //                 $params['_theme']
    //             )
    //         );
    //         // add filemtime to the filename to provide new file version to all clients
    //         if (strpos('custom', $result) && file_exists($result)) {
    //             $file .= '?v=' . filemtime($result);
    //         }
    //     }
    //     $result = $this->getSkinBaseUrl($params) . (empty($file) ? '' : $file);
    //     Varien_Profiler::stop(__METHOD__);
    //     return $result;
    // }
}
