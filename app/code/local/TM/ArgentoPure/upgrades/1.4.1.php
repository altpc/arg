<?php

class TM_ArgentoPure_Upgrade_1_4_1 extends TM_Core_Model_Module_Upgrade
{
    public function up()
    {
        // change the description position from top to the bottom
        $slider = Mage::getModel('easyslide/easyslide')->load('argento_pure');
        if (!$slider->getId()) {
            return;
        }

        $slides = Mage::getResourceModel('easyslide/easyslide_slides_collection')
            ->addFieldToFilter('slider_id', $slider->getId());
        foreach ($slides as $slide) {
            $slide->setDescPos(3)->save();
        }
    }
}
