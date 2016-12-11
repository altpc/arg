<?php

class TM_Email_Block_Adminhtml_System_Email_Template_Preview extends Mage_Adminhtml_Block_Widget
{
    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var $template Mage_Core_Model_Email_Template */
//        $template = Mage::getModel('core/email_template');
        $id = (int)$this->getRequest()->getParam('id');
        $message = Mage::getModel('tm_email/queue_message')->load($id);

        if (!$message) {
            return;
        }
        //Zend_Debug::dump($message->getData());

        $mail = $message->getMail();
        if (!$mail) {
            return;
        }
//        Zend_Debug::dump($mail->getHeaderEncoding());
//        new Zend_Mime_Part();

        $part = $mail->getBodyHtml();
        if ($part == false || !$part instanceof Zend_Mime_Part) {
            $part = $mail->getBodyText();
        }
        $content = $part->getContent();
        if ('quoted-printable' == $mail->getHeaderEncoding()) {
            $content = quoted_printable_decode($content);
        }
        $toolbar = "<div class='filter-actions form-buttons' style='float:right;clear:both'>";
        $status = (int)$message->getStatus();
        if (TM_Email_Model_Queue_Message_Status::APPROVED !== $status) {
            $toolbar .= $this->getButtonHtml(
                Mage::helper('tm_email')->__('Approve'),
                "Windows.close('grid-action-changes', event);" .
                "setLocation('" . $this->getUrl('*/*/status', array(
                    'message_id' => $id,
                    'status' => TM_Email_Model_Queue_Message_Status::APPROVED,

                )) . "')",
                'save'
            );
        }
        if (TM_Email_Model_Queue_Message_Status::DISAPPROVED !== $status) {
            $toolbar .= $this->getButtonHtml(
                Mage::helper('tm_email')->__('Disapprove'),
                "Windows.close('grid-action-changes', event);" .
                "setLocation('" . $this->getUrl('*/*/status', array(
                    'message_id' => $id,
                    'status' => TM_Email_Model_Queue_Message_Status::DISAPPROVED,

                )) . "')",
                'delete'
            );

        }
        $toolbar .= '</div>';

        return $toolbar . $content;
    }
}
