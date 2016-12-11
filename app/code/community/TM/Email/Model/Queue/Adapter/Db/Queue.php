<?php
class TM_Email_Model_Queue_Adapter_Db_Queue extends Zend_Queue_Adapter_Db_Queue
{
    protected $_name = 'tm_email_queue_queue';
}