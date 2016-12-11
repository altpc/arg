<?php
class TM_Email_Model_Observer
{
    /**
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return TM_Email_Model_Observer
     */
    public function scheduledSend($schedule)
    {
        /* @var $message Zend_Queue_Message */
        $_queues = Mage::getModel('tm_email/queue')->getQueue()->getQueues();

        foreach ($_queues as $_queue) {
            Mage::getModel('tm_email/queue')
                ->setName($_queue)
                ->receive();
        }
        return $this;
    }
}
