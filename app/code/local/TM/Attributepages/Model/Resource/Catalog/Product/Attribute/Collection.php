<?php

class TM_Attributepages_Model_Resource_Catalog_Product_Attribute_Collection
    extends Mage_Catalog_Model_Resource_Product_Attribute_Collection
{
    public function toOptionArray()
    {
        return $this->_toOptionArray('attribute_id', 'frontend_label');
    }

    public function toOptionHash()
    {
        return $this->_toOptionHash('attribute_id', 'frontend_label');
    }
}
