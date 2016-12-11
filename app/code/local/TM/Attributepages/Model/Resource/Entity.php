<?php

class TM_Attributepages_Model_Resource_Entity extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('attributepages/entity', 'entity_id');
    }

    /**
     * Prepare serialized_configuration column
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$this->getIsUniquePageToStores($object)) {
            Mage::throwException(Mage::helper('cms')->__('A page URL key for specified store already exists.'));
        }

        // remove spaces from url
        $url = $object->getIdentifier();
        $url = str_replace(array(' ', '+'), '-', $url);
        $object->setIdentifier($url);

        $options = $object->getExcludedOptionIds();
        if (is_array($options)) {
            $object->setExcludedOptionIds(
                implode(TM_Attributepages_Model_Entity::DELIMITER, $options)
            );
        } elseif (strstr($options, '&')) { // grid serializer uses & to concat values
            $object->setExcludedOptionIds(
                str_replace('&', TM_Attributepages_Model_Entity::DELIMITER, $options)
            );
        }

        $displaySettings = array(
            'display_mode',
            'listing_mode',
            'column_count',
            'group_by_first_letter',
            // 'children_count',
            'image_width',
            'image_height'
        );
        $data = array();
        foreach ($displaySettings as $key) {
            if ($object->hasData($key)) {
                $data[$key] = $object->getData($key);
            }
        }
        $object->setDisplaySettings(Mage::helper('core')->jsonEncode($data));

        return parent::_beforeSave($object);
    }

    /**
     * Assign entity to store views
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table  = $this->getTable('attributepages/entity_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = array(
                'entity_id = ?'   => (int) $object->getId(),
                'store_id IN (?)' => $delete
            );
            $this->_getWriteAdapter()->delete($table, $where);
        }

        if ($insert) {
            $data = array();
            foreach ($insert as $storeId) {
                $data[] = array(
                    'entity_id' => (int) $object->getId(),
                    'store_id'  => (int) $storeId
                );
            }
            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param Mage_Core_Model_Abstract $object
     * @param mixed $value
     * @param string $field
     * @return TM_Attributepages_Model_Resource_Entity
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'identifier';
        }
        return parent::load($object, $value, $field);
    }

    /**
     * Perform operations after object load
     *
     * @param Mage_Core_Model_Abstract $object
     * @return TM_Attributepages_Model_Resource_Entity
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param TM_Attributepages_Model_Entity $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) { // for the frontend only
            $storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID, (int)$object->getStoreId());
            $select->join(
                array('entity_store' => $this->getTable('attributepages/entity_store')),
                $this->getMainTable() . '.entity_id = entity_store.entity_id',
                array())
                ->where('use_for_attribute_page = ?', 1)
                ->where('entity_store.store_id IN (?)', $storeIds)
                ->order('entity_store.store_id DESC')
                ->limit(1);
        }

        return $select;
    }

    /**
     * Retrieve load select with filter by identifier, store and activity
     *
     * @param string $identifier
     * @param int|array $store
     * @param int $isActive
     * @return Varien_Db_Select
     */
    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('entity' => $this->getMainTable()))
            ->join(
                array('entity_store' => $this->getTable('attributepages/entity_store')),
                'entity.entity_id = entity_store.entity_id',
                array())
            ->where('entity.identifier = ?', $identifier)
            ->where('entity_store.store_id IN (?)', $store);

        if (!is_null($isActive)) {
            $select->where('entity.use_for_attribute_page = ?', $isActive);
        }

        return $select;
    }

    /**
     * Check for unique of identifier of page to selected store(s).
     *
     * @param Mage_Core_Model_Abstract $object
     * @return bool
     */
    public function getIsUniquePageToStores(Mage_Core_Model_Abstract $object)
    {
        if (Mage::app()->isSingleStoreMode() || !$object->hasStores()) {
            $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID);
        } else {
            $stores = (array)$object->getData('stores');
        }

        $select = $this->_getLoadByIdentifierSelect($object->getData('identifier'), $stores);

        if ($object->getId()) {
            $select->where('entity_store.entity_id <> ?', $object->getId());
        }

        if ($this->_getWriteAdapter()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     * Check if entity identifier exist for specific store
     * return entity id if entity exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID, $storeId);
        $select = $this->_getLoadByIdentifierSelect($identifier, $stores, 1);
        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns('entity.entity_id')
            ->order('entity_store.store_id DESC')
            ->limit(1);

        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($pageId)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('attributepages/entity_store'), 'store_id')
            ->where('entity_id = ?',(int)$pageId);

        return $adapter->fetchCol($select);
    }
}
