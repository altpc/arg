<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Block_Adminhtml_Rules_Edit_Tab_Content extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('prolabels_rules');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rules_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('prolabels')->__('Content (Product Page)'), 'class' => 'fieldset-wide'));
        $this->_addElementTypes($fieldset); //register own image element

        $fieldset->addField('product_position', 'select', array(
            'label'     => Mage::helper('prolabels')->__('Position'),
            'title'     => Mage::helper('prolabels')->__('Position'),
            'name'      => 'product_position',
            'options'   => array(
                'top-left'     => Mage::helper('prolabels')->__('top-left'),
                'top-center'   => Mage::helper('prolabels')->__('top-center'),
                'top-right'    => Mage::helper('prolabels')->__('top-right'),
                'middle-left'  => Mage::helper('prolabels')->__('middle-left'),
                'middle-center'=> Mage::helper('prolabels')->__('middle-center'),
                'middle-right' => Mage::helper('prolabels')->__('middle-right'),
                'bottom-left'  => Mage::helper('prolabels')->__('bottom-left'),
                'bottom-center'=> Mage::helper('prolabels')->__('bottom-center'),
                'bottom-right' => Mage::helper('prolabels')->__('bottom-right'),
                'content'      => Mage::helper('prolabels')->__('content')
            )
        ));

        $fieldset->addField('product_image', 'image', array(
            'name'      => 'product_image',
            'label'     => Mage::helper('prolabels')->__('Image'),
            'title'     => Mage::helper('prolabels')->__('Image')
        ));

        $fieldset->addField('product_image_text', 'text', array(
            'name'      => 'product_image_text',
            'label'     => Mage::helper('prolabels')->__('Image Text'),
            'title'     => Mage::helper('prolabels')->__('Image Text'),
            'after_element_html' => '<small>#attr:attribute_code# or #discount_percent# or #discount_amount# or #special_price# or #special_date# or #final_price# or #price# or #product_name# or #product_sku# or #stock_item#</small>',
        ));

        $fieldset->addField('product_custom_url', 'text', array(
            'name'      => 'product_custom_url',
            'label'     => Mage::helper('prolabels')->__('Label Custom Url'),
            'title'     => Mage::helper('prolabels')->__('Label Custom Url')
        ));

        if ($model->getId() == '2') {

            $fieldset->addField('product_min_stock', 'text', array(
                'name'      => 'product_min_stock',
                'label'     => Mage::helper('prolabels')->__('Display if Stock is lower then'),
                'title'     => Mage::helper('prolabels')->__('Display if Stock is lower then'),
            ));

            $fieldset->addField('product_out_stock', 'select', array(
                'label'     => Mage::helper('prolabels')->__('Enable Out of stock label'),
                'title'     => Mage::helper('prolabels')->__('Enable Out of stock label'),
                'name'      => 'product_out_stock',
                'options'   => array(
                    '1'     => Mage::helper('prolabels')->__('Yes'),
                    '0'      => Mage::helper('prolabels')->__('No'),
                ),
            ));

            $fieldset->addField('product_out_stock_image', 'image', array(
                'name'      => 'product_out_stock_image',
                'label'     => Mage::helper('prolabels')->__('Out of stock Image'),
                'title'     => Mage::helper('prolabels')->__('Out of stock Image')
            ));

            $fieldset->addField('product_out_text', 'text', array(
                'name'      => 'product_out_text',
                'label'     => Mage::helper('prolabels')->__('Out Of Stock Label Text'),
                'title'     => Mage::helper('prolabels')->__('Out Of Stock Label Text'),
            ));
        }


        $fieldset->addField('product_position_style', 'text', array(
            'name'      => 'product_position_style',
            'label'     => Mage::helper('prolabels')->__('Position Style'),
            'title'     => Mage::helper('prolabels')->__('Position Style'),
            'after_element_html' => '<small>Example: top:0px; left:0px;</small>',
        ));

        $fieldset->addField('product_font_style', 'text', array(
            'name'      => 'product_font_style',
            'label'     => Mage::helper('prolabels')->__('Font Style'),
            'title'     => Mage::helper('prolabels')->__('Font Style'),
            'after_element_html' => '<small>Example: color: #fff; font: bold 0.9em/11px Arial, Helvetica, sans-serif; letter-spacing: 0.01px;</small>',
        ));

        $fieldset->addField('product_round_method', 'select', array(
            'label'     => Mage::helper('prolabels')->__('Round Method'),
            'title'     => Mage::helper('prolabels')->__('Round Method'),
            'name'      => 'product_round_method',
            'options'   => array(
                'round'     => Mage::helper('prolabels')->__('Math'),
                'ceil'      => Mage::helper('prolabels')->__('Ceil'),
                'floor'     => Mage::helper('prolabels')->__('Floor')
            ),
        ));

        $fieldset->addField('product_round', 'text', array(
            'name'      => 'product_round',
            'label'     => Mage::helper('prolabels')->__('Round'),
            'title'     => Mage::helper('prolabels')->__('Round'),
            'after_element_html' => '<small>Example: 0.1 or 0.01 or 1 or 10 or 100</small>',
        ));


        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getAdditionalElementTypes()
    {
        return array(
            'image' => Mage::getConfig()->getBlockClassName('prolabels/adminhtml_rules_helper_image')
        );
    }

}
