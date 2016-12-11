<?php

class TM_Attributepages_Block_Adminhtml_Page_Edit_Tab_Options_Renderer_Identifier
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return '<input type="text" class="input-text required-entry"
            value="' . $row->getIdentifier() . '"
            name="option['. $row->getOptionId() .'][identifier]"
        />';
    }
}
