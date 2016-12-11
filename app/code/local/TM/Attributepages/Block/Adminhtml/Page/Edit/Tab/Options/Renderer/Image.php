<?php

class TM_Attributepages_Block_Adminhtml_Page_Edit_Tab_Options_Renderer_Image
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_type = 'image';

    public function render(Varien_Object $row)
    {
        $output = '<div style="float: left; width: 25px; height: 25px;">';
        if ($image = $row->getData($this->_type)) {
            $output .= '<img src="' . Mage::getBaseUrl('media')
                . TM_Attributepages_Model_Entity::IMAGE_PATH
                . $image
                . '"'
                . 'alt="' . $row->getIdentifier() . '" width="25" height="25"/>';
        }
        $output .= '</div>';

        $optionId = $row->getOptionId();
        $output .= '<input type="file" style="width: 180px;" name="option_' . $optionId . '_' . $this->_type . '"/>';
        $output .= '<input type="hidden" value="' . $row->getEntityId() . '" name="option['. $optionId .'][entity_id]"/>';
        return $output;
    }
}
