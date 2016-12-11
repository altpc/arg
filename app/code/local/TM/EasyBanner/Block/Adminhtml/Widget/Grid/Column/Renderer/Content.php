<?php

class TM_EasyBanner_Block_Adminhtml_Widget_Grid_Column_Renderer_Content
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $value = $this->_getValue($row);
        if (is_array($value)) {
            $html = '<img ';
            $html .= 'src="' . $value['image'] . '"';
            $html .= 'style="' . $this->getColumn()->getImageCss() . '"/>';
            return $html;
        } else {
            $html = $this->escapeHtml($value);
        }
        return $html;
    }

}
