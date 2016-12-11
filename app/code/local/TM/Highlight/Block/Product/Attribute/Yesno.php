<?php
/**
 * This is the part of 'Highlight' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 *
 * @author Templates-Master
 * @copyright Templates Master www.templates-master.com
 */

class TM_Highlight_Block_Product_Attribute_Yesno extends TM_Highlight_Block_Product_List
{
    protected function _beforeToHtml()
    {
        $collection = $this->getCollection();

        try {
            $attributeCode = $this->getAttributeCode();
            if (!$collection->getAttribute($attributeCode)) { // Mage 1.6.0.0 fix
                throw new Exception("Attribute {$attributeCode} not found");
            }
            $collection->addAttributeToFilter("{$attributeCode}", array('Yes' => true));
        } catch (Exception $e) {
            $this->setTemplate(null);
            $this->setCustomTemplate(null);
        }

        return parent::_beforeToHtml();
    }
}
