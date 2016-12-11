<?php

class TM_NavigationPro_Model_Resource_Menu extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('navigationpro/menu', 'menu_id');
    }

    /**
     * Load all menu data at once
     *
     * @return TM_NavigationPro_Model_Resource_Menu_Collection
     */
    public function loadTreeByName($name)
    {
        /**
         * @var TM_NavigationPro_Model_Resource_Menu_Collection
         */
        $menus = Mage::getModel('navigationpro/menu')->getCollection()
            ->addChildMenus()
            ->addFieldToFilter('main_table.name', $name);
        $menus->addContentToResult(Mage::app()->getStore()->getId());
        $ids = array_keys($menus->getItems());

        // columns
        $columns = Mage::getModel('navigationpro/column')->getCollection()
            ->addFieldToFilter('menu_id', array('in' => $ids))
            ->addFieldToFilter('is_active', 1)
            ->setOrder('sort_order', 'ASC');
        $columns->addContentToResult(Mage::app()->getStore()->getId());

        $columnsToMenu = array();
        foreach ($columns as $column) {
            $columnsToMenu[$column->getMenuId()][] = $column;
        }
        foreach ($columnsToMenu as $menuId => $columns) {
            $menus->getItemById($menuId)->setColumns($columns);
        }

        // siblings
        $siblings = Mage::getModel('navigationpro/sibling')->getCollection()
            ->addFieldToFilter('menu_id', array('in' => $ids))
            ->addFieldToFilter('is_active', 1)
            ->setOrder('sort_order', 'ASC');
        $siblings->addContentToResult(Mage::app()->getStore()->getId());

        $siblingsToMenu = array();
        foreach ($siblings as $sibling) {
            $siblingsToMenu[$sibling->getMenuId()][] = $sibling;
        }
        foreach ($siblingsToMenu as $menuId => $siblings) {
            $menus->getItemById($menuId)->setSiblings($siblings);
        }

        return $menus;
    }

    /**
     * Load array of menu parents merged with category data
     *
     * @param string $path
     * @param bool $addCollectionData
     * @param bool $withRootNode
     * @return array
     */
    public function loadBreadcrumbsArray($path)
    {
        $pathIds    = explode('/', $path);
        $rootMenuId = array_shift($pathIds);
        $rootMenuId = str_replace('menu_', '', $rootMenuId);

        $result = array();
        if (!empty($pathIds)) {
            $adapter = $this->_getReadAdapter();
            $select = $adapter->select()
                ->from($this->getMainTable(), array('category_id', 'is_active'))
                ->where('category_id IN (?)', $pathIds)
                ->where('root_menu_id = ?', $rootMenuId);

            $result = $adapter->fetchAssoc($select);
        }
        return $result;
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
            'columns_mode',
            'display_in_navigation',
            'levels_per_dropdown',
            'style'
        );
        $data = array();
        foreach ($jsonFields as $key) {
            if ($object->hasData($key)) {
                $data[$key] = $object->getData($key);
            }
        }

        $object->setConfiguration(Mage::helper('core')->jsonEncode($data));

        return $this;
    }

    /**
     * @param TM_NavigationPro_Model_Resource_Menu
     * @return TM_NavigationPro_Model_Resource_Menu
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        // columns
        foreach ($object->getColumns() as $columnData) {
            $column = Mage::getModel('navigationpro/column');
            $column->setMenuId($object->getId())
                ->setStoreId($object->getStoreId());

            if (!empty($columnData['column_id'])) {
                $column->setId($columnData['column_id']);
            } else {
                unset($columnData['column_id']);
            }

            if (!empty($columnData['is_delete'])) {
                $column->delete();
            } else {
                unset($columnData['is_delete']);
                $column->addData($columnData)->save();
            }
        }

        // siblings
        foreach ($object->getSiblings() as $siblingData) {
            $sibling = Mage::getModel('navigationpro/sibling');
            $sibling->setMenuId($object->getId())
                ->setStoreId($object->getStoreId());

            if (!empty($siblingData['sibling_id'])) {
                $sibling->setId($siblingData['sibling_id']);
            } else {
                unset($siblingData['sibling_id']);
            }

            if (!empty($siblingData['is_delete'])) {
                $sibling->delete();
            } else {
                unset($siblingData['is_delete']);
                $sibling->addData($siblingData)->save();
            }
        }

        // menu content
        $table = $this->getTable('navigationpro/menu_content');
        $where = array(
            'menu_id = ?'  => (int) $object->getId(),
            'store_id = ?' => (int) $object->getStoreId()
        );
        $this->_getWriteAdapter()->delete($table, $where);

        $content = $object->getContent();
        $top     = isset($content['top']) ? $content['top'] : false;
        $bottom  = isset($content['bottom']) ? $content['bottom'] : false;
        $title   = isset($content['title']) ? $content['title'] : false;

        if (false !== $top || false !== $bottom || false !== $title) {
            $this->_getWriteAdapter()->insert($table, array(
                'menu_id'  => (int) $object->getId(),
                'store_id' => (int) $object->getStoreId(),
                'top'      => $top ? $top : '',
                'bottom'   => $bottom ? $bottom : '',
                'title'    => $title ? $title : ''
            ));
        }

        return parent::_afterSave($object);
    }

    public function getContent($menuId, $storeId)
    {
        $storeIds = array(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, $storeId);
        $content  = array();

        $select = $this->getReadConnection()->select()
            ->from(
                array('mc' => $this->getTable('navigationpro/menu_content'))
            )
            ->where('mc.menu_id = ?', $menuId)
            ->where('mc.store_id IN (?)', $storeIds)
            ->order('mc.store_id DESC')
            ->limit(1);

        return $this->getReadConnection()->fetchRow($select);
    }
}
