<?php

class TM_NavigationPro_Model_Resource_Column extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('navigationpro/column', 'column_id');
    }

    /**
     * Process column data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     * @return TM_NavigationPro_Model_Resource_Column
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $jsonFields = array(
            'type',             'style',                'width',
            'columns_count',    'levels_per_dropdown',  'levels_to_load',
            'direction',        'max_items_count',      'css_id',
            'css_class',        'css_styles',           'is_active_exception'
        );
        $data = array();
        foreach ($jsonFields as $key) {
            if (!$object->hasData($key)) {
                continue;
            }
            $data[$key] = $object->getData($key);

            if ('is_active_exception' === $key && is_array($data[$key])) {
                foreach ($data[$key] as $i => $values) {
                    if (!empty($data[$key][$i]['is_delete'])) {
                        unset($data[$key][$i]);
                    } else {
                        unset($data[$key][$i]['is_delete']);
                    }

                    if (empty($data[$key][$i]['expression'])) {
                        unset($data[$key][$i]);
                    }
                }
                $data[$key] = array_values($data[$key]);
            }
        }

        $object->setConfiguration(Mage::helper('core')->jsonEncode($data));

        return $this;
    }

    /**
     * Assign column content to store views
     *
     * @param Mage_Core_Model_Abstract $object
     * @return TM_NavigationPro_Model_Resource_Column
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $table = $this->getTable('navigationpro/column_content');
        $where = array(
            'column_id = ?' => (int) $object->getId(),
            'store_id = ?'  => (int) $object->getStoreId()
        );
        $this->_getWriteAdapter()->delete($table, $where);

        if (null !== $object->getTitle() || null !== $object->getContent()) {
            $this->_getWriteAdapter()->insert($table, array(
                'column_id' => (int) $object->getId(),
                'store_id'  => (int) $object->getStoreId(),
                'title'     => $object->getTitle(),
                'content'   => $object->getContent()
            ));
        }

        return parent::_afterSave($object);
    }
}
