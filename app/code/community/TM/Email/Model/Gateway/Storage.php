<?php
class TM_Email_Model_Gateway_Storage extends Mage_Core_Model_Abstract
{
    const TYPE_POP3   = 1;
    const TYPE_IMAP   = 2;

    const SECURE_NONE = 0;//false;
    const SECURE_SSL  = 1;//'SSL';
    const SECURE_TLS  = 2;//'TLS';

    public function _construct()
    {
        parent::_construct();
        $this->_init('tm_email/gateway_storage');
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
     * @return Zend_Mail_Storage_Abstract
     */

    public function getStorage()
    {
        $type = $this->getType();

        if (self::TYPE_POP3 == $type) {
            $class = 'Zend_Mail_Storage_Pop3';
        } elseif (self::TYPE_IMAP == $type) {
            $class = 'Zend_Mail_Storage_Imap';
        } else {
           throw new Exception(
               Mage::helper('tm_email')->__('Protocol type incorrect'
           ));
        }
        $config = array(
            'host'     => $this->getHost(),
            'user'     => $this->getUser(),
            'password' => $this->getPassword(),
            'port'     => (int) $this->getPort(),
            'ssl'      => $this->getSsl()

        );

        return new $class($config);
    }
}