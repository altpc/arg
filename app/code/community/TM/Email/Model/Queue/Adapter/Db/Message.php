<?php
class TM_Email_Model_Queue_Adapter_Db_Message extends Zend_Queue_Adapter_Db_Message
{
    protected $_name = 'tm_email_queue_message';
}