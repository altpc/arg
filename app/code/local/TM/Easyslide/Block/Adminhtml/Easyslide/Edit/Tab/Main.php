<?php

class TM_Easyslide_Block_Adminhtml_Easyslide_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('slider');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('easyslide_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
             array(
                 'legend'=>Mage::helper('easyslide')->__('General'),
                 'class' => 'fieldset-wide'
             )
        );

        if ($model->getEasyslideId()) {
            $fieldset->addField('easyslide_id', 'hidden', array(
                'name' => 'easyslide_id',
            ));
        }

        $fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => Mage::helper('easyslide')->__('Title'),
            'title'     => Mage::helper('easyslide')->__('Title'),
            'required'  => true
        ));

        $fieldset->addField('identifier', 'text', array(
            'name'      => 'identifier',
            'label'     => Mage::helper('easyslide')->__('Identifier'),
            'title'     => Mage::helper('easyslide')->__('Identifier'),
            'required'  => true
        ));

        $fieldset->addField('slider_type', 'select', array(
            'label'     => Mage::helper('easyslide')->__('Slider Type'),
            'title'     => Mage::helper('easyslide')->__('Slider Type'),
            'name'      => 'slider_type',
            'required'  => true,
            'options'   => array(
                '0' => Mage::helper('easyslide')->__('Prototype Slider'),
                '1' => Mage::helper('easyslide')->__('Nivo Slider (jQuery)')
            )
        ));

        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('easyslide')->__('Status'),
            'title'     => Mage::helper('easyslide')->__('Status'),
            'name'      => 'status',
            'required'  => true,
            'options'   => array(
                '1' => Mage::helper('easyslide')->__('Enabled'),
                '0' => Mage::helper('easyslide')->__('Disabled')
            )
        ));

        $prototype = $form->addFieldset(
            'prototype_fieldset',
            array(
                'legend'=>Mage::helper('easyslide')->__('Prototype Slider Options'),
                'class' => 'fieldset-wide'
            )
        );

        $prototype->addField('controls_type', 'select', array(
            'label'     => Mage::helper('easyslide')->__('Controls type'),
            'title'     => Mage::helper('easyslide')->__('Controls type'),
            'name'      => 'controls_type',
            'required'  => false,
            'options'   => array(
                'number' => Mage::helper('easyslide')->__('Numbers'),
                'arrow' => Mage::helper('easyslide')->__('Arrows')
            )
        ));

        $prototype->addField('width', 'text', array(
            'name'      => 'width',
            'label'     => Mage::helper('easyslide')->__('Width'),
            'title'     => Mage::helper('easyslide')->__('Width'),
            'required'  => false
        ));

        $prototype->addField('height', 'text', array(
            'name'      => 'height',
            'label'     => Mage::helper('easyslide')->__('Height'),
            'title'     => Mage::helper('easyslide')->__('Height'),
            'required'  => false
        ));

        $prototype->addField('effect', 'select', array(
            'label'     => Mage::helper('easyslide')->__('Effect'),
            'title'     => Mage::helper('easyslide')->__('Effect'),
            'name'      => 'effect',
            'required'  => false,
            'options'   => array(
                'scroll' => Mage::helper('easyslide')->__('Scroll'),
                'speedscroll' => Mage::helper('easyslide')->__('Speedscroll'),
                'fade' => Mage::helper('easyslide')->__('Fade'),
                'blend' => Mage::helper('easyslide')->__('Blend'),
                'mosaic' => Mage::helper('easyslide')->__('Mosaic')
            )
        ));

        $prototype->addField('duration', 'text', array(
            'name'      => 'duration',
            'label'     => Mage::helper('easyslide')->__('Duration'),
            'title'     => Mage::helper('easyslide')->__('Duration'),
            'required'  => false
        ));

        $prototype->addField('frequency', 'text', array(
            'name'      => 'frequency',
            'label'     => Mage::helper('easyslide')->__('Frequency'),
            'title'     => Mage::helper('easyslide')->__('Frequency'),
            'required'  => false
        ));

        $prototype->addField('autoglide', 'select', array(
            'label'     => Mage::helper('easyslide')->__('Autoglide'),
            'title'     => Mage::helper('easyslide')->__('Autoglide'),
            'name'      => 'autoglide',
            'required'  => false,
            'options'   => array(
                '1' => Mage::helper('easyslide')->__('Enabled'),
                '0' => Mage::helper('easyslide')->__('Disabled')
            )
        ));

        $nivo = $form->addFieldset(
            'nivo_fieldset',
            array(
                'legend'=>Mage::helper('easyslide')->__('Nivo Slider Options'),
                'class' => 'fieldset-wide'
            )
        );

        $nivo->addField('theme', 'select', array(
            'label'     => Mage::helper('easyslide')->__('Theme'),
            'title'     => Mage::helper('easyslide')->__('Theme'),
            'name'      => 'theme',
            'required'  => false,
            'options'   => array(
                'default' => Mage::helper('easyslide')->__('Default'),
                'light' => Mage::helper('easyslide')->__('Light'),
                'dark' => Mage::helper('easyslide')->__('Dark'),
                'bar' => Mage::helper('easyslide')->__('Bar'),
            )
        ));

        $nivo->addField('nivoeffect', 'multiselect', array(
            'label'     => Mage::helper('easyslide')->__('Effect'),
            'title'     => Mage::helper('easyslide')->__('Effect'),
            'name'      => 'nivoeffect[]',
            'required'  => false,
            'values'    => Mage::helper('easyslide')->getEffectOptionsData()
        ));

        $nivo->addField('slices', 'text', array(
            'name'      => 'slices',
            'label'     => Mage::helper('easyslide')->__('Slices(For slice animations)'),
            'title'     => Mage::helper('easyslide')->__('Slices(For slice animations)'),
            'required'  => false
        ));

        $nivo->addField('boxCols', 'text', array(
            'name'      => 'boxCols',
            'label'     => Mage::helper('easyslide')->__('Box Cols(For box animations)'),
            'title'     => Mage::helper('easyslide')->__('Box Cols(For box animations)'),
            'required'  => false
        ));

        $nivo->addField('boxRows', 'text', array(
            'name'      => 'boxRows',
            'label'     => Mage::helper('easyslide')->__('Box Rows(For box animations)'),
            'title'     => Mage::helper('easyslide')->__('Box Rows(For box animations)'),
            'required'  => false
        ));

        $nivo->addField('animSpeed', 'text', array(
            'name'      => 'animSpeed',
            'label'     => Mage::helper('easyslide')->__('Slide transition speed'),
            'title'     => Mage::helper('easyslide')->__('Slide transition speed'),
            'required'  => false
        ));

        $nivo->addField('pauseTime', 'text', array(
            'name'      => 'pauseTime',
            'label'     => Mage::helper('easyslide')->__('How long each slide will show'),
            'title'     => Mage::helper('easyslide')->__('How long each slide will show'),
            'required'  => false
        ));

        $nivo->addField('directionNav', 'select', array(
            'label'     => Mage::helper('easyslide')->__('Next & Prev navigation'),
            'title'     => Mage::helper('easyslide')->__('Next & Prev navigation'),
            'name'      => 'directionNav',
            'required'  => false,
            'options'   => array(
                'true' => Mage::helper('easyslide')->__('Yes'),
                'false' => Mage::helper('easyslide')->__('No')
            )
        ));
        $nivo->addField('controlNav', 'select', array(
            'label'     => Mage::helper('easyslide')->__('Use thumbnails for Control Nav'),
            'title'     => Mage::helper('easyslide')->__('Use thumbnails for Control Nav'),
            'name'      => 'controlNav',
            'required'  => false,
            'options'   => array(
                'true' => Mage::helper('easyslide')->__('Yes'),
                'false' => Mage::helper('easyslide')->__('No')
            )
        ));

        $nivo->addField('pauseOnHover', 'select', array(
            'label'     => Mage::helper('easyslide')->__('Stop animation while hovering'),
            'title'     => Mage::helper('easyslide')->__('Stop animation while hovering'),
            'name'      => 'pauseOnHover',
            'required'  => false,
            'options'   => array(
                'true' => Mage::helper('easyslide')->__('Yes'),
                'false' => Mage::helper('easyslide')->__('No')
            )
        ));

        $nivo->addField('manualAdvance', 'select', array(
            'label'     => Mage::helper('easyslide')->__('Autoglide'),
            'title'     => Mage::helper('easyslide')->__('Autoglide'),
            'name'      => 'manualAdvance',
            'required'  => false,
            'options'   => array(
                'false' => Mage::helper('easyslide')->__('Yes'),
                'true' => Mage::helper('easyslide')->__('No')
            )
        ));

        $form->setValues(array_merge(
            array(
                'duration'  => '0.5',
                'frequency' => '4.0',
                'slices'    => '15',
                'boxCols'   => '8',
                'boxRows'   => '4',
                'animSpeed' => '500',
                'pauseTime' => '3000'
            ),
            $model->getData()
        ));

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
