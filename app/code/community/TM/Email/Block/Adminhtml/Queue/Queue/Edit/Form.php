<?php

class TM_Email_Block_Adminhtml_Queue_Queue_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Init form
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('tm_email_gateway_form');
        $this->setTitle(Mage::helper('tm_email')->__('Queue Information'));
    }

    protected function _prepareForm()
    {
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $id)),
                'method' => 'post',
            )
        );

        if (Mage::registry('tm_email_queue_data') ) {
            $data = Mage::registry('tm_email_queue_data')->getData();
        }

        $fieldset = $form->addFieldset(
            'gateway_general_form',
            array('legend' => Mage::helper('tm_email')->__('Queue Details'))
        );
        $fieldset->addField('queue_id', 'hidden', array(
        //  'class'     => 'required-entry',
            'name'      => 'queue_id'
        ));

        $fieldset->addField('queue_name', 'text', array(
            'label'     => Mage::helper('tm_email')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'queue_name',
        ));


        $fieldset->addField('timeout', 'text', array(
            'label'     => Mage::helper('tm_email')->__('Timeout'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'timeout',
        ));

        $fieldset->addField('default_status', 'select', array(
            'label'    => Mage::helper('tm_email')->__('Default Status'),
            'title'    => Mage::helper('tm_email')->__('Default Status'),
            'name'     => 'default_status',
            'required' => true,
            'options'  => Mage::getSingleton('tm_email/queue_message_status')->toOptionHash(),
        ));

        $form->setValues($data);

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}