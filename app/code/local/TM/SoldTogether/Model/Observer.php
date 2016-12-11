<?php

class TM_SoldTogether_Model_Observer
{

    /**
     * Prepare product data for saving
     *
     * @param Varien_Object $observer
     * @return TM_SoldTogether_Model_Observer
     */
    public function prepareProductSoldTogetherData($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $request = $observer->getEvent()->getRequest();

        if (null !== ($orderData = $request->getPost('soldtogether_order'))) {
            $product->setSoldtogetherOrderData(
                new Varien_Object(
                    Mage::helper('adminhtml/js')->decodeGridSerializedInput($orderData)
                )
            );
        }

        if (null !== ($customerData = $request->getPost('soldtogether_customer'))) {
            $product->setSoldtogetherCustomerData(
                new Varien_Object(
                    Mage::helper('adminhtml/js')->decodeGridSerializedInput($customerData)
                )
            );
        }
        $this->saveProductSoldTogetherData($observer);
        return $this;
    }

    /**
     * Save product relations after saving product
     *
     * @param Varien_Object $observer
     * @return TM_SoldTogether_Model_Observer
     */
    public function saveProductSoldTogetherData($observer)
    {
        $product = $observer->getEvent()->getProduct();

        $model = Mage::getResourceModel('soldtogether/order');

        if (null !== ($orderData = $product->getSoldtogetherOrderData())) {
            $orderData = $orderData->getData();
            if (!count($orderData)) {
                $model->deleteData(
                    "product_id = {$product->getId()}"
                );
            } else {
                $relatedIds = array_keys($orderData);
                $model->deleteData(
                    "product_id = {$product->getId()}
                    AND related_product_id NOT IN (" . implode($relatedIds, ',') . ")"
                );

                foreach ($orderData as $relatedProductId => $values) {
                    $model->saveData(array(
                        'product_id'            => $product->getId(),
                        'related_product_id'    => $relatedProductId,
                        'weight'                => $values['soldtogether_weight'],
                        'is_admin'              => 1,
                        'override_admin'        => 1
                    ));
                }
            }
        }

        $model2 = Mage::getResourceModel('soldtogether/customer');

        if (null !== ($customerData = $product->getSoldtogetherCustomerData())) {
            $customerData = $customerData->getData();
            if (!count($customerData)) {
                $model2->deleteData(
                    "product_id = {$product->getId()}"
                );
            } else {
                $relatedIds = array_keys($customerData);
                $model2->deleteData(
                    "product_id = {$product->getId()}
                    AND related_product_id NOT IN (" . implode($relatedIds, ',') . ")"
                );
                foreach ($customerData as $relatedProductId => $values) {
                    $model2->saveData(array(
                        'product_id'            => $product->getId(),
                        'related_product_id'    => $relatedProductId,
                        'weight'                => $values['soldtogether_weight'],
                        'is_admin'              => 1,
                        'override_admin'        => 1
                    ));
                }
            }
        }

        return $this;
    }

    public function indexNewOrdersSoldTogetherData($observer)
    {
        $data = Mage::getStoreConfig('soldtogether/general/enabled');
        if (!$data) {
            return $this;
        }
        if (!Mage::getStoreConfig('soldtogether/general/create_order')
            && !Mage::getStoreConfig('soldtogether/general/create_customer')) {
                return $this;
            }
        $orderId = $observer->getOrder()->getId();
        $customerId = null;
        $orderModel = Mage::getResourceModel('soldtogether/order');
        $customerModel = Mage::getResourceModel('soldtogether/customer');
        $res = $orderModel->getOrderObserverData($orderId);
        $result = array();
        for ($i=0;$i<count($res);$i++) {
            for ($j=0;$j<count($res);$j++) {
                if ($res[$i] == $res[$j]) {
                    continue;
                }
                if ($this->productsExist($res[$i], $res[$j])) {
                    $result['product_id'] = $res[$i];
                    $result['related_product_id'] = $res[$j];
                    if (Mage::getStoreConfig('soldtogether/general/create_order')) {
                        $orderModel->saveData($result);
                    }
                    if (Mage::getStoreConfig('soldtogether/general/create_customer')) {
                        $customerModel->saveData($result);
                    }
                }
                $result = array();
            }
        }
        return $this;
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

    public function autoReindexOrders($observer)
    {
        if (Mage::getStoreConfig('soldtogether/general/cron_order')) {
            $orderModel = Mage::getModel('soldtogether/order');
            $orderModel->reindexAll();
        }
        if (Mage::getStoreConfig('soldtogether/general/cron_customer')) {
            $customerModel = Mage::getModel('soldtogether/customer');
            $customerModel->reindexAll();
        }

        return $this;
    }

    public function appendMockupBlocks($observer)
    {
        $helper = Mage::helper('soldtogether');
        $blocks = $observer->getBlocks()->getValue();
        $blocks[] = array(
                'value' => 'soldtogether.customers.bought',
                'label' => $helper->__('Customers Also Buy')
            );
        $blocks[] = array(
                'value' => 'soldtogether.ordered.also',
                'label' => $helper->__('Bought Together')
            );
        $observer->getBlocks()->setValue($blocks);
    }

    /**
     * append Checkout Success config section with some additional options
     * from system.xml under `sections_depend_on` tag
     */
    public function appendCheckoutsuccessConfig($observer)
    {
        $depend = $observer->getConfig()->getNode('sections_depend_on');
        foreach ($depend->children() as $child) {
            $moduleName = $child->getName();
            $module = Mage::getConfig()->getNode('modules/' . $moduleName);
            if ($module && $module->descend('active') == 'true') {
                $currentVersion = $module->descend('version');
                $requiredVersion = $child->descend('version');
                if (version_compare($currentVersion, $requiredVersion) > -1) {
                    $observer->getConfig()->getNode('sections')->extend(
                            $child->descend('sections')
                        );
                }
            }
        }
    }

}
