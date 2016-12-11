<?php

class TM_NavigationPro_Model_Resource_Column_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('navigationpro/column');
    }

    /**
     * @param int $storeId
     * @return TM_NavigationPro_Model_Resource_Column_Collection
     */
    public function addContentToResult($storeId)
    {
        $storeIds = array(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, $storeId);
        $columnIds = $this->getColumnValues('column_id');
        $contentToColumn = array();

        if (count($columnIds)) {
            $select = $this->getConnection()->select()
                ->from(
                    array('cc' => $this->getTable('navigationpro/column_content'))
                )
                ->where('cc.column_id IN (?)', $columnIds)
                ->where('cc.store_id IN (?)', $storeIds)
                ->order('cc.store_id DESC');

            $result = $this->getConnection()->fetchAll($select);
            foreach ($result as $row) {
                if (isset($contentToColumn[$row['column_id']])) {
                    continue;
                }
                $contentToColumn[$row['column_id']] = $row;
            }
        }

        foreach ($this as $item) {
            if (!isset($contentToColumn[$item->getId()])) {
                continue;
            }
            $item->addData($contentToColumn[$item->getId()]);
        }

        return $this;
    }
}
