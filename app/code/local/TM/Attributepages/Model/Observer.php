<?php

class TM_Attributepages_Model_Observer
{
    public function updatePageOptionsOnAttributeSave($observer)
    {
        $attribute = $observer->getAttribute();

        $attributepagesOptions = Mage::getModel('attributepages/entity')
            ->getCollection()
            ->addOptionOnlyFilter()
            ->addFieldToFilter('attribute_id', $attribute->getAttributeId());
        $existingIds = $attributepagesOptions->getColumnValues('option_id');

        $eavOptions = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setAttributeFilter($attribute->getAttributeId())
            ->addFieldToFilter('main_table.option_id', array('nin' => $existingIds));
        $table = Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value');
        $eavOptions->getSelect()
            ->joinLeft(
                array('sort_alpha_value' => $table),
                'sort_alpha_value.option_id = main_table.option_id AND sort_alpha_value.store_id = 0',
                array('value')
            );

        foreach ($eavOptions as $option) {
            $entity = Mage::getModel('attributepages/entity');
            $entity->importOptionData($option);
            try {
                $entity->save();
            } catch (Exception $e) {
                //
            }
        }
    }
}
