<?php
/**
 * This is the part of 'Highlight' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 *
 * @author Templates-Master
 * @copyright Templates Master www.templates-master.com
 */

class TM_Highlight_Block_Product_Special extends TM_Highlight_Block_Product_Attribute_Date
{
    const PAGE_TYPE = 'onsale';

    protected $_title         = 'Special Products';
    protected $_priceSuffix   = '-special';
    protected $_attributeCode = 'special_from_date,special_to_date';
    protected $_className     = 'highlight-special';

    protected function _beforeToHtml()
    {
        $this->getCollection()->addAttributeToFilter('special_price', array('gt' => 0));
        return parent::_beforeToHtml();
    }
}
