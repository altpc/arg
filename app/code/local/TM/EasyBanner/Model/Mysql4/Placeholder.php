<?php

class TM_EasyBanner_Model_Mysql4_Placeholder extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('easybanner/placeholder', 'placeholder_id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$this->isValidPlaceholderIdentifier($object)) {
            Mage::throwException(Mage::helper('easybanner')->__('The name contains capital letters or disallowed symbols.'));
        }
        if ($this->isNumericPlaceholderIdentifier($object)) {
            Mage::throwException(Mage::helper('easybanner')->__('The name cannot consist only of numbers.'));
        }
        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if (null === $object->getDoNotUpdateLayout()) {
            /* layout_update */
            Mage::getModel('easybanner/layout')->buildLayoutUpdateByPlaceholder($object);
        }
        return $this;
    }

    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        /* cleanup layout_update table */
        Mage::getResourceModel('easybanner/layout')->removeUpdatesByPlaceholder($object->getId());
        return $this;
    }

    public function loadBannerIds(TM_EasyBanner_Model_Placeholder $object, $activeOnly = false)
    {
        $placeholderId = $object->getId();
        $bannerIds = array();
        if ($placeholderId) {
            $bannerIds = $this->lookupBannerIds($placeholderId, $activeOnly);
        }
        if ($activeOnly) {
            $object->setBannerIdsActive($bannerIds);
        } else {
            $object->setBannerIds($bannerIds);
        }
    }

    /**
     * Get banner ids, that linked with placeholder
     *
     * @param int $id placeholder_id
     * @param boolean $activeOnly
     * @return array
     */
    public function lookupBannerIds($id, $activeOnly = false)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('banner_placeholder' => $this->getTable('easybanner/banner_placeholder')), 'banner_id')
            ->where("banner_placeholder.placeholder_id = ?", $id);

        if ($activeOnly) {
            $select
                ->join(array('banner' => $this->getTable('easybanner/banner')),
                    'banner.banner_id = banner_placeholder.banner_id')
                ->where("banner.status = 1");
        }

        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Check whether banner identifier is numeric
     *
     * @param Mage_Core_Model_Abstract $object
     * @return bool
     */
    protected function isNumericPlaceholderIdentifier(Mage_Core_Model_Abstract $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('name'));
    }

    /**
     * Check whether page identifier is valid
     *
     *  @param Mage_Core_Model_Abstract $object
     *  @return bool
     */
    protected function isValidPlaceholderIdentifier(Mage_Core_Model_Abstract $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('name'));
    }
}