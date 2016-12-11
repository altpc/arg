<?php

class TM_AskIt_Block_Adminhtml_AskIt_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $id = $this->getRequest()->getParam('id');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $id)),
            'method' => 'post'
        ));

        if (Mage::registry('askit_data')) {
            $data = Mage::registry('askit_data')->getData();
        }

        $fieldset = $form->addFieldset(
            'askit_form',
            array('legend' => Mage::helper('askit')->__('Item information'))
        );
        $fieldset->addField('id', 'hidden', array(
            'name'      => 'id'
        ));
        $fieldset->addField('text', 'textarea', array(
            'label'     => Mage::helper('askit')->__('Text'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'text',
        ));
        $fieldset->addField('hint', 'text', array(
            'label'     => Mage::helper('askit')->__('Hint'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'hint',
        ));

        if (null !== $id) {
            $fieldset->addField('customer_name', 'label', array(
                'label'     => Mage::helper('askit')->__('Customer'),
                'class'     => 'required-entry',
//                'disabled'  => null !== $id ? true : false,
                'name'      => 'customer_name'
            ));

            $item = Mage::helper('askit')->getItem($data);

            $data['item_type_name'] = $item->getTypeName();
            $fieldset->addField('item_type_name', 'label', array(
                'label'     => Mage::helper('askit')->__('Main Item Type'),
                // 'class'     => 'required-entry',
//                'disabled'  => null !== $id ? true : false,
                'name'      => 'item_type_name'
            ));

            $fieldset->addField('item_type_id', 'hidden', array(
                // 'class'     => 'required-entry',
                'name'      => 'item_type_id'
            ));

            $data['item_name'] = $item->getName();
            $fieldset->addField('item_name', 'link', array(
                'href'      => $item->getBackendItemUrl(),
                'label'     => Mage::helper('askit')->__('Main Item Name'),
                'disabled'  => true,
                'name'      => 'item_name'
            ));

            $fieldset->addField('item_id', 'hidden', array(
                // 'class'     => 'required-entry',
                'name'      => 'item_id'
            ));
        } else {

            $data['item_type_id'] = TM_AskIt_Model_Item_Type::PRODUCT_ID;
            $fieldset->addField('item_type_id', 'hidden', array(
                // 'class'     => 'required-entry',
                'name'      => 'item_type_id'
            ));

            $fieldset->addType(
                'autocompleter',
                'TM_AskIt_Block_Adminhtml_AskIt_Edit_Form_Element_Autocompleter'
            );
            $fieldset->addField('item_id', 'autocompleter', array(
                'label'              => Mage::helper('askit')->__('Product'),
                'name'               => 'item_id',
                'autocompleterUrl'   => Mage::getUrl('*/*/product'),
                'autocompleterValue' => '',
//                    'required'           => true,
            ));
//            $fieldset->addField('product_id', 'text', array(
//                'label'     => Mage::helper('askit')->__('Product Id'),
//                'class'     => 'required-entry',
//                'name'      => 'product_id'
//            ));
        }

        if (!Mage::app()->isSingleStoreMode()) {
        	$fieldset->addField('store_id', 'select', array(
                'name'      => 'store_id',
                'label'     => Mage::helper('cms')->__('Store View'),
                'title'     => Mage::helper('cms')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')
                    ->getStoreValuesForForm(false, true),
            ));
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'store_id'
            ));
            // Mage::getModel('askit/message')->setStoreId(
            //     Mage::app()->getStore(true)->getId()
            // );
            $data['store_id'] = Mage::app()->getStore(true)->getId();
        }
        //
        $fieldset->addField('email', null !== $id ? 'label' : 'text', array(
            'label'     => Mage::helper('askit')->__('Email'),
            'class'     => 'required-entry',
            'name'      => 'email'
        ));

        $_statusses = array();
        foreach (TM_AskIt_Model_Status::getQuestionOptionArray() as $_status => $_label) {
            $_statusses[] = array(
                'value' => $_status,
                'label' => $_label
            );
        }

        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('askit')->__('Status'),
            'name'      => 'status',
            'required'  => true,
            'values'    => $_statusses,
        ));
        $fieldset->addField('private', 'select', array(
            'label'     => Mage::helper('askit')->__('Private'),
            'name'      => 'private',
            'values'    => array(
                array(
                    'value' => 0,
                    'label' => Mage::helper('askit')->__('Public'),
                ),
                array(
                    'value' => 1,
                    'label' => Mage::helper('askit')->__('Private'),
                )
            ),
        ));
        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('created_time', 'date', array(
            'label'     => Mage::helper('askit')->__('Create date'),
            'required'  => true,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso,
            'name'      => 'created_time',
        ));

        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
