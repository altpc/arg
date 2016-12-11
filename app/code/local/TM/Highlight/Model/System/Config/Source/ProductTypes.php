<?php

class TM_Highlight_Model_System_Config_Source_ProductTypes
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return Mage::getSingleton('catalog/product_type')->getOptions();
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = array();
        foreach ($this->toOptionArray() as $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }

}
