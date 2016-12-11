<?php

class TM_Email_Model_System_Config_Source_Gateway_Transport_Provider
{

    static public function toOptionHash()
    {
        return array(
            // TM_Email_Model_Gateway_Transport::TYPE_SENDMAIL => 'Sendmail',
            TM_Email_Model_Gateway_Transport::TYPE_SMTP      => 'Smtp',
            TM_Email_Model_Gateway_Transport::TYPE_AOL       => 'AOL Mail',
            TM_Email_Model_Gateway_Transport::TYPE_COMCAST   => 'Comcast',
            TM_Email_Model_Gateway_Transport::TYPE_GMX       => 'GMX',
            TM_Email_Model_Gateway_Transport::TYPE_GMAIL     => 'Gmail',
            TM_Email_Model_Gateway_Transport::TYPE_HOTMAIL   => 'Hotmail',
            TM_Email_Model_Gateway_Transport::TYPE_MAILCOM   => 'Mail.com',
            TM_Email_Model_Gateway_Transport::TYPE_MAILGUN   => 'Mailgun',
            TM_Email_Model_Gateway_Transport::TYPE_O2        => 'O2 Mail',
            TM_Email_Model_Gateway_Transport::TYPE_OFFICE365 => 'Office365',
            TM_Email_Model_Gateway_Transport::TYPE_ORANGE    => 'Orange',
            TM_Email_Model_Gateway_Transport::TYPE_OUTLOOK   => 'Outlook',
            TM_Email_Model_Gateway_Transport::TYPE_YAHOO     => 'Yahoo!',
            TM_Email_Model_Gateway_Transport::TYPE_ZOHO      => 'Zoho',
            TM_Email_Model_Gateway_Transport::TYPE_MANDRILL  => 'Mandrill (smtp)',
            TM_Email_Model_Gateway_Transport::TYPE_AMAZONSES => 'Amazon SES',
            TM_Email_Model_Gateway_Transport::TYPE_SENDGRID  => 'SendGrid (smtp)',
        );
    }

    /**
     * Options getter
     *
     * @return array
     */
    static public function toOptionArray()
    {
        $return = array();
        $helper = Mage::helper('tm_email');
        foreach (self::toOptionHash() as $key => $value) {
            $return[] = array(
                'value' => $key,
                'label' => $helper->__($value)
            );
        }

        return $return;
    }
}
