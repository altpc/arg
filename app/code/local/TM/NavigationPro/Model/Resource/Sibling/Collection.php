<?php

class TM_NavigationPro_Model_Resource_Sibling_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('navigationpro/sibling');
    }

    /**
     * @param int $storeId
     * @return TM_NavigationPro_Model_Resource_Sibling_Collection
     */
    public function addContentToResult($storeId)
    {
        $storeIds = array(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, $storeId);
        $siblingIds = $this->getColumnValues('sibling_id');
        $contentToSibling = array();

        if (count($siblingIds)) {
            $select = $this->getConnection()->select()
                ->from(
                    array('cc' => $this->getTable('navigationpro/sibling_content'))
                )
                ->where('cc.sibling_id IN (?)', $siblingIds)
                ->where('cc.store_id IN (?)', $storeIds)
                ->order('cc.store_id DESC');

            $result = $this->getConnection()->fetchAll($select);
            foreach ($result as $row) {
                if (isset($contentToSibling[$row['sibling_id']])) {
                    continue;
                }
                $contentToSibling[$row['sibling_id']] = $row;
            }
        }

        foreach ($this as $item) {
            if (!isset($contentToSibling[$item->getId()])) {
                continue;
            }
            $item->addData($contentToSibling[$item->getId()]);
        }

        return $this;
    }
}
