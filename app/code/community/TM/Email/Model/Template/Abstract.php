<?php
if (Mage::helper('core')->isModuleOutputEnabled('Aschroder_SMTPPro')) {
    class TM_Email_Model_Template_Abstract extends Aschroder_SMTPPro_Model_Email_Template
    {
        protected function _beforeSend(&$transport, $mail)
        {
            if (!$transport instanceof Zend_Mail_Transport_Sendmail) {
                return;
            }
            $_helper = Mage::helper('smtppro');
            // If it's not enabled, just return the parent result.
            if (!$_helper->isEnabled()) {
                $_helper->log('SMTP Pro is not enabled, fall back to parent class');
                return;
            }

            //observer :: beforeSendTemplate
            $_helper->log($mail);
            $transport = $_helper->getTransport();
        }

    }
} else {
    class TM_Email_Model_Template_Abstract extends Mage_Core_Model_Email_Template 
    {
        protected function _beforeSend($transport, $mail)
        {
        }
    }
}
