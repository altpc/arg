<?php

class TM_FacebookLB_Adminhtml_Model_System_Config_Source_Lang
{
    public function toOptionArray()
    {
        $path = 'http://facebook.com/translations/FacebookLocales.xml';

        try {
            $xml = new Varien_Simplexml_Element($path, 0, true);
            $xmlData = $xml->children();
        } catch (Exception $e) {
            // no internet connection is available
            return array(
                array(
                    'value' => 'en_US',
                    'label' => 'English'
                )
            );
        }

        $locales = $xmlData;
        $result = array();
        foreach ($xmlData as $locale) {
            $data = $locale->asArray();
            $codes = $data['codes'];
            $code = trim($codes['code']['standard']['representation']);
            $label = $data['englishName'] . ' - ' . $code;

            $result[] = array('value' => $code, 'label' => $label);
        }

        return $result;
    }
}
