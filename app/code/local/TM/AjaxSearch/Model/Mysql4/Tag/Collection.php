<?php

class TM_AjaxSearch_Model_Mysql4_Tag_Collection extends Mage_Tag_Model_Resource_Product_Collection
{

    public function addTagsFilter($tags)
    {
        if (!is_array($tags)) {
            $tags = array($tag);
        }
        $tags = array_filter($tags);
        $select = $this->getSelect();

        foreach ($tags as $tag) {
            $select->orWhere('t.name LIKE ?', "%{$tag}%");
        }

        return $this;
    }
}