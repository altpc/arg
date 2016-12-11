<?php
/**
 * This is the part of 'Highlight' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 *
 * @author Templates-Master
 * @copyright Templates Master www.templates-master.com
 */

class TM_Highlight_Block_Product_Featured extends TM_Highlight_Block_Product_Attribute_Yesno
{
    const PAGE_TYPE = 'featured';

    protected $_title         = 'Featured Products';
    protected $_priceSuffix   = '-featured';
    protected $_attributeCode = 'featured';
    protected $_className     = 'highlight-featured';
}
