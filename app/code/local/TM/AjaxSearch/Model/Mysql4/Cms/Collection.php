<?php
class TM_AjaxSearch_Model_Mysql4_Cms_Collection extends Mage_Cms_Model_Resource_Page_Collection
{

    public function setQueryFilter($query)
    {
        $andWhere = array();
        foreach (explode(' ', trim($query)) as $word) {

            $this->addFieldToFilter(
                'title', array('like'=> '%' . $word .'%')
            );

            $andWhere[] = $this->_getConditionSql(
                'title', array('like' => '%' . $word . '%')
            );
        }
        $this->getSelect()->orWhere(implode(' AND ', $andWhere));

        return $this;
    }
}