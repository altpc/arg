<?php

class TM_Email_Block_Adminhtml_Gateway_Transport_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Init form
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('tm_email_gateway_transport_form');
        $this->setTitle(Mage::helper('tm_email')->__('Mail Transport'));
    }

    protected function _prepareForm()
    {
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $id)),
            'method' => 'post',
        ));

        $data = array();
        if (Mage::registry('tm_email_gateway_transport_data')) {
            $data = Mage::registry('tm_email_gateway_transport_data')->getData();
        }

        $isNew = !isset($data['id']);

        $fieldset = $form->addFieldset(
            'gateway_general_form',
            array('legend' => Mage::helper('tm_email')->__('Email Transport Details'))
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

        $el = $fieldset->addField('type', 'select', array(
            'label'     => Mage::helper('tm_email')->__('Type'),
            'name'      => 'type',
            'required'  => true,
            'values'    => TM_Email_Model_System_Config_Source_Gateway_Transport_Provider::toOptionArray(),
            'onchange'  => $isNew ? 'fillDefaultProviderData(this)' : 'return false'
        ));

        if ($isNew) {
            $conf = array(
                TM_Email_Model_Gateway_Transport::TYPE_AOL => array(
                    'host'   => 'smtp.aol.com',
                    'port'   => 587,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_NONE,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
                    'user'    =>  '',
                    'password' => '',
                ),
                TM_Email_Model_Gateway_Transport::TYPE_COMCAST => array(
                    'host'   => 'smtp.comcast.net',
                    'port'   => 587,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_NONE,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
                    'user'    =>  '',
                    'password' => '',
                ),
                TM_Email_Model_Gateway_Transport::TYPE_GMX => array(
                    'host'   => 'mail.gmx.net',
                    'port'   => 587,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_TLS,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
                    'user'    =>  '',
                    'password' => '',
                ),
                TM_Email_Model_Gateway_Transport::TYPE_GMAIL => array(
                    'host'   => 'smtp.gmail.com',
                    'port'   => 465,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_SSL,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
                    'user'    =>  'Gmail username',
                    'password' => 'Gmail password',
                ),
                TM_Email_Model_Gateway_Transport::TYPE_HOTMAIL => array(
                    'host'   => 'smtp.live.com',
                    'port'   => 465,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_SSL,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
                    'user'    =>  '',
                    'password' => '',
                ),
                TM_Email_Model_Gateway_Transport::TYPE_MAILCOM => array(
                    'host'   => 'smtp.mail.com',
                    'port'   => 465,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_SSL,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
                    'user'    =>  '',
                    'password' => '',
                ),
                TM_Email_Model_Gateway_Transport::TYPE_O2 => array(
                    'host'   => 'smtp.o2.ie',
                    'port'   => 25,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_NONE,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
                    'user'    =>  '',
                    'password' => '',
                ),
                TM_Email_Model_Gateway_Transport::TYPE_OFFICE365 => array(
                    'host'   => 'smtp.office365.com',
                    'port'   => 587,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_TLS,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
                    'user'    =>  '',
                    'password' => '',
                ),
                TM_Email_Model_Gateway_Transport::TYPE_ORANGE => array(
                    'host'   => 'smtp.orange.net',
                    'port'   => 25,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_NONE,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
                    'user'    =>  '',
                    'password' => '',
                ),
                TM_Email_Model_Gateway_Transport::TYPE_OUTLOOK => array(
                    'host'   => 'smtp-mail.outlook.com',
                    'port'   => 587,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_TLS,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
                    'user'    =>  '',
                    'password' => '',
                ),
                TM_Email_Model_Gateway_Transport::TYPE_YAHOO => array(
                    'host'   => 'smtp.mail.yahoo.com',
                    'port'   => 465,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_SSL,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
                    'user'    =>  '',
                    'password' => '',
                ),
                TM_Email_Model_Gateway_Transport::TYPE_ZOHO => array(
                    'host'   => 'smtp.zoho.com',
                    'port'   => 465,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_SSL,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN
                ),
                //https://mandrill.zendesk.com/hc/en-us/articles/205582147-How-to-Send-with-PHPMailer
                //https://mandrill.zendesk.com/hc/en-us/articles/205582137-How-to-Send-via-SMTP-with-Popular-Programming-Languages
                TM_Email_Model_Gateway_Transport::TYPE_MANDRILL => array(
                    'host'   => 'smtp.mandrillapp.com',
                    'port'   => 587,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_TLS,
                    // 'secure' => TM_Email_Model_Gateway_Transport::SECURE_SSL,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
                    'user'    =>  'MANDRILL_USERNAME',
                    'password' => 'MANDRILL_APIKEY',
                ),
                TM_Email_Model_Gateway_Transport::TYPE_AMAZONSES => array(
                    'host'   => 'email.us-east-1.amazonaws.com',
                    'port'   => 587,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_TLS,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
                    'user'    =>  'YOUR_AWS_ACCESS_KEY',
                    'password' => 'YOUR_AWS_PRIVATE_KEY',
                ),
                //https://sendgrid.com/docs/Integrate/Frameworks/zend.html
                TM_Email_Model_Gateway_Transport::TYPE_SENDGRID => array(
                    'host'   => 'smtp.sendgrid.net',
                    'port'   => 587,
                    'secure' => TM_Email_Model_Gateway_Transport::SECURE_TLS,
                    'auth'   => TM_Email_Model_Gateway_Transport::AUTH_LOGIN,
                    'user'    =>  'SendGrid Username',
                    'password' => 'SendGrid Password',
                ),
            );
            $el->setAfterElementHtml(
                "<script type=\"text/javascript\">
                    function fillDefaultProviderData(element)
                    {
                        var type = element.value, dataset = " . json_encode($conf) . ";
                        if (dataset[type]) {
                            \$H(dataset[type]).each(function(pair){
                                $(pair.key).value = pair.value;
                            });
                        }
                    }
                </script>
            ");
        }


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
            'note'      => Mage::helper('tm_email')->__('25 and 587 (465) for SMTP by default'),
            'name'      => 'port',
        ));

        $fieldset->addField('secure', 'select', array(
            'label'     => Mage::helper('tm_email')->__('Secure'),
            'name'      => 'secure',
            'required'  => true,
            'values'    => TM_Email_Model_System_Config_Source_Gateway_Transport_Secure::toOptionArray()
        ));

        $fieldset->addField('auth', 'select', array(
            'label'     => Mage::helper('tm_email')->__('Authorize Type'),
            'name'      => 'auth',
            'required'  => true,
            'values'    => TM_Email_Model_System_Config_Source_Gateway_Transport_Auth::toOptionArray()
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
