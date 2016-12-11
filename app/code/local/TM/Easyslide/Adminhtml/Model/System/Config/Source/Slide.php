<?php
class TM_Easyslide_Adminhtml_Model_System_Config_Source_Slide
{
     public function toOptionArray()
    {
        $slides = Mage::getResourceModel('easyslide/easyslide')->loadSliders();
        $data = array();

        foreach ($slides as $slide) {
            $data[] = array('value' => $slide['identifier'], 'label' => $slide['title']);
        }
        return $data;
    }
}
