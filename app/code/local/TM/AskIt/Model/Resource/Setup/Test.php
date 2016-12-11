<?php

class TM_AskIt_Model_Resource_Setup_Test extends Mage_Core_Model_Resource_Setup
{
    protected $prefix = 'xx_rm_';

    protected $tables = array(
        'tm_askit_item',
        'tm_askit_vote',
        'tm_askit_message'
    );
    // protected $resourceName = 'askit_setup';

    public function setTablePrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function getTable($tableName)
    {
        if (in_array($tableName, $this->tables)) {
            return $this->prefix . parent::getTable($tableName);
        }
        return parent::getTable($tableName);
    }

    public function setDbVersion($version)
    {
        $this->_getResource()->setDbVersion($this->_resourceName, $version);
        return $this;
    }

    protected function _setResourceVersion($actionType, $version)
    {
        // switch ($actionType) {
        //     case self::TYPE_DB_INSTALL:
        //     case self::TYPE_DB_UPGRADE:
        //         $this->_getResource()->setDbVersion($this->_resourceName, $version);
        //         break;
        //     case self::TYPE_DATA_INSTALL:
        //     case self::TYPE_DATA_UPGRADE:
        //         $this->_getResource()->setDataVersion($this->_resourceName, $version);
        //         break;

        // }

        return $this;
    }


    public function install22()
    {
        $newVersion = '2.2.0';

        $oldVersion = $this->_modifyResourceDb(self::TYPE_DB_INSTALL, '', $newVersion);
        $this->_modifyResourceDb(self::TYPE_DB_UPGRADE, $oldVersion, $newVersion);
        return $this;
    }

    public function install23()
    {
        $newVersion = '2.3.0';
        $oldVersion = $this->_modifyResourceDb(self::TYPE_DB_INSTALL, '', $newVersion);
        $this->_modifyResourceDb(self::TYPE_DB_UPGRADE, $oldVersion, $newVersion);
        return $this;
    }

    public function upgrade222223()
    {
        $oldVersion = '2.2.0';
        $newVersion = '2.2.3';

        $this->_modifyResourceDb(self::TYPE_DB_UPGRADE, $oldVersion, $newVersion);
        return $this;
    }

    public function upgrade223224()
    {
        $oldVersion = '2.2.3';
        $newVersion = '2.2.4';

        $this->_modifyResourceDb(self::TYPE_DB_UPGRADE, $oldVersion, $newVersion);
        return $this;
    }

    public function upgrade224225()
    {
        $oldVersion = '2.2.4';
        $newVersion = '2.2.5';

        $this->_modifyResourceDb(self::TYPE_DB_UPGRADE, $oldVersion, $newVersion);
        return $this;
    }

    public function dropTempTables()
    {
        $prefix = $this->prefix;
        // $installer = new Mage_Core_Model_Resource_Setup();
        $installer = $this;
        $connection = $installer->getConnection();
        $installer->startSetup();
        $tables = array(
            'tm_askit_item',
            'tm_askit_vote',
            'tm_askit_message'
        );
        foreach ($tables as $table) {
            $table = $this->getTable($table);
            $connection->dropTable($table);
        }
        $installer->endSetup();
        return $this;
    }

    public function fill22()
    {
        $prefix = $this->prefix;
        // $installer = new Mage_Core_Model_Resource_Setup();
        $installer = $this;
        $connection = $installer->getConnection();

        // $installer->startSetup();

        $defaultQuestionStatus = Mage::getStoreConfig('askit/general/defaultQuestionStatus');//pending

        $customer = Mage::getModel('customer/customer')->getCollection()
            ->getLastItem()
        ;

        $itemTableName = $prefix . 'tm_askit_item';
        $voteTableName = $prefix . 'tm_askit_vote';
        // $messageTableName = $installer->getTable('tm_askit_message');
        // $voteTableName = $installer->getTable('tm_askit_vote');
        // $itemTableName = $installer->getTable('tm_askit_item');

        for ($i = 1; $i < 5; $i++) {
            $question = new Varien_Object();
            $text = file_get_contents("http://loripsum.net/api/1/short/plaintext");
            $storeId = Mage::app()->getStore()->getId();
            $question
                ->setText($text)
                ->setStoreId($storeId)
                ->setHint(0)
                ->setParentId(null)
                ->setCustomerId((int)$customer->getId())
                ->setCustomerName($customer->getName())
                ->setEmail($customer->getEmail())
                ->setCreatedTime(now())
                ->setUpdateTime(now())
                ->setStatus($defaultQuestionStatus)
                ->setPrivate(false)
                ->setItemId(480)
                ->setItemTypeId(TM_AskIt_Model_Item_Type::PRODUCT_ID)
            ;
            $connection->insert($itemTableName, $question->getData());
            $questionId = $connection->lastInsertId();

            $vote = new Varien_Object();
            $vote->setItemId((int)$questionId)
                ->setCustomerId((int)$customer->getId())
            ;
            $connection->insert($voteTableName, $vote->getData());

            for ($j = 1; $j < 3; $j++) {
                $answer = new Varien_Object();
                $defaultAnswerStatus = Mage::getStoreConfig('askit/general/defaultAnswerStatus');//pending
                $text = file_get_contents("http://loripsum.net/api/1/short/plaintext");
                $answer
                    ->setText($text)
                    ->setStoreId((int)$storeId)
                    ->setCustomerId((int)$customer->getId())
                    ->setHint(0)
                    ->setParentId($questionId)
                    ->setCustomerName($customer->getName())
                    ->setEmail($customer->getEmail())
                    ->setCreatedTime(now())
                    ->setUpdateTime(now())
                    ->setStatus($defaultAnswerStatus)
                ;
                $connection->insert($itemTableName, $answer->getData());
                $answerId = $connection->lastInsertId();

                $vote = new Varien_Object();
                $vote->setItemId($answerId)
                    ->setCustomerId((int)$customer->getId())
                ;
                $connection->insert($voteTableName, $vote->getData());
            }
        }
        // $installer->endSetup();
    }

    public function regenerateAll()
    {
        $collection = Mage::getModel('askit/message')->getCollection();

        foreach ($collection as $message) {
            $content = file_get_contents("http://loripsum.net/api/1/short/plaintext");
            $message->setText($content)->save();
        }
        return $this;
    }
}
