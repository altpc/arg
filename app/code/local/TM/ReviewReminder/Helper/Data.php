<?php
class TM_ReviewReminder_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Id of indexing process to lock
     */
    const PROCESS_ID = 'tm_reviewreminder_email_send';
    /**
     * @var Mage_Index_Model_Process $indexProcess
     */
    private $indexProcess;
    /**
     * @var Boolean manual email sending flag
     */
    private $isManualSend;
    /**
     * Path to store config if frontend output is enabled
     *
     * @var string
     */
    const XML_PATH_ENABLED = 'tm_reviewreminder/general/enabled';
    /**
     * Path to store config number of emails per cron
     *
     * @var string
     */
    const XML_EMAILS_NUM_PER_CRON = 'tm_reviewreminder/general/emails_per_cron';
    /**
     * Path to store config reminder default status
     *
     * @var string
     */
    const XML_PATH_DEFAULT_STATUS = 'tm_reviewreminder/general/default_status';
    /**
     * Path to store config process orders
     *
     * @var string
     */
    const XML_PATH_ALLOW_SPECIFIC = 'tm_reviewreminder/email/allow_specific_order';
    /**
     * Path to store config consider orders statuses
     *
     * @var string
     */
    const XML_PATH_SPECIFIC_ORDER_STATUSES = 'tm_reviewreminder/email/specific_order';
    /**
     * Path to store config email subject
     *
     * @var string
     */
    const XML_EMAIL_SUBJECT = 'tm_reviewreminder/email/email_subject';
    /**
     * Path to store config email template
     *
     * @var string
     */
    const XML_EMAIL_TEMPLATE = 'tm_reviewreminder/email/email_template';
    /**
     * Path to store config email send from contact
     *
     * @var string
     */
    const XML_EMAIL_SEND_FROM = 'tm_reviewreminder/email/send_from';
    /**
     * Path to store config send email after days
     *
     * @var string
     */
    const XML_SEND_EMAIL_AFTER = 'tm_reviewreminder/email/send_after';
    /**
     * Checks whether product videos can be displayed in the frontend
     *
     * @param integer|string|Mage_Core_Model_Store $store
     * @return boolean
     */
    public function isEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED, $store);
    }
    /**
     * Process only orders with selected statuses enabled
     *
     * @param integer|string|Mage_Core_Model_Store $store
     * @return boolean
     */
    public function allowSpecificStatuses($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ALLOW_SPECIFIC, $store);
    }
    /**
     * Get selected orders statuses array
     *
     * @param integer|string|Mage_Core_Model_Store $store
     * @return Array
     */
    public function specificOrderStatuses($store = null)
    {
        return explode(',', Mage::getStoreConfig(self::XML_PATH_SPECIFIC_ORDER_STATUSES, $store));
    }
    /**
    * Return email subject
    *
    * @param integer|string|Mage_Core_Model_Store $store
    * @return String
    */
    public function getEmailSubject($store = null)
    {
        return Mage::getStoreConfig(self::XML_EMAIL_SUBJECT, $store);
    }
    /**
    * Return email template
    *
    * @param integer|string|Mage_Core_Model_Store $store
    * @return String
    */
    public function getEmailTemplate($store = null)
    {
        return Mage::getStoreConfig(self::XML_EMAIL_TEMPLATE, $store);
    }
    /**
    * Return email send from contact
    *
    * @param integer|string|Mage_Core_Model_Store $store
    * @return String
    */
    public function getEmailSendFrom($store = null)
    {
        return Mage::getStoreConfig(self::XML_EMAIL_SEND_FROM, $store);
    }
    /**
     * Return send email after days value
     *
     * @param integer|string|Mage_Core_Model_Store $store
     * @return int
     */
    public function getSendEmailAfter($store = null)
    {
        return abs((int)Mage::getStoreConfig(self::XML_SEND_EMAIL_AFTER, $store));
    }
    /**
     * Return send emails per cron iteration number
     *
     * @param integer|string|Mage_Core_Model_Store $store
     * @return int
     */
    public function getNumOfEmailsPerCron($store = null)
    {
        $numToSend = abs((int)Mage::getStoreConfig(self::XML_EMAILS_NUM_PER_CRON, $store));
        return $numToSend ? $numToSend : 10;
    }
    /**
     * Return reminder's default status
     *
     * @param integer|string|Mage_Core_Model_Store $store
     * @return int
     */
    public function getDefaultStatus($store = null)
    {
        return abs((int)Mage::getStoreConfig(self::XML_PATH_DEFAULT_STATUS, $store));
    }
    /**
     * Get order created date or status change date depending from configuration
     * @param  Mage_Sales_Order $order order instance
     * @return date order date
     */
    public function getOrderDate($order)
    {
        $storeId = $order->getStoreId();
        $orderHistoryCollection = Mage::getResourceModel('sales/order_status_history_collection')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSort('created_at', 'ASC')
            ->addAttributeToFilter('parent_id', array('eq' => $order->getId()));

        if (Mage::helper('tm_reviewreminder')->allowSpecificStatuses($storeId)) {
            $orderHistoryCollection->addAttributeToFilter('status',
                array('in' => Mage::helper('tm_reviewreminder')->specificOrderStatuses($storeId)))
            ->load();
            $orderDate = $orderHistoryCollection->getLastItem()->getCreatedAt();
        } else {
            $orderDate = $order->getCreatedAt();
        }

        return $orderDate;
    }
    /**
     * Send review reminders
     * @param Array $reminderIds array of ids when sent manually or null for cron
     */
    public function sendReminders($reminderIds)
    {
        $this->indexProcess = new Mage_Index_Model_Process();
        $this->indexProcess->setId(self::PROCESS_ID);

        if ($this->indexProcess->isLocked()) {
            return $this;
        }
        $this->indexProcess->lockAndBlock();

        $this->isManualSend = ($reminderIds != null);
        $reminderModel = Mage::getModel('tm_reviewreminder/entity');
        $entityCollection = $reminderModel->getCollection();
        if ($this->isManualSend) {
            $entityCollection->addFieldToFilter('entity_id', array('in' => $reminderIds));
        } else {
            $entityCollection->addFieldToFilter('status', array('eq' => TM_ReviewReminder_Model_Entity::STATUS_NEW));
        }

        // check if enough days passed to send reminder
        if (!$this->isManualSend) {
            $daysAfter = Mage::helper('tm_reviewreminder')->getSendEmailAfter();
            if (is_int($daysAfter) && $daysAfter > 0) {
                $checkDate = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time() - $daysAfter * 24 * 60 * 60));
                $entityCollection->addFieldToFilter('order_date', array('lteq' => $checkDate));
            }
        }

        $entityCollection->getSelect()
            ->reset('columns')
            ->columns(array(
                'entity_ids' => 'GROUP_CONCAT(entity_id SEPARATOR ",")',
                'order_ids' => 'GROUP_CONCAT(order_id SEPARATOR ",")',
                'customer_email'
            ))
            ->group('customer_email');

        if (!$this->isManualSend) {
            $entityCollection->getSelect()->limit($this->getNumOfEmailsPerCron());
        }

        foreach ($entityCollection as $entity) {
            try {
                $this->processOrders($entity->getCustomerEmail(), $entity->getOrderIds(), $entity->getEntityIds());
                $this->changeOrdersStatus($reminderModel, $entity->getEntityIds(), TM_ReviewReminder_Model_Entity::STATUS_SENT);
            } catch (Exception $e) {
                Mage::log($e->getMessage());
                $this->changeOrdersStatus($reminderModel, $entity->getEntityIds(), TM_ReviewReminder_Model_Entity::STATUS_FAILED);
                throw new Exception($e->getMessage());
            }
        }

        $this->indexProcess->unlock();
    }
    private function changeOrdersStatus($model, $entityIds, $status)
    {
        if (strpos($entityIds, ',') === false) {
            $this->saveEntityStatus($model, $entityIds, $status);
        } else {
            $entityIdsArr = explode(',', $entityIds);
            foreach ($entityIdsArr as $entityId) {
                $this->saveEntityStatus($model, $entityId, $status);
            }
        }
    }
    /**
     * Save record status
     */
    private function saveEntityStatus($model, $entityId, $status)
    {
        $model->load($entityId)->setStatus($status);
        try {
            $model->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
    /**
     * Go through orders and send emails
     * @param  String $customerEmail
     * @param  String $orderIds
     */
    private function processOrders($customerEmail, $orderIds, $entityIds)
    {
        $orderDataArr = array();
        if (strpos($orderIds, ',') === false) {
            $this->collectOrderData($orderIds, $orderDataArr);
        } else {
            $orderIdsArr = explode(',', $orderIds);
            foreach ($orderIdsArr as $orderId) {
                $this->collectOrderData($orderId, $orderDataArr);
            }
        }
        // remove duplicated products
        $orderDataArr = array_map("unserialize", array_unique(array_map("serialize", $orderDataArr)));
        $this->sendEmail($customerEmail, $orderDataArr);
    }
    /**
     * Collect order data by order id
     * @param  int $orderId
     * @param  array &$orderDataArr Reference to data array
     */
    public function collectOrderData($orderId, &$orderDataArr)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        $orderDataArr['customer_name'] = $order->getCustomerName();
        $orderDataArr['store_id'] = $order->getStoreId();
        $orderedItems = $order->getAllVisibleItems();
        $orderedProductIds = array();

        foreach ($orderedItems as $item) {
            array_push($orderedProductIds, $item->getData('product_id'));
        }

        $productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addIdFilter($orderedProductIds)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('product_url')
            ->addAttributeToSelect('image')
            ->load();

        //emulate frontend to get correct product image
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation
            ->startEnvironmentEmulation($orderDataArr['store_id']);

        foreach($productCollection as $product) {
            array_push($orderDataArr, array(
                'id' => $product->getId(),
                'url' => $product->getProductUrl(),
                'name' => $product->getName(),
                'image' => (string)Mage::helper('catalog/image')->init($product, 'image')->resize(100))
            );
        }
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
    }
    /**
     * Get reminder email subject and fill variables considering storeID from order
     * @return String email subject
     */
    public function filterEmailSubject($customerName, $productName, $storeId)
    {
        $subject = Mage::helper('tm_reviewreminder')->getEmailSubject($storeId);
        $subject = str_replace("{customer_name}", $customerName, $subject);
        $subject = str_replace("{product_name}", $productName, $subject);
        return $subject;
    }
    /**
     * Get list of products links for email
     * @return String
     */
    public function getProductsList($data)
    {
        $products = '';
        foreach ($data as $product) {
            if ($product['url'] && $product['name']) {
                $products .= "<a href='" . $product['url'] . "'>" . $product['name'] . "</a>, ";
            }
        }
        return $products;
    }
    /**
     * Send email to customer
     * @param  String $customerEmail
     * @param  Array $emailData Reminder email data
     */
    private function sendEmail($customerEmail, $emailData)
    {
        $storeId = isset($emailData['store_id']) ?
            $emailData['store_id'] : Mage::app()->getStore()->getId();

        if (!$this->isManualSend && !Mage::helper('tm_reviewreminder')->isEnabled($storeId)) {
            return $this;
        }

        // Start emulation and init translator to show correct texts
        // when {{block}} directive used in email template
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
        Mage::app()->getTranslator()->init('frontend', true);

        $customerName = $emailData['customer_name'];
        $productName = $emailData[0]['name'];
        unset($emailData['customer_name']);
        unset($emailData['store_id']);
        $productsList = $this->getProductsList($emailData);
        $subject = $this->filterEmailSubject($customerName, $productName, $storeId);
        $templateId = Mage::helper('tm_reviewreminder')->getEmailTemplate($storeId);
        $senderId = Mage::helper('tm_reviewreminder')->getEmailSendFrom($storeId);

        $vars = array(
            'subject' => $subject,
            'products' => $emailData,
            'customer_name' => $customerName,
            'products_list' => $productsList
        );

        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $mailTemplate = Mage::getModel('core/email_template');
        $mailTemplate->setTemplateSubject($subject)
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional(
                $templateId,
                $senderId,
                $customerEmail,
                $customerName,
                $vars
        );
        $translate->setTranslateInline(true);
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
    }
    /**
     * Generate random string for reminder hash
     *
     * @return string
     */
    public function generateHash()
    {
        $chars = Mage_Core_Helper_Data::CHARS_LOWERS
            . Mage_Core_Helper_Data::CHARS_UPPERS
            . Mage_Core_Helper_Data::CHARS_DIGITS;
        return Mage::helper('core')->getRandomString(16, $chars);
    }
}
