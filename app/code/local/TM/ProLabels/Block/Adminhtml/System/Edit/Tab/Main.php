<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * ProLabels module for Magento - flexible label management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Block_Adminhtml_System_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model       = Mage::registry('prolabels_system_rules');
        $storeModel  = Mage::getModel('prolabels/sysstore');
        $data        = $storeModel->getStoreIds($model->getId());
        $storesArray = array();
        foreach ($data as $row) {
            $storesArray[] = $row['store_id'];
        }
        $model->setData('stores', $storesArray);

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rules_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('prolabels')->__('General Information'), 'class' => 'fieldset-wide'));
        $this->_addElementTypes($fieldset); //register own image element

        $fieldset->addField('system_id', 'hidden', array(
            'name' => 'system_id',
        ));
        if ($model->getId()) {
            $rulesId = $model->getRulesId();
        } else {
            $rulesId = $this->getRequest()->getParam('rulesid');
        }

        $rulesModel = Mage::getModel('prolabels/label');
        $rulesModel->load($rulesId);
        $rulesValues = $rulesModel->getData();

        $fieldset->addField('rules_id', 'hidden', array(
            'name'  => 'rules_id'
        ));

        $fieldset->addField('system_label_name', 'text', array(
            'name'      => 'system_label_name',
            'label'     => Mage::helper('prolabels')->__('Name'),
            'title'     => Mage::helper('prolabels')->__('Name'),
            'required'  => true,
        ));

        $fieldset->addField('stores', 'multiselect', array(
            'name'      => 'stores[]',
            'label'     => Mage::helper('prolabels')->__('Store View'),
            'title'     => Mage::helper('prolabels')->__('Store View'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
        ));

        if (Mage::getStoreConfig("prolabels/general/customer_group")) {
            $fieldset->addField('customer_group_ids', 'multiselect', array(
                'name'      => 'customer_group_ids[]',
                'label'     => Mage::helper('prolabels')->__('Customer Groups'),
                'title'     => Mage::helper('prolabels')->__('Customer Groups'),
                'required'  => true,
                'values'    => Mage::getResourceModel('customer/group_collection')->toOptionArray()
            ));
        }

        $fieldset->addField('system_label', 'select', array(
            'label'     => Mage::helper('prolabels')->__('System'),
            'title'     => Mage::helper('prolabels')->__('System'),
            'name'      => 'system_label',
            'disabled'  => true,
            'required'  => true,
            'options'   => array(
                '0' => Mage::helper('prolabels')->__('No'),
                '1' => Mage::helper('prolabels')->__('Yes')
            )
        ));

        $fieldset->addField('l_status', 'select', array(
            'label'     => Mage::helper('prolabels')->__('Status'),
            'title'     => Mage::helper('prolabels')->__('Status'),
            'name'      => 'l_status',
            'required'  => true,
            'options'   => array(
                '1' => Mage::helper('prolabels')->__('Enabled'),
                '0' => Mage::helper('prolabels')->__('Disabled')
            )
        ));
        if (Mage::getStoreConfig("prolabels/general/priority")) {
            $fieldset->addField('use_priority', 'select', array(
                'label'     => Mage::helper('prolabels')->__('Use Label Priority'),
                'title'     => Mage::helper('prolabels')->__('Use Label Priority'),
                'name'      => 'use_priority',
                'options'   => array(
                    '0' => Mage::helper('prolabels')->__('No'),
                    '1' => Mage::helper('prolabels')->__('Yes')
                )
            ));

            $fieldset->addField('priority', 'text', array(
                'name'      => 'priority',
                'label'     => Mage::helper('prolabels')->__('Priority'),
                'title'     => Mage::helper('prolabels')->__('Priority'),
                'required'  => false
            ));
        }

        if (!$model->getRulesId()) {
            $model->addData(array('rules_id' => $rulesId));
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getAdditionalElementTypes()
    {
        return array(
            'image' => Mage::getConfig()->getBlockClassName('prolabels/adminhtml_system_helper_image')
        );
    }

}
