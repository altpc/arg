<?php
class TM_Email_Model_Gateway_Storage_Message_Parser
{
    /**
     *
     * @var Zend_Mail_Message
     */
    protected $_message;

    /**
     *
     * @var string
     */
    protected $_content   = '';

    /**
     *
     * @var string
     */
    protected $_fileNames = '';

    public function __construct(Zend_Mail_Message $message = null)
    {
        $this->_message = $message;
    }

    public function setMessage(Zend_Mail_Message $message)
    {
        $this->_message = $message;
        return $this;
    }

    public function getContent()
    {
        if (!empty($this->_content)) {
            return $this->_content;
        }
        $content = '';

        $message = $this->_message;
        if ($message->isMultipart()) {
            foreach (new RecursiveIteratorIterator($message) as $part) {
                /* @var $part Zend_Mail_Part */
                try {
                    $type = $part->getHeaderField('content-type');

                    switch ($type) {
                        case 'text/html': // priority secure reason
                            $content = $this->_getContentFromPart($part);
                            break;

                        case 'text/plain':
                            if (empty($content)) {
                                $content = $this->_getContentFromPart($part);
                            }
                            break;

                        default:
                        break;
                    }
                } catch (Zend_Mail_Exception $e) {
//                    echo "$e \n";
//                    die;
                }
            }
        // is not multipart
        } else {
            $content = $this->_getContentFromPart($message);
        }

        $this->_content = $content;
        return $this->_content;
    }

    protected function _getContentFromPart(Zend_Mail_Part_Interface $message)
    {
        $content = $message->getContent();

        $encoding = null;
        if ($message->headerExists('content-transfer-encoding')) {
            $encoding = $message->getHeader('content-transfer-encoding');
        }
        $content = $this->_decode($content, $encoding);

        $charset = $message->getHeaderField('content-type', 'charset');

        $content = $this->_toUtf8($content, $charset);

        return $content;
    }

    private function _toUtf8($content, $charset)
    {
        if (empty($charset)
            || 'UTF-8' == $charset
            || !in_array($charset, mb_list_encodings())) {

            return $content;
        }
        return mb_convert_encoding($content, "UTF-8", $charset);
    }

    private function _decode($content, $encoding)
    {
//           case '7bit':
//                break;
//            case '8bit':
//                $content = quoted_printable_decode(imap_8bit($content));
//                break;
//            case 'binary':
//                $content = imap_base64(imap_binary($content));
//                break;
//            case 'quoted-printable':
//                $content = quoted_printable_decode($content);
//                break;
//            case 'base64':
//                $content = imap_base64($content);
//                break;
        // Convert to UTF-8 if necessary
        switch ($encoding) {
            case 'quoted-printable':
                $content = quoted_printable_decode($content);
                break;
            case 'base64':
                $content = base64_decode($content);
                break;
            default:
                $content = $content;
                break;
        }

        return $content;
    }

    public function getFilenames()
    {
        if (!empty($this->_fileNames)) {
            return $this->_fileNames;
        }
        if (true != Mage::getStoreConfig('helpmate/general/enabledAttached')) {
            return '';
        }

        if (!$this->_message->isMultipart()) {
            return '';
        }

        $file = '';
        $attachedAllowedExtensions = explode(',', Mage::getStoreConfig('helpmate/general/attachedAllowedExtensions'));

        $path = Mage::getBaseDir('media') . DS . 'helpmate' . DS;

        foreach (new RecursiveIteratorIterator($this->_message) as $part) {
            /* @var $part Zend_Mail_Part */
            try {
                $type = $part->getHeaderField('content-type');

                if (in_array($type, array('text/plain', 'text/html'))) {
                    continue;
                }

                $encoding = null;
                if ($part->headerExists('content-transfer-encoding')) {
                    $encoding = $part->getHeader('content-transfer-encoding');
                }
                if ($part->headerExists('content-disposition')) {
//                                $name = $part->getHeaderField('content-disposition', 'filename');
                    $name = preg_replace(
                        '/^attachment; filename=(.+)$/',
                        "$1",
                        $part->getHeader('content-disposition')
                    );
//                    $name = $this->_toUtf8($content, $charset);
                    $name = iconv_mime_decode(
                        $name,
                        ICONV_MIME_DECODE_CONTINUE_ON_ERROR,
                        "UTF-8"
                    );
                    $name = trim($name, "\'\"");
                } else {
                    $name = rand(time());
                }
//                $name = trim($name, "\'\"");

                $content = $part->getContent();
                $content = $this->_decode($content, $encoding);

                $fileExtension = substr($name, strrpos($name, '.') + 1);
                $name =  date("Y-m-d") . '_' . $name;

		if (!in_array(strtolower($fileExtension), $attachedAllowedExtensions)) {
                    throw new Exception('Disallowed file type.');
		}
		file_put_contents($path . $name, $content);
                $file .= ';' . $name;
            } catch (Exception $e) {
//                echo "$e \n";
//                die;
            }
        }
        $this->_fileNames = trim($file, ';');
        return $this->_fileNames;
    }

    /**
     *
     * @return string
     */
    public function getMessageId()
    {
        $message = $this->_message;

        $value = null;
        if ($message->headerExists('Message-ID')) {
            $value = $message->getHeader('Message-ID');
        } elseif($message->headerExists('message-id')) {
            $value = $message->getHeader('message-id');
        }

//        $value = preg_replace('|<(.*?)>|', '$1', $value);
        if (preg_match('|<(.*?)>|', $value, $regs)) {
            $value = $regs[1];
        }
        return $value;
    }

    /**
     *
     * @return string
     */
    public function getInReplyTo()
    {
        $message = $this->_message;

        $value = null;
        if ($message->headerExists('In-Reply-To')) {
            $value = $message->getHeader('In-Reply-To');
        } elseif($message->headerExists('in-reply-to')) {
            $value = $message->getHeader('in-reply-to');
        }

        if (preg_match('|<(.*?)>|', $value, $regs)) {
            $value = $regs[1];
        }
        return $value;
    }

    /**
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return date('Y-m-d H:i:s', strtotime($this->_message->date));
    }

    /**
     *
     * @return string
     */
    public function getFrom()
    {
        $value = $this->_message->from;
        if (preg_match('|<(.*?)>|', $value, $regs)) {
            $value = $regs[1];
        }
        return $value;
    }

    /**
     *
     * @return string
     */
    public function getSubject()
    {
        return iconv_mime_decode(
            $this->_message->subject,
            ICONV_MIME_DECODE_CONTINUE_ON_ERROR,
            "UTF-8"
        );
    }

    /**
     *
     * @param string $subject [optional]
     * @return mixed int|void
     */
    public function getTicketIdFromSubject($subject = null)
    {
        if (null === $subject) {
            $subject = $this->getSubject();
        }
        /*
         * Helpmate (ticket# {{var vars.id}})  helpmate_notify_customer_ticket_create
         * : Re:(ticket# {{var vars.id}})      helpmate_send_ticket_answer
         */
        preg_match("/\s*\(ticket#\s*(?P<ticketId>\d+)\)/", $subject, $matches);

        return isset($matches['ticketId']) ?
           (int) $matches['ticketId'] - 1000000 : null;
    }

}
