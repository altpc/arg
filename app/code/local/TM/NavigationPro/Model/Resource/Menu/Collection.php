<?php

class TM_NavigationPro_Model_Resource_Menu_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('navigationpro/menu');
    }

    /**
     * @param int $storeId
     * @return TM_NavigationPro_Model_Resource_Menu_Collection
     */
    public function addContentToResult($storeId)
    {
        $storeIds = array(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, $storeId);
        $menuIds = $this->getColumnValues('menu_id');
        $contentToMenu = array();

        if (count($menuIds)) {
            $select = $this->getConnection()->select()
                ->from(
                    array('mc' => $this->getTable('navigationpro/menu_content'))
                )
                ->where('mc.menu_id IN (?)', $menuIds)
                ->where('mc.store_id IN (?)', $storeIds)
                ->order('mc.store_id DESC');

            $result = $this->getConnection()->fetchAll($select);
            foreach ($result as $row) {
                if (isset($contentToMenu[$row['menu_id']])) {
                    continue;
                }
                $contentToMenu[$row['menu_id']] = $row;
            }
        }

        foreach ($this as $item) {
            if (!isset($contentToMenu[$item->getId()])) {
                continue;
            }
            $item->addData($contentToMenu[$item->getId()]);
        }

        return $this;
    }

    /**
     * Add all child menus to current collection
     * This method resets the COLUMNS part in select
     *
     * @return TM_NavigationPro_Model_Resource_Menu_Collection
     */
    public function addChildMenus()
    {
        $this->getSelect()
            ->reset('columns') // we need columns from main_table2 only
            ->joinLeft(
                array('main_table2' => $this->getResource()->getMainTable()),
                'main_table.menu_id = main_table2.root_menu_id'
                    . ' OR main_table.menu_id = main_table2.menu_id'
            );

        return $this;
    }

    public function toOptionArray($valueField='id', $labelField='name', $additional=array())
    {
        return $this->_toOptionArray($valueField, $labelField, $additional);
    }

    public function setIsLoaded($flag)
    {
        return $this->_setIsLoaded($flag);
    }
}
