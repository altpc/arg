<?php

class TM_EasyBanner_Model_Mysql4_Banner extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('easybanner/banner', 'banner_id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$this->isValidBannerIdentifier($object)) {
            Mage::throwException(Mage::helper('easybanner')->__('The name contains capital letters or disallowed symbols.'));
        }
        if ($this->isNumericBannerIdentifier($object)) {
            Mage::throwException(Mage::helper('easybanner')->__('The name cannot consist only of numbers.'));
        }

        if ($object->getIsDuplicate()) {
            return $this;
        }

        /* image */
        $value = $object->getImage();
        $path = Mage::getBaseDir('media') . DS . trim(Mage::getStoreConfig('easybanner/general/image_folder'), ' /\\') . DS;

        if (is_array($value) && !empty($value['delete'])) {
            @unlink($path . $value['value']);
            $object->setImage('');
            return $this;
        }

        if (empty($_FILES['image']['name'])) {
            if (is_array($value)) {
                $object->setImage($value['value']);
            }
            return $this;
        }

        try {
            $uploader = new Varien_File_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png', 'bmp'));
            $uploader->setAllowRenameFiles(true);
            $uploader->save($path);
            $object->setImage($uploader->getUploadedFileName());
        } catch (Exception $e) {
            $object->unsImage();
            throw $e;
            //return $this;
        }
        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $deleteWhere = $this->_getWriteAdapter()->quoteInto('banner_id = ?', $object->getId());

        /* placeholders */
        $this->_getWriteAdapter()->delete($this->getTable('easybanner/banner_placeholder'), $deleteWhere);
        foreach ($object->getPlaceholderIds() as $placeholderId) {
            $data = array(
                'banner_id'     => $object->getId(),
                'placeholder_id' => $placeholderId
            );
            $this->_getWriteAdapter()->insert($this->getTable('easybanner/banner_placeholder'), $data);
        }

        /* store_view */
        $this->_getWriteAdapter()->delete($this->getTable('easybanner/banner_store'), $deleteWhere);
        foreach ($object->getStoreIds() as $storeId) {
            $data = array(
                'banner_id' => $object->getId(),
                'store_id'  => $storeId
            );
            $this->_getWriteAdapter()->insert($this->getTable('easybanner/banner_store'), $data);
        }

        /* layout_update */
        Mage::getModel('easybanner/layout')->buildLayoutUpdateByBanner($object);

        return $this;
    }

    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        /* cleanup layout_update table */
        Mage::getResourceModel('easybanner/layout')->removeUpdatesByBanner($object->getId());
        return $this;
    }

    public function loadPlaceholderIds(TM_EasyBanner_Model_Banner $object, $activeOnly = false)
    {
        $bannerId = $object->getId();
        $placeholderIds = array();
        if ($bannerId) {
            $placeholderIds = $this->lookupPlaceholderIds($bannerId, $activeOnly);
        }
        if ($activeOnly) {
            $object->setPlaceholderIdsActive($placeholderIds);
        } else {
            $object->setPlaceholderIds($placeholderIds);
        }
    }

    /**
     * Get placeholder ids, to which specified item is assigned
     *
     * @param int $id
     * @param boolean $activeOnly
     * @return array
     */
    public function lookupPlaceholderIds($id, $activeOnly = false)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('banner_placeholder' => $this->getTable('easybanner/banner_placeholder')), 'placeholder_id')
            ->where("banner_placeholder.banner_id = ?", $id);

        if ($activeOnly) {
            $select
                ->join(array('placeholder' => $this->getTable('easybanner/placeholder')),
                    'placeholder.placeholder_id = banner_placeholder.placeholder_id')
                ->where("placeholder.status = 1");
        }

        return $this->_getReadAdapter()->fetchCol($select);
    }

    public function loadStoreIds(TM_EasyBanner_Model_Banner $object)
    {
        $bannerId = $object->getId();
        $storeIds = array();
        if ($bannerId) {
            $storeIds = $this->lookupStoreIds($bannerId);
        }
        $object->setStoreIds($storeIds);
    }

    /**
     * Get store ids, to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        return $this->_getReadAdapter()->fetchCol($this->_getReadAdapter()->select()
            ->from($this->getTable('easybanner/banner_store'), 'store_id')
            ->where("{$this->getIdFieldName()} = ?", $id)
        );
    }

    public function loadStatistics(TM_EasyBanner_Model_Banner $object)
    {
        $bannerId = $object->getId();
        $display_count = $clicks_count = 0;
        if ($bannerId) {
            $result = $this->_getReadAdapter()->fetchRow($this->_getReadAdapter()->select()
                ->from($this->getTable('easybanner/banner_statistic'), array(
                    'display_count' => new Zend_Db_Expr('SUM(display_count)'),
                    'clicks_count' => new Zend_Db_Expr('SUM(clicks_count)')
                ))
                ->group("{$this->getIdFieldName()}")
                ->where("{$this->getIdFieldName()} = ?", $bannerId));

            $display_count = $result['display_count'];
            $clicks_count = $result['clicks_count'];
        }
        $object->setDisplayCount($display_count);
        $object->setClicksCount($clicks_count);
    }

    /**
     * Check whether banner identifier is numeric
     *
     * @param Mage_Core_Model_Abstract $object
     * @return bool
     */
    protected function isNumericBannerIdentifier(Mage_Core_Model_Abstract $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     * Check whether page identifier is valid
     *
     *  @param Mage_Core_Model_Abstract $object
     *  @return bool
     */
    protected function isValidBannerIdentifier(Mage_Core_Model_Abstract $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }
}