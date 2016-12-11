<?php

class TM_AskIt_Model_Observer
{
    const TRANSPORT_CONFIG       = 'askit/email/transport';
    const QUEUE_CONFIG           = 'askit/email/queue';
    const CUSTOMER_NOTIFY_STATUS = 'askit/email/enableCustomerNotification';
    const ADMIN_NOTIFY_STATUS    = 'askit/email/enableAdminNotification';

    /**
     *
     * @return Mage_Core_Model_Email_Template
     */
    protected function _getEmailTemplate($storeId = null)
    {
        $emailTemplate = Mage::getModel('core/email_template');
        /* @var $emailTemplate Mage_Core_Model_Email_Template */

        $emailTemplate->setDesignConfig(
            array('area' => 'frontend', 'store' => $storeId)
        );

        if (!$emailTemplate instanceof TM_Email_Model_Template) {
            return $emailTemplate;
        }

        $config = self::TRANSPORT_CONFIG;
        $transportId = (int) Mage::getStoreConfig($config);
        $transport = Mage::getModel('tm_email/gateway_transport')
            ->getTransport($transportId)
        ;
        if ($transport instanceof Zend_Mail_Transport_Abstract) {
            $emailTemplate->setTransport($transport);
        }

        $queueId = Mage::getStoreConfig(self::QUEUE_CONFIG);
        if (!empty($queueId)) {
            $_queue = Mage::getModel('tm_email/queue_queue')
                ->load($queueId)
            ;

            if ($_queue) {
                $emailTemplate->setQueueName($_queue->getQueueName());
            }
        }

        return $emailTemplate;
    }


    public function sendAdminNotification(Varien_Event_Observer $observer)
    {
        $message = $observer->getEvent()->getDataObject();
        $storeId = $message->getStoreId();
        if (!Mage::getStoreConfig(self::ADMIN_NOTIFY_STATUS, $storeId)) {
            return $this;
        }
//        $parentId = $message->getParentId();
//        $_answer = $message->getData('new_answer_text');
//        if (!empty($parentId) || $ques    tion->getText() != $_answer) {
//            return $this;
//        }
        $data = new Varien_Object();
        $messageHref = Mage::getSingleton('adminhtml/url')->getUrl(
            'adminhtml/askIt_index/edit',
            array('id' => $message->getId())
        );

        if (null == $message->getParentId()) {
            $subject = 'New %ss question was posted : %s';
            $question = Mage::getModel('askit/message')->load($message->getId());
        } else {
            $subject = '%ss question was updated : %s';
            $question = Mage::getModel('askit/message')->load($message->getParentId());
        }
        $item = Mage::helper('askit')->getItem($question);

        $subject = Mage::helper('askit')->__(
            $subject,
            $item->getPrefix(),
            $item->getName()
        );

        $data
            ->setSubject($subject)
            ->setQuestionhref($messageHref)
            ->setItemhref($item->getBackendItemUrl())
            ->setCustomerName($message->getCustomerName())
            ->setEmail($message->getEmail())
            ->setQuestion($message->getText())
            ->setItemname($item->getName())
            ->setStoreId($storeId)
        ;
        $adminEmail = Mage::getStoreConfig('askit/email/admin_email', $storeId);
        if (empty($adminEmail)) {
            return;
//            throw new Mage_Exception(
//                '\'Send admin notification to\' store config must be not empty'
//            );
        }

        $mailTemplate = $this->_getEmailTemplate($storeId);
        /* @var $mailTemplate Mage_Core_Model_Email_Template */

        $mailTemplate
            ->setReplyTo($data->getEmail())
            ->sendTransactional(
                Mage::getStoreConfig('askit/email/admin_template', $storeId),
                Mage::getStoreConfig('askit/email/sender', $storeId),
                $adminEmail,
                null,
                array('data' => $data)
            );
    }

    protected function _getPrecessedText($text, $storeId, $variables = array())
    {
        // $storeId = $this->getTicket()->getStoreId();

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation(
            $storeId,
            Mage_Core_Model_App_Area::AREA_FRONTEND
        );

        // $text = $this->getText();
        // cms filter
        $processor = Mage::helper('cms')->getBlockTemplateProcessor();
        $text = $processor->filter($text);

        // email filter
        $emailProcessor = Mage::getModel('core/email_template_filter')
            ->setUseAbsoluteLinks(true)
            ->setStoreId($storeId)
        ;
        if (!empty($variables)) {
            $emailProcessor
                ->setVariables($variables);
        }
        $text = $emailProcessor->filter($text);

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        return $text;
    }

    public function sendCustomerNotification(Varien_Event_Observer $observer)
    {
        $answer = $observer->getEvent()->getDataObject();

        $parentId = $answer->getParentId();
        if (empty($parentId)) {
            return $this;
        }
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        if (!$adminUser) {
            return $this;
        }
        $data = new Varien_Object();
        $question = Mage::getModel('askit/message')->load($answer->getParentId());
        $storeId = $question->getStoreId();
        if (!Mage::getStoreConfig(self::CUSTOMER_NOTIFY_STATUS, $storeId)) {
            return $this;
        }

        $item = Mage::helper('askit')->getItem($question);
        $answerText = $this->_getPrecessedText($answer->getText(), $storeId);
        $data->setName($question->getCustomerName())
//            ->setItemName($item->getName())
            ->setItemname($item->getName())
//            ->setItemUrl($item->getFrontendItemUrl())
            ->setItemurl($item->getFrontendItemUrl())
            ->setQuestion($question->getText())
            ->setAnswer($answerText)
            ->setAdminUserEmail($adminUser->getEmail())
            ->setCustomerEmail($question->getEmail())
            ->setStoreId($storeId);

        $mailTemplate = $this->_getEmailTemplate($storeId);

        $mailTemplate
            ->setReplyTo($data->getAdminUserEmail())
            ->sendTransactional(
                Mage::getStoreConfig('askit/email/customer_template', $storeId),
                Mage::getStoreConfig('askit/email/sender', $storeId),
                $data->getCustomerEmail(),
                //Mage::getStoreConfig('askit/email/admin_email'),
                null,
                array('data' => $data)
            );
//        $mailTemplate->getSentSuccess();
    }

    /**
     *
     * @param Varien_Event_Observer $observer
     * @param string $formId
     * @return \TM_AskIt_Model_Observer
     */
    protected function _checkCaptcha($observer, $formId)
    {
        $helperClass = Mage::getConfig()->getHelperClassName('captcha');
        if (@!class_exists($helperClass)) {
            return $this;
        }
        $captchaModel = Mage::helper('captcha')->getCaptcha($formId);
        if ($captchaModel->isRequired()) {
            $controller = $observer->getControllerAction();
            $captchaParams = $controller->getRequest()->getPost(
                Mage_Captcha_Helper_Data::INPUT_NAME_FIELD_VALUE
            );
            if (!$captchaModel->isCorrect($captchaParams[$formId])) {
                Mage::getSingleton('core/session')->addError(
                    Mage::helper('captcha')->__('Incorrect CAPTCHA.')
                );
                $controller->setFlag(
                    '',
                    Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH,
                    true
                );

                $refererUrl = Mage::helper('core/http')->getHttpReferer() ?
                    Mage::helper('core/http')->getHttpReferer() : Mage::getUrl('*/*/index');

                $controller->getResponse()->setRedirect($refererUrl);
            }
        }
        return $this;
    }

    /**
     * Check Captcha On Question Save
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Captcha_Model_Observer
     */
    public function checkCaptchaOnQuestionSave($observer)
    {
        $formId = 'askit_new_question_form';
        return $this->_checkCaptcha($observer, $formId);
    }

    /**
     * Check Captcha On Answer Save
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Captcha_Model_Observer
     */
    public function checkCaptchaOnAnswerSave($observer)
    {
        $formId = 'askit_new_answer_form';
        return $this->_checkCaptcha($observer, $formId);
    }
}
