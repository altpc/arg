<?php
class TM_Easyslide_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getEffectOptionsData()
    {
        $data = array();

        $data[] = array(
            'value' => 'random',
            'label' => Mage::helper('easyslide')->__('Random')
        );
        $data[] = array(
            'value' =>'sliceDown',
            'label' => Mage::helper('easyslide')->__('Slice Down'));

        $data[] = array(
            'value' => 'sliceDownLeft',
            'label' => Mage::helper('easyslide')->__('Slice Down Left'));
        $data[] = array(
            'value' => 'sliceDownRight',
            'label' => Mage::helper('easyslide')->__('Slice Down Right'));
        $data[] = array(
            'value' => 'sliceUp',
            'label' => Mage::helper('easyslide')->__('Slice Up'));
        $data[] = array(
            'value' => 'sliceUpLeft',
            'label' => Mage::helper('easyslide')->__('Slice Up Left'));
        $data[] = array(
            'value' => 'sliceUpRight',
            'label' => Mage::helper('easyslide')->__('Slice Up Right'));

        $data[] = array(
            'value' => 'sliceUpDown',
            'label' => Mage::helper('easyslide')->__('Slice Up Down'));
        $data[] = array(
            'value' => 'sliceUpDownLeft',
            'label' => Mage::helper('easyslide')->__('Slice Up Down Left'));

        $data[] = array(
            'value' => 'sliceUpDownRight',
            'label' => Mage::helper('easyslide')->__('Slice Up Down Right'));
        $data[] = array(
            'value' => 'fold',
                'label' => Mage::helper('easyslide')->__('Fold'));
        $data[] = array(
            'value' => 'fade',
            'label' =>  Mage::helper('easyslide')->__('Fade'));
        $data[] = array(
            'value' => 'slideInRight',
            'label' => Mage::helper('easyslide')->__('Slide In Right'));
        $data[] = array(
            'value' => 'slideInLeft',
            'label' => Mage::helper('easyslide')->__('Slide In Left'));
        $data[] = array(
            'value' => 'boxRandom',
            'label' => Mage::helper('easyslide')->__('Box Random'));
        $data[] = array(
            'value' => 'boxRain',
            'label' => Mage::helper('easyslide')->__('Box Rain'));
        $data[] = array(
            'value' => 'boxRainReverse',
            'label' => Mage::helper('easyslide')->__('Box Rain Reverse'));
        $data[] = array(
            'value' => 'boxRainGrow',
            'label' => Mage::helper('easyslide')->__('Box Rain Grow'));
        $data[] = array(
            'value' => 'boxRainGrowReverse',
            'label' => Mage::helper('easyslide')->__('Box Rain Grow Reverse'));

        return $data;
    }
}
