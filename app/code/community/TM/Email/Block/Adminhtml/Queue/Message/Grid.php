<?php

class TM_Email_Block_Adminhtml_Queue_Message_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('tm_email_queue_message_grid');
        $this->setDefaultSort('message_id');
        $this->setDefaultDir('DESC');

        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('tm_email/queue_message')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('message_id', array(
          'header'    => Mage::helper('tm_email')->__('ID'),
          'align'     => 'right',
          'width'     => '50px',
          'index'     => 'message_id',
          'type'      => 'number'
        ));

        $queues = Mage::getModel('tm_email/queue_queue')->toOptionHash();
        $this->addColumn('queue_id', array(
            'header'    => Mage::helper('tm_email')->__('Queue'),
            'align'     => 'left',
            'index'     => 'queue_id',
            'type'      => 'options',
            'options'   => $queues,
            'frame_callback' => array($this, 'decorateQueue')
        ));

//        $this->addColumn('handle', array(
//          'header'    => Mage::helper('tm_email')->__('Handle'),
//          'align'     => 'left',
//          'index'     => 'handle',
//        ));

        $this->addColumn('subject', array(
            'header'    => Mage::helper('tm_email')->__('Subject'),
            'align'     => 'left',
            'index'     => 'body',
            'frame_callback' => array($this, 'decorateSubject')
        ));

        $this->addColumn('from', array(
            'header'    => Mage::helper('tm_email')->__('From'),
            'align'     => 'left',
            'index'     => 'body',
            'frame_callback' => array($this, 'decorateFrom')
        ));

        $this->addColumn('to', array(
            'header'    => Mage::helper('tm_email')->__('To'),
            'align'     => 'left',
            'index'     => 'body',
            'frame_callback' => array($this, 'decorateTo')
        ));

//        $this->addColumn('md5', array(
//          'header'    => Mage::helper('tm_email')->__('MD5'),
//          'align'     => 'left',
//          'index'     => 'md5',
//        ));
//
//        $this->addColumn('timeout', array(
//          'header'    => Mage::helper('tm_email')->__('Timeout'),
//          'align'     => 'left',
//          'index'     => 'timeout',
//          'type'      => 'number'
//        ));

        $this->addColumn('created', array(
          'header'    => Mage::helper('tm_email')->__('Created'),
          'align'     => 'left',
          'index'     => 'created',
          'type'      => 'datetime',
        ));

        $this->addColumn('status', array(
            'header'  => Mage::helper('tm_email')->__('Status'),
            'align'   => 'left',
            'width'   => '80px',
            'index'   => 'status',
            'type'    => 'options',
            'options' => Mage::getSingleton('tm_email/queue_message_status')->toOptionHash(),
            'frame_callback' => array($this, 'decorateStatus')
        ));

        $this->addColumn('actions', array(
            'header'   => Mage::helper('adminnotification')->__('Actions'),
            'width'    => '200px',
            'filter'   => false,
            'sortable' => false,
            'renderer' => 'tm_email/adminhtml_queue_message_grid_renderer_action'
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('tm_email')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('tm_email')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('tm_email');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('tm_email')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('tm_email')->__('Are you sure?')
        ));

        $_statuses = Mage::getSingleton('tm_email/queue_message_status')->toOptionHash();
        $statuses = array();
        foreach ($_statuses as $_value => $_label) {
            $statuses[] = array('label' => $_label, 'value' => $_value);
        }
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('tm_email')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name'   => 'status',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => Mage::helper('tm_email')->__('Status'),
                    'values' => $statuses
                )
            )
        ));




        return $this;
    }


    /**
     * Decorate status column values
     *
     * @return string
     */
    public function decorateQueue($value, $row, $column, $isExport)
    {
//        Zend_Debug::dump($row->getData());
        $links = array();
        $href = $this->getUrl('*/queue_queue/edit', array('id' => $row->getQueueId()));
        $links[] = sprintf('<a href="%s">%s</a>', $href, $value);
        return implode(' | ', $links);
    }

    /**
     * Decorate status column values
     *
     * @return string
     */
    public function decorateSubject($value, $row, $column, $isExport)
    {
        /* @var $mail Zend_Mail */
        $mail = $row->getMail();
        return Zend_Mime_Decode::decodeQuotedPrintable($mail->getSubject());
    }

    /**
     * Decorate status column values
     *
     * @return string
     */
    public function decorateFrom($value, $row, $column, $isExport)
    {
        /* @var $mail Zend_Mail */
        $mail = $row->getMail();

        return $mail->getFrom();

//        $headers = $mail->getHeaders();
//        $subject = Zend_Mime_Decode::decodeQuotedPrintable($mail->getSubject());
//        $to = Zend_Mime_Decode::decodeQuotedPrintable($headers["To"][0]);
//        $return = "From : {$mail->getFrom()}<br/>"
//            . "Subject : {$subject}<br/>"
//            . "To : {$to}<br/>";
//        return $return;// . substr($value, 0, 100);
    }

    /**
     * Decorate status column values
     *
     * @return string
     */
    public function decorateTo($value, $row, $column, $isExport)
    {
        /* @var $mail Zend_Mail */
        $mail = $row->getMail();

        $headers = $mail->getHeaders();
        $to = isset($headers["To"][0]) ? $headers["To"][0] : '';
        return Zend_Mime_Decode::decodeQuotedPrintable($to);
    }

    protected function _getCell($value, $class = 'critical')
    {
        return '<span class="grid-severity-' . $class . '"><span>'
            . $value
            . '</span></span>';
    }


    /**
    * Decorate status column values
    *
    * @return string
    */
    public function decorateStatus($value, $row, $column, $isExport)
    {
        switch ($row->status) {
            case TM_Email_Model_Queue_Message_Status::FAILURE:
                $class = 'critical';
            break;
            case TM_Email_Model_Queue_Message_Status::DISAPPROVED:
                $class = 'major';
            break;
            case TM_Email_Model_Queue_Message_Status::APPROVED:
                $class = 'minor';
            break;
            case TM_Email_Model_Queue_Message_Status::SUCCESS:
            default:
                $class = 'notice';
            break;
        }
        return $this->_getCell($value, $class);
    }

//    public function getRowUrl($row)
//    {
//        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
//    }
}