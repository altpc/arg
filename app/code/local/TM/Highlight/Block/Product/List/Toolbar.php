<?php

/**
 * Class is used for highlight blocks except TM_Highlight_Block_Page
 * Blocked functionality:
 *  - order
 *  - pagination
 */
class TM_Highlight_Block_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    /**
     * Overriden to prevent accident offset on category page.
     *
     * @todo Will be used for ajax requests to allow pagination inside blocks
     * @var string
     */
    protected $_pageVarName = 'highlight_page';

    /**
     * Collection is ordered in TM_Highlight_Block_Product_List
     * @return false
     */
    public function getCurrentOrder()
    {
        return false;
    }
}
