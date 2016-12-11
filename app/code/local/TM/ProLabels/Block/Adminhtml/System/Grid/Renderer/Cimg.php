<?php
class TM_ProLabels_Block_Adminhtml_System_Grid_Renderer_Cimg extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }

    protected function _getValue(Varien_Object $row)
    {
        $val = $row->getData($this->getColumn()->getIndex());
        if (!$val) { return ""; }
        $val = str_replace("no_selection", "", $val);
        $url = Mage::getBaseUrl('media') . 'prolabel' . DS . $val;
        $out = "<img src=". $url ." />";
        return $out;
    }
}
