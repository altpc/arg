<?php
class TM_Email_Model_Queue_Message extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('tm_email/queue_message');
    }

    /**
     *
     * @return Zend_Mail
     */
    public function getMail()
    {
        list($mail, $transport) = unserialize($this->body);

        return $mail;
    }

    /**
     *
     * @return Zend_Mail_Transport_Abstract
     */
    public function getTransport()
    {
        list($mail, $transport) = unserialize($this->body);

        return $transport;
    }
}