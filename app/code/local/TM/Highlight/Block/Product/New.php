<?php
/**
 * This is the part of 'Highlight' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 *
 * @author Templates-Master
 * @copyright Templates Master www.templates-master.com
 */

class TM_Highlight_Block_Product_New extends TM_Highlight_Block_Product_Attribute_Date
{
    const PAGE_TYPE = 'new';

    protected $_title         = 'New Products';
    protected $_priceSuffix   = '-new';
    protected $_attributeCode = 'news_from_date,news_to_date';
    protected $_className     = 'highlight-new';
}
