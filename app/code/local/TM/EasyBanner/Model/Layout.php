<?php

class TM_EasyBanner_Model_Layout extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('easybanner/layout');
    }

    public function fetchDbLayoutUpdates(Varien_Event_Observer $observer)
    {
        Varien_Profiler::start("easybanner::updateLayout");
        $layoutUpdate = $observer->getData('layout')->getUpdate();
        $layoutUpdate->addUpdate(
            Mage::getResourceModel('easybanner/layout')->fetchUpdatesByHandle(
                $layoutUpdate->getHandles()
            )
        );
        Varien_Profiler::stop("easybanner::updateLayout");
    }

    public function buildLayoutUpdateByBanner(TM_EasyBanner_Model_Banner $banner)
    {
        Mage::getResourceModel('easybanner/layout')->removeUpdatesByBanner($banner->getBannerId());

        if (!$banner->isActive()) {
            return;
        }

        $this->_saveBannerXml($banner);
    }

    public function buildLayoutUpdateByPlaceholder(TM_EasyBanner_Model_Placeholder $placeholder)
    {
        Mage::getResourceModel('easybanner/layout')->removeUpdatesByPlaceholder($placeholder->getPlaceholderId());

        if (!$placeholder->getStatus()) {
            return;
        }

        $this->_savePlaceholderXml($placeholder);

        foreach ($placeholder->getBannerIds(true) as $bannerId) {
            $this->_saveBannerXml(Mage::getSingleton('easybanner/banner')->load($bannerId));
        }
    }

    protected function _saveBannerXml(TM_EasyBanner_Model_Banner $banner)
    {
        $actionXml = $this->_getActionXml($banner);
        $placeholders = array();
        foreach (Mage::getModel('easybanner/placeholder')->getCollection()->toOptionArray() as $values) {
            $placeholders[$values['value']] = $values['label'];
        }
        $data = array(
            'store_ids' => $banner->getStoreIds(),
            'banner_id' => $banner->getBannerId()
        );
        $placeholderPrefix = TM_EasyBanner_Model_Placeholder::LAYOUT_NAME_PREFIX;
        foreach ($banner->getPlaceholderIds(true) as $placeholderId) {
            $data['handle'] = 'default'; //@todo foreach by handlers
            $data['placeholder_id'] = $placeholderId;
            $data['xml'] = "<reference name='{$placeholderPrefix}{$placeholders[$placeholderId]}'>{$actionXml}</reference>";
            $this->setData($data);
            $this->save();
        }
    }

    protected function _savePlaceholderXml(TM_EasyBanner_Model_Placeholder $placeholder)
    {
        $placeholderPrefix = TM_EasyBanner_Model_Placeholder::LAYOUT_NAME_PREFIX;
        $placeholderXml = "<reference name='{$placeholder->getParentBlock()}'>\n";
        $placeholderXml .= "  <block type='easybanner/placeholder' name='{$placeholderPrefix}{$placeholder->getName()}' {$placeholder->getPosition()}>\n";
        $placeholderXml .= "    <action method='setPlaceholderId'><id>{$placeholder->getPlaceholderId()}</id></action>\n";
        //$placeholderXml .= "    <action method='setPlaceholderLimit'><limit>{$placeholder->getLimit()}</limit></action>\n";
        $placeholderXml .= "  </block>\n";
        $placeholderXml .= "</reference>";
        $this->setData(array(
            'store_ids' => array(0),
            'banner_id' => new Zend_Db_Expr('NULL'),
            'placeholder_id' => $placeholder->getPlaceholderId(),
            'handle' => 'default',
            'xml' => $placeholderXml
        ));
        $this->save();
    }

    protected function _getActionXml($banner)
    {
        $filters = $this->_getFiltersXml(array(unserialize($banner->getConditionsSerialized())));
        return "\n<action method='addBanner'>\n  <id>{$banner->getIdentifier()}</id>\n{$filters}</action>\n";
    }

    protected function _getFiltersXml(array $conditions, $key = 'filter')
    {
        $filters = '';
        $i = 0;
        foreach ($conditions as $filter) {
            $i++;
            $filters .= "  <{$key}-{$i}>";
            $filters .= "<key>{$key}-{$i}</key>";

            if (isset($filter['aggregator'])) {
                $filters .= "<aggregator>{$filter['aggregator']}</aggregator>";
            } else {
                $filters .= "<attribute>{$filter['attribute']}</attribute>";
                $filters .= "<operator><![CDATA[{$filter['operator']}]]></operator>";
            }

            $filters .= "<value><![CDATA[{$filter['value']}]]></value>";
            $filters .= "</{$key}-{$i}>\n";

            if (isset($filter['conditions'])) {
                $filters .= $this->_getFiltersXml($filter['conditions'], $key . '-' . $i);
            }
        }
        return $filters;
    }
}