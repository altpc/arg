<?php

class TM_EasyBanner_Model_Mysql4_Banner_Statistic extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('easybanner/banner_statistic', 'id');
    }

    public function incrementDisplayCount($bannerId)
    {
        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATE_INTERNAL_FORMAT);

        $select = $this->_getReadAdapter()->select()
            ->from(array('banner_statistic' => $this->getMainTable()))
            ->where('banner_id = ?', $bannerId)
            ->where('date = ?', $todayDate);

        $row = $this->_getReadAdapter()->fetchRow($select);

        if ($row) {
            $this->_getWriteAdapter()->update($this->getMainTable(), array(
                'display_count' => ++$row['display_count']
                ), "banner_id = {$bannerId} AND date = '{$todayDate}'"
            );
        } else {
            $this->_getWriteAdapter()->insert($this->getMainTable(), array(
                'banner_id' => $bannerId,
                'date' => $todayDate,
                'display_count' => 1,
                'clicks_count' => 0
            ));
        }
    }

    public function incrementClicksCount($bannerId)
    {
        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATE_INTERNAL_FORMAT);

        $select = $this->_getReadAdapter()->select()
            ->from(array('banner_statistic' => $this->getMainTable()))
            ->where('banner_id = ?', $bannerId)
            ->where('date = ?', $todayDate);

        $row = $this->_getReadAdapter()->fetchRow($select);

        if ($row) {
            $this->_getWriteAdapter()->update($this->getMainTable(), array(
                'clicks_count' => ++$row['clicks_count']
                ), "banner_id = {$bannerId} AND date = '{$todayDate}'"
            );
        } else {
            // if banner was rendered at 23:59 and clicked at 00:01
            $this->_getWriteAdapter()->insert($this->getMainTable(), array(
                'banner_id' => $bannerId,
                'date' => $todayDate,
                'display_count' => 1,
                'clicks_count' => 1
            ));
        }
    }

    public function clearStatistics($bannerId)
    {
        $this->_getWriteAdapter()->delete($this->getMainTable(), "banner_id = {$bannerId}");
    }

    public function getByIdentifier($bannerId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('banner_statistic' => $this->getMainTable()))
            ->where('banner_id = ?', $bannerId);
    }

    public function getList($bannerId)
    {
        $select = $this->_getReadAdapter()->select()
                ->from(array('banner_statistic' => $this->getMainTable()))
                ->where('banner_id = ?', $bannerId);

        return $row = $this->_getReadAdapter()->fetchAll($select);
    }
}