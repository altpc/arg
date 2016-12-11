<?php
/**
 * Template model
 *
 * Example:
 *
 * // Loading of template
 * $emailTemplate  = Mage::getModel('core/email_template')
 *    ->load(Mage::getStoreConfig('path_to_email_template_id_config'));
 * $variables = array(
 *    'someObject' => Mage::getSingleton('some_model')
 *    'someString' => 'Some string value'
 * );
 * $emailTemplate->send('some@domain.com', 'Name Of User', $variables);
 *
 * @method Mage_Core_Model_Resource_Email_Template _getResource()
 * @method Mage_Core_Model_Resource_Email_Template getResource()
 * @method string getTemplateCode()
 * @method Mage_Core_Model_Email_Template setTemplateCode(string $value)
 * @method string getTemplateText()
 * @method Mage_Core_Model_Email_Template setTemplateText(string $value)
 * @method string getTemplateStyles()
 * @method Mage_Core_Model_Email_Template setTemplateStyles(string $value)
 * @method int getTemplateType()
 * @method Mage_Core_Model_Email_Template setTemplateType(int $value)
 * @method string getTemplateSubject()
 * @method Mage_Core_Model_Email_Template setTemplateSubject(string $value)
 * @method string getTemplateSenderName()
 * @method Mage_Core_Model_Email_Template setTemplateSenderName(string $value)
 * @method string getTemplateSenderEmail()
 * @method Mage_Core_Model_Email_Template setTemplateSenderEmail(string $value)
 * @method string getAddedAt()
 * @method Mage_Core_Model_Email_Template setAddedAt(string $value)
 * @method string getModifiedAt()
 * @method Mage_Core_Model_Email_Template setModifiedAt(string $value)
 * @method string getOrigTemplateCode()
 * @method Mage_Core_Model_Email_Template setOrigTemplateCode(string $value)
 * @method string getOrigTemplateVariables()
 * @method Mage_Core_Model_Email_Template setOrigTemplateVariables(string $value)
 *
 */
class TM_Email_Model_Template extends TM_Email_Model_Template_Abstract
{
    const CONFIG_DEFAULT_TRANSPORT = 'tm_email/default/transport';

    /**
     *
     * @var string
     */
    protected $_queueName = null;

    /**
     *
     * @var Zend_Mail_Transport_Abstract
     */
    protected $_transport = null;

    /**
     *
     * @var int
     */
    protected $_storeId = null;

    /**
     *
     * @param int $storeId
     * @return \TM_Email_Model_Template
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     *
     * @param string $name
     * @return \TM_Email_Model_Template
     */
    public function setQueueName($name)
    {
        $this->_queueName = $name;
        return $this;
    }

//    /**
//     *
//     * @param string $name
//     * @return Zend_Queue
//     */
//    protected function _getQueue()
//    {
//        $options = array(
//            Zend_Queue::NAME => $this->_queueName
//        );
//
//        $queue = new Zend_Queue(null, $options);
//        $adapter = new TM_Email_Model_Queue_Adapter_Db($options);
//        $queue->setAdapter($adapter);
//
//        return $queue;
//    }

    /**
     *
     * @param Zend_Mail_Transport_Abstract $transport
     * @return \TM_Email_Model_Template
     */
    public function setTransport(Zend_Mail_Transport_Abstract $transport)
    {
        $this->_transport = $transport;
        return $this;
    }

    /**
     *
     * @return \Zend_Mail_Transport_Abstract
     */
    public function getTransport()
    {
        if (!$this->_transport instanceof Zend_Mail_Transport_Abstract) {

            $config = self::CONFIG_DEFAULT_TRANSPORT;
            $transportId = (int) Mage::getStoreConfig($config, $this->_storeId);
            $modelTransport = Mage::getModel('tm_email/gateway_transport');
            $this->_transport = $modelTransport
                ->setSenderEmail($this->getSenderEmail())
                ->getTransport($transportId)
            ;
            Zend_Mail::setDefaultTransport($this->_transport);
        }

        return $this->_transport;
    }

    protected function _getReturnPathEmail()
    {
        $setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH, $this->_storeId);
        switch ($setReturnPath) {
            case 1:
                $returnPathEmail = $this->getSenderEmail();
                break;
            case 2:
                $returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL, $this->_storeId);
                break;
            default:
                $returnPathEmail = null;
                break;
        }

        return $returnPathEmail;
    }

    /**
     * Send mail to recipient
     *
     * @param   array|string       $email        E-mail(s)
     * @param   array|string|null  $name         receiver name(s)
     * @param   array              $variables    template variables
     * @return  boolean
     **/
    public function send($email, $name = null, array $variables = array())
    {
        if (!$this->isValidForSend()) {
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
        }

        $emails = array_values((array)$email);
        $names = is_array($name) ? $name : (array)$name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);

        $this->setUseAbsoluteLinks(true);
        $variables['boundary'] = $boundary = strtoupper(uniqid('--boundary_'));//'--BOUNDARY_TEXT_OF_CHOICE';
        $text = $this->getProcessedTemplate($variables, true);
        $subject = $this->getProcessedTemplateSubject($variables);

        $returnPathEmail = $this->_getReturnPathEmail();

        // comment magento core queue logic (AR-18)
        // if ($this->hasQueue() && $this->getQueue() instanceof Mage_Core_Model_Email_Queue) {
        //     /** @var $emailQueue Mage_Core_Model_Email_Queue */
        //     $emailQueue = $this->getQueue();
        //     $emailQueue->setMessageBody($text);
        //     $emailQueue->setMessageParameters(array(
        //             'subject'           => $subject,
        //             'return_path_email' => $returnPathEmail,
        //             'is_plain'          => $this->isPlain(),
        //             'from_email'        => $this->getSenderEmail(),
        //             'from_name'         => $this->getSenderName(),
        //             'reply_to'          => $this->getMail()->getReplyTo(),
        //             'return_to'         => $this->getMail()->getReturnPath(),
        //         ))
        //         ->addRecipients($emails, $names, Mage_Core_Model_Email_Queue::EMAIL_TYPE_TO)
        //         ->addRecipients($this->_bccEmails, array(), Mage_Core_Model_Email_Queue::EMAIL_TYPE_BCC);
        //     $emailQueue->addMessageToQueue();

        //     return true;
        // }

        $mail = $this->getMail();

        foreach ($emails as $key => $email) {
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
        }

        // echo($text);
        // die;
        if($this->isPlain()) {
            $mail->setBodyText($text);
        } elseif(strpos($text, $boundary)) {
            $_text = substr($text, 0, strpos($text, $boundary));
            $_html = str_replace($boundary, '', substr($text, strpos($text, $boundary)));

            $mail->setBodyText($_text);
            $mail->setBodyHTML($_html);
        } else {
            $mail->setBodyHTML($text);
        }

        $mail->setSubject('=?utf-8?B?' . base64_encode($subject) . '?=');
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

        try {
            $transport = $this->getTransport();

            if (method_exists($this, '_beforeSend')) {
                $this->_beforeSend($transport, $mail);
            }
            if (empty($this->_queueName)) {
                $mail->send($transport);
            } else {
                Mage::getModel('tm_email/queue')
                    ->setName($this->_queueName)
                    ->send($mail, $transport)
                ;
            }
            $this->_mail = null;
        }
        catch (Exception $e) {
            $this->_mail = null;
            Mage::logException($e);
            return false;
        }

        return true;
    }
}
