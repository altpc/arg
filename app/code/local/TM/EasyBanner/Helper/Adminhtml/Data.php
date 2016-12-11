<?php

class TM_EasyBanner_Helper_Adminhtml_Data extends Mage_Adminhtml_Helper_Dashboard_Abstract
{
    protected function _initCollection()
    {
        $period = $this->getParam('period');
        if (!in_array($period, array_keys($this->getDatePeriods()))) {
            $this->setParam('period', '7d');
        }

        $isFilter = $this->getParam('store') || $this->getParam('website') || $this->getParam('group');
        $this->_collection = Mage::getResourceSingleton('easybanner/banner_statistic_collection')
            ->prepareSummary($this->getParam('period'), 0, 0, $isFilter);

        if ($this->getParam('banner_id')) {
            $this->_collection->addFieldToFilter('banner_id', $this->getParam('banner_id'));
        }

        $this->_collection->load();
    }

    public function getDatePeriods()
    {
        return array(
            '7d'  => $this->__('Last 7 Days'),
            '1m'  => $this->__('Current Month'),
            '1y'  => $this->__('YTD'),
            '2y'  => $this->__('2YTD')
        );
    }
}