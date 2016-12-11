<?php

class TM_SoldTogether_Model_Mysql4_Customer extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('soldtogether/customer', 'relation_id');
    }

    public function getItemsToProcess($count = 1, $step = 0)
    {
        $connection = $this->_getReadAdapter();

        $customerSelect = $connection->select()
            ->from(array('so' => $this->getTable('sales/order')),'customer_email')
            ->order('entity_id')
            ->limit($count, $count * $step);

        $customerMail = $connection->fetchCol($customerSelect);
        $customerMail = array_filter($customerMail);
        $select = $this->_getReadAdapter()->select()
            ->from(array('soi' => $this->getTable('sales/order_item')),
                array('product_id', 'parent_item_id'))
            ->join(array('so' => $this->getTable('sales/order')),
                'so.entity_id = soi.order_id',
                'customer_email')
            ->joinInner(array('cp' => $this->getTable('catalog/product')),
                'cp.entity_id = soi.product_id',
                array())
            ->where('so.customer_email IN (?)', $customerMail)
            ->order(array('order_id', 'product_id'));

        $result = array_fill_keys($customerMail, array());
        foreach ($this->_getReadAdapter()->fetchAll($select) as $row) {
            if (!isset($result[$row['customer_email']][$row['parent_item_id']])) {
                $result[$row['customer_email']][$row['parent_item_id']] = array();
            }
            $result[$row['customer_email']][$row['parent_item_id']][] = $row['product_id'];
        }

        return $result;
    }

    public function deleteData($where)
    {
        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
    }

    public function saveData($data)
    {
        $connection = $this->_getReadAdapter();

        $select = $connection->select()
            ->from(array('so' => $this->getMainTable()))
            ->where('product_id = ?', $data['product_id'])
            ->where('related_product_id = ?', $data['related_product_id']);

        if (!($row = $connection->fetchRow($select))) {
            $row['weight'] = 0;
        } elseif ($row['is_admin']
            && (!isset($data['override_admin']) || !$data['override_admin'])) {

            return;
        } else {
            $data['relation_id'] = $row['relation_id'];
            if (isset($data['weight']) && ($row['weight'] != $data['weight'])) {
                $data['is_admin'] = 1;
            } else {
                $data['is_admin'] = $row['is_admin'];
            }
        }

        if (!isset($data['weight']) || !$data['weight']) {
            $data['weight'] = $row['weight'] + 1;
        }

        $customerModel = Mage::getModel('soldtogether/customer');
        $customerModel->addData($data);

        $this->save($customerModel);
    }

    public function clearTable()
    {
        $this->_getWriteAdapter()->delete($this->getMainTable(), 'is_admin = 0');
        $this->_getWriteAdapter()->commit();
        $this->autoincremetCustomerTable($this->getMaxAdminId());
    }

    public function autoincremetCustomerTable($maxId)
    {
        if ($maxId == null) {
            $id = 1;
        } else {
            $id = $maxId + 1;
        }

        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        $write->query("ALTER TABLE " . $this->getTable('soldtogether/customer') . " AUTO_INCREMENT = " . $id);
        $write->commit();

        return $this;
    }

    public function getMaxAdminId()
    {
        $connection = $this->_getReadAdapter();

        $select = $connection->select()
            ->from(array('so' => $this->getMainTable()),
                array('MAX(relation_id)'))
            ->where('is_admin = ?', 1);

        $result = $connection->fetchRow($select);

        return $result['MAX(relation_id)'];
    }

    public function getRelated($productId)
    {
        $connection = $this->_getReadAdapter();

        $select = $connection->select()
            ->from(array('so' => $this->getMainTable()),
                array('related_product_id', 'weight'))
            ->where('product_id = ?', $productId);

        return $connection->fetchAll($select);
    }

    public function addOrderProductData($customer)
    {
        $email = $customer->getEmail();
        $res = $this->getOrderObserverData($email);

        $result = array();
        for ($i=0;$i<count($res);$i++) {
            for ($j=0;$j<count($res);$j++) {
                if ($res[$i] == $res[$j]) {
                    continue;
                }
                if ($this->productsExist($res[$i], $res[$j])) {
                    $result['product_id'] = $res[$i];
                    $result['related_product_id'] = $res[$j];
                    $this->saveData($result);
                }
                $result = array();
            }
        }
    }

    public function productsExist($product1, $product2)
    {
        $model1 = Mage::getModel('catalog/product');
        $model2 = Mage::getModel('catalog/product');
        $model1->load($product1);
        $model2->load($product2);
        if ($model1->getId() && $model2->getId()) {
            return true;
        }
        return false;
    }

    public function getOrderObserverData($email)
    {
        $connection = $this->_getReadAdapter();
        $emails = array();
        $emails[] = $email;

        $select = $this->_getReadAdapter()->select()
            ->from(array('soi' => $this->getTable('sales/order_item')),
                array('product_id', 'parent_item_id'))
            ->join(array('so' => $this->getTable('sales/order')),
                'so.entity_id = soi.order_id',
                'customer_email')
            ->joinInner(array('cp' => $this->getTable('catalog/product')),
                'cp.entity_id = soi.product_id',
                array())
            ->where('so.customer_email IN (?)', $emails)
            ->order(array('order_id', 'product_id'));

        $result = array();

        foreach ($this->_getReadAdapter()->fetchAll($select) as $row) {
            if (!isset($row['parent_item_id'])) {
                $result[] = $row['product_id'];
            }
        }
        return $result;
    }
}
