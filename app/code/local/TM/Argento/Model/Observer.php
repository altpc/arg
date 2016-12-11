<?php

class TM_Argento_Model_Observer
{
    public function onArgentoConfigSave($observer)
    {
        $request = $observer->getControllerAction()->getRequest();
        $section = $request->getParam('section');
        if (false === strstr($section, 'argento_')) {
            return;
        }

        $css         = Mage::getModel('argento/css');
        $storeCode   = $request->getParam('store');
        $websiteCode = $request->getParam('website');

        $inherited   = true;
        foreach ($request->getParam('groups') as $groupName => $groupValue) {
            foreach ($groupValue['fields'] as $fieldName => $fieldValue) {
                if (empty($fieldValue['inherit'])) {
                    $inherited = false;
                    break 2;
                }
            }
        }
        // if all options are inherited - delete file
        if ($inherited) {
            $css->removeFile($section, $storeCode, $websiteCode);
        } else {
            $css->generateAndSave($section, $storeCode, $websiteCode, TM_Argento_Model_Css::MODE_CREATE_AND_SAVE);
        }
        if (!$storeCode) {
            $descending = $this->_getDescendingWebsiteAndStoreCodes($websiteCode);
            foreach ($descending as $_websiteCode => $_storeCodes) {
                $css->generateAndSave($section, null, $_websiteCode, TM_Argento_Model_Css::MODE_UPDATE);
                foreach ($_storeCodes as $_storeCode) {
                    $css->generateAndSave($section, $_storeCode, $_websiteCode, TM_Argento_Model_Css::MODE_UPDATE);
                }
            }
        }
    }

    /**
     * Retrieve pairs of store and website codes that are the childs
     * of given websiteCode.
     * If websiteCode is null, all stores and websites will be returned.
     *
     * Used to update all descending stores that already has css file,
     * to fix all inherited rules from currently saved store
     *
     * @param string $websiteCode
     * @return array
     */
    protected function _getDescendingWebsiteAndStoreCodes($websiteCode)
    {
        $collection = Mage::getResourceModel('core/store_collection')
            ->setWithoutDefaultFilter()
            ->join(
                array('website' => 'core/website'),
                'website.website_id = main_table.website_id',
                array('website_code' => 'code')
            );

        if ($websiteCode) {
            $collection->addFieldToFilter('website.code', $websiteCode);
        }

        $result = array();
        foreach ($collection as $store) {
            $result[$store->getWebsiteCode()][] = $store->getCode();
        }
        return $result;
    }
}
