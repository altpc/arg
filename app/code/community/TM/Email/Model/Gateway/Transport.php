<?php
class TM_Email_Model_Gateway_Transport extends Mage_Core_Model_Abstract
{
    const TYPE_SENDMAIL  = 0;
    const TYPE_SMTP      = 1;

    const TYPE_AOL       = 2;
    const TYPE_COMCAST   = 3;
    const TYPE_GMX       = 4;
    const TYPE_GMAIL     = 5;
    const TYPE_HOTMAIL   = 6;
    const TYPE_MAILCOM   = 7;
    const TYPE_MAILGUN   = 8;
    const TYPE_O2        = 9;
    const TYPE_OFFICE365 = 10;
    const TYPE_ORANGE    = 11;
    const TYPE_OUTLOOK   = 12;
    const TYPE_YAHOO     = 13;
    const TYPE_ZOHO      = 14;

    const TYPE_MANDRILL  = 15;
    const TYPE_AMAZONSES = 16;
    const TYPE_SENDGRID  = 17;

    const SECURE_NONE = 0;//false;
    const SECURE_SSL  = 1;//'SSL';
    const SECURE_TLS  = 2;//'TLS';

    const AUTH_LOGIN   = 'login';
    const AUTH_PLAIN   = 'plain';
    const AUTH_CRAMMD5 = 'crammd5';

    public function _construct()
    {
        parent::_construct();
        $this->_init('tm_email/gateway_transport');
    }

    public function toOptionArray()
    {
        return $this->_getResource()->toOptionArray();
    }

    public function toOptionHash()
    {
        return $this->_getResource()->toOptionHash();
    }

    public function getSsl()
    {
        $secure = $this->getSecure();
        if (self::SECURE_SSL == $secure) {
            return 'SSL';
        } elseif (self::SECURE_TLS == $secure) {
            return 'TLS';
        }
        return false;
    }

    /**
     *
     * @param int $id
     * @return \Zend_Mail_Transport_Abstract
     */
    public function getTransport($id = null)
    {
        $senderEmail = $this->getSenderEmail();
        if (null !== $id) {
            $this->load($id);
        }
        $type = $this->getType();
        switch ($type) {
            case self::TYPE_SMTP:
            case self::TYPE_AOL:
            case self::TYPE_COMCAST:
            case self::TYPE_GMX:
            case self::TYPE_GMAIL:
            case self::TYPE_HOTMAIL:
            case self::TYPE_MAILCOM:
            case self::TYPE_MAILGUN:
            case self::TYPE_O2:
            case self::TYPE_OFFICE365:
            case self::TYPE_ORANGE:
            case self::TYPE_OUTLOOK:
            case self::TYPE_YAHOO:
            case self::TYPE_ZOHO:
            case self::TYPE_MANDRILL:
            case self::TYPE_SENDGRID:
                $config = array();

                $port = $this->getPort();
                if (!empty($port)) {
                    $config['port'] = $port;
                }
                if ($this->getSsl()) {
                    $config['ssl'] = $this->getSsl();
                }
                $user = $this->getUser();
                $password = $this->getPassword();
                if ($user && $password) {
        //            $config['auth'] = 'login';
                    $config['username'] = $user;
                    $config['password'] = $password;
                }
                $config['auth'] = $this->getAuth();
                $transport = new Zend_Mail_Transport_Smtp($this->getHost(), $config);

                break;
            case self::TYPE_AMAZONSES:
                $user = $this->getUser();
                $password = $this->getPassword();

                $transport = new TM_Email_Model_Mail_Transport_AmazonSES(
                    array('accessKey' => $user, 'privateKey' => $password)
                );

                break;
            case self::TYPE_SENDMAIL:
            default:
                ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
                ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

                $setReturnPath = Mage::getStoreConfig(
                    Mage_Core_Model_Email_Template::XML_PATH_SENDING_SET_RETURN_PATH
                );
                switch ($setReturnPath) {
                    case 1:
                        $returnPathEmail = $senderEmail;
                        break;
                    case 2:
                        $returnPathEmail = Mage::getStoreConfig(
                            Mage_Core_Model_Email_Template::XML_PATH_SENDING_RETURN_PATH_EMAIL
                        );
                        break;
                    default:
                        $returnPathEmail = null;
                        break;
                }

                if ($returnPathEmail !== null) {
                    $transport = new Zend_Mail_Transport_Sendmail("-f".$returnPathEmail);
    //                Zend_Mail::setDefaultTransport($mailTransport);
                } else {
                    $transport = new Zend_Mail_Transport_Sendmail();
                }

                break;
        }
        return $transport;
    }
}
