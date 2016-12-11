<?php

class TM_Email_Block_Adminhtml_Gateway_Storage_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Init form
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('tm_email_gateway_storage_form');
        $this->setTitle(Mage::helper('tm_email')->__('Gateway Information'));
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

        if (Mage::registry('tm_email_gateway_storage_data') ) {
            $data = Mage::registry('tm_email_gateway_storage_data')->getData();
        }

        $fieldset = $form->addFieldset(
            'gateway_general_form',
            array('legend' => Mage::helper('tm_email')->__('Email Gateway Details'))
        );
        $fieldset->addField('id', 'hidden', array(
        //  'class'     => 'required-entry',
            'name'      => 'id'
        ));

        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('tm_email')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));

        $fieldset->addField('email', 'text', array(
            'label'     => Mage::helper('tm_email')->__('Email'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'email',
            'note'      => Mage::helper('tm_email')->__('For Helpmate users: PLEASE NOTICE THAT ALL EMAILS AT THIS ADDRESS WILL BE DELETED IN INITIAL E-MAIL PROCESSING. THIS EMAIL SHOULD NOT USED BY ANY OTHER EMAIL SOFTWARE'),
        ));

        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('tm_email')->__('Status'),
            'name'      => 'status',
            'required'  => true,
            'values'    => array(

                array(
                    'value'     => 0,
                    'label'     => Mage::helper('tm_email')->__('Disabled')
                ),
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('tm_email')->__('Enabled')
                )
            ),
        ));

        $fieldset->addField('type', 'select', array(
            'label'     => Mage::helper('tm_email')->__('Type'),
            'name'      => 'type',
            'required'  => true,
            'values'    => array(

                array(
                    'value'     => TM_Email_Model_Gateway_Storage::TYPE_POP3,
                    'label'     => Mage::helper('tm_email')->__('Pop3')
                ),
                array(
                    'value'     => TM_Email_Model_Gateway_Storage::TYPE_IMAP,
                    'label'     => Mage::helper('tm_email')->__('Imap')
                )
            ),
        ));

        $fieldset->addField('host', 'text', array(
            'label'     => Mage::helper('tm_email')->__('Host'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'host',
        ));

        $fieldset->addField('user', 'text', array(
            'label'     => Mage::helper('tm_email')->__('User'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'user',
        ));

        $fieldset->addField('password', 'password', array(
            'label'     => Mage::helper('tm_email')->__('Password'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'password',
        ));

        $fieldset->addField('port', 'text', array(
            'label'     => Mage::helper('tm_email')->__('Port'),
            'class'     => 'required-entry',
            'required'  => true,
            'note'      => Mage::helper('tm_email')->__('110 for POP3, 995 for POP3-SSL, 143 for IMAP-TLS and 993 for IMAP-SSL by default'),
            'name'      => 'port',
        ));

        $fieldset->addField('secure', 'select', array(
            'label'     => Mage::helper('tm_email')->__('Secure'),
            'name'      => 'secure',
            'required'  => true,
            'values'    => array(
                array(
                    'value'     => 0,
                    'label'     => Mage::helper('tm_email')->__('None'),
                ),
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('tm_email')->__('SSL'),
                ),
                array(
                    'value'     => 2,
                    'label'     => Mage::helper('tm_email')->__('TLS'),
                )
            ),
        ));
        /*
        $fieldset->addField('remove', 'select', array(
            'label'     => Mage::helper('tm_email')->__('Remove'),
            'name'      => 'remove',
            'required'  => true,
            'values'    => array(

                array(
                    'value'     => 0,
                    'label'     => Mage::helper('tm_email')->__('Disabled')
                ),
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('tm_email')->__('Enabled')
                )
            ),
        ));
        */
        $form->setValues($data);

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}