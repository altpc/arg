<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SearchAttributes
 *
 * @author user100500
 */
class TM_AjaxSearch_Adminhtml_Model_System_Config_Source_SearchAttributes
{
    public function toOptionArray()
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
                ->addIsSearchableFilter()
                ->addSearchableAttributeFilter()
                ->addToIndexFilter()
                ->addStoreLabel(Mage::app()->getStore()->getId())
                ->setOrder('main_table.attribute_id', 'asc')
                ->load();
        $result = array();
        foreach ($attributes as $attributesSearchable){
            $result[] = array(
                'value' => $attributesSearchable["attribute_code"],
                'label' => $attributesSearchable["frontend_label"]
            );
        }
        return $result;
    }
}
