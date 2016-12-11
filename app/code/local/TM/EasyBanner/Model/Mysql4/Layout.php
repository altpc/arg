<?php

class TM_EasyBanner_Model_Mysql4_Layout extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('easybanner/layout_update', 'layout_update_id');
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /* layout_link */
        foreach ($object->getStoreIds() as $storeId) {
            $layoutLinkData = array(
                'layout_update_id' => $object->getId(),
                'store_id' => $storeId,
                'banner_id' => $object->getBannerId(),
                'placeholder_id' => $object->getPlaceholderId()
            );
            $this->_getWriteAdapter()->insert($this->getTable('easybanner/layout_link'), $layoutLinkData);
        }

        return $this;
    }

    public function fetchUpdatesByHandle(array $handles)
    {
        $readAdapter = $this->_getReadAdapter();
        $updateStr = '';

        if ($readAdapter) {
            $select = $readAdapter->select()->from(array('update' => $this->getMainTable()), 'xml')
                ->join(array('link' => $this->getTable('easybanner/layout_link')), 'link.layout_update_id = update.layout_update_id', '')
                ->where('link.store_id IN (?)', array(Mage::app()->getStore()->getId(), 0))
                ->where('update.handle IN (?)', $handles)
                ->order('link.banner_id ASC');

            foreach ($readAdapter->fetchAll($select) as $update) {
                $updateStr .= $update['xml'];
            }
        }
        return $updateStr;
    }

    public function removeUpdatesByBanner($id)
    {
        $this->removeUpdates('banner_id', $id);
    }

    public function removeUpdatesByPlaceholder($id)
    {
        $this->removeUpdates('placeholder_id', $id);
    }

    public function removeUpdatesByStore($id)
    {
        $this->removeUpdates('store_id', $id);
    }

    public function removeUpdates($key, $value)
    {
        $readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select()->from(
            array('link' => $this->getTable('easybanner/layout_link')),
            'layout_update_id')
            ->where("link.{$key} = ?", $value);
        $layout_update_ids = $readAdapter->fetchCol($select);
        $writeAdapter = $this->_getWriteAdapter();
        $writeAdapter->delete(
            $this->getMainTable(),
            $writeAdapter->quoteInto('layout_update_id IN (?)', $layout_update_ids)
        );
    }
}