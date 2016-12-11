<?php
class TM_Email_Model_Queue_Message_Status
{
    const DISAPPROVED = 0;
    const APPROVED    = 1;
    const SUCCESS     = 2;
    const FAILURE     = 3;
    /**
    *
    * @return array
    */
    public function toOptionHash()
    {
        return array(
            self::DISAPPROVED => Mage::helper('tm_email')->__('Disapproved'),
            self::APPROVED    => Mage::helper('tm_email')->__('Approved'),
            self::SUCCESS     => Mage::helper('tm_email')->__('Success'),
            self::FAILURE     => Mage::helper('tm_email')->__('Failure'),
        );
    }

    public static function isStatus($status)
    {
        return in_array($status, array(
            self::DISAPPROVED, self::APPROVED,  self::SUCCESS, self::FAILURE
        ));
    }
}