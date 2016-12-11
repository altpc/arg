<?php
class TM_ReviewReminder_Model_Entity extends Mage_Core_Model_Abstract
{
    // Record statuses
    const STATUS_NEW = 1;
    const STATUS_SENT = 2;
    const STATUS_CANCELLED = 3;
    const STATUS_FAILED = 4;
    const STATUS_PENDING = 5;
    // Review statuses
    const NOT_REVIEWED = 1;
    const REVIEWED = 2;
    const NO_CUSTOMER = 3;

    public function __construct()
    {
        $this->_init('tm_reviewreminder/entity');
        parent::_construct();
    }
    /**
     * Prepare reminder's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        $statuses = new Varien_Object(array(
            self::STATUS_NEW => Mage::helper('tm_reviewreminder')->__('New'),
            self::STATUS_SENT => Mage::helper('tm_reviewreminder')->__('Sent'),
            self::STATUS_CANCELLED => Mage::helper('tm_reviewreminder')->__('Cancelled'),
            self::STATUS_FAILED => Mage::helper('tm_reviewreminder')->__('Failed'),
            self::STATUS_PENDING => Mage::helper('tm_reviewreminder')->__('Pending')
        ));

        return $statuses->getData();
    }
    /**
     * Prepare review's statuses.
     *
     * @return array
     */
    public function getReviewStatuses()
    {
        $statuses = new Varien_Object(array(
            self::NOT_REVIEWED => Mage::helper('tm_reviewreminder')->__('Not reviewed'),
            self::REVIEWED => Mage::helper('tm_reviewreminder')->__('Reviewed'),
            self::NO_CUSTOMER => Mage::helper('tm_reviewreminder')->__('Customer not found')
        ));

        return $statuses->getData();
    }

    public function getOrderInfo()
    {
        return $this->getResource()->getOrderInfo($this->getOrderId());
    }
}