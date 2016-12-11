<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Model_Mysql4_Label extends Mage_CatalogRule_Model_Mysql4_Rule
{
    protected $_productIds = null;

    protected $_productCollection = null;

    protected function _construct()
    {
        $this -> _init('prolabels/label', 'rules_id');
    }

    public function load(Mage_Core_Model_Abstract $object, $value, $field=null)
    {
        if (!intval($value) && is_string($value)) {
            $field = 'identifier'; // You probably don't have an identifier...
        }
        return parent::load($object, $value, $field);
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('prolabels/store'))
            ->where('rule_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row['store_id'];
            }
            $object->setData('store_id', $storesArray);
        }

        return parent::_afterLoad($object);
    }

    public function getProductLabelsData($productId, $mode) {
        $rulesIds = $this -> getProductRuleIds($productId);
        for($i=1; $i<=3; $i++) {
            $rulesIds[] = $i;
        }
        $result = $this -> _getReadAdapter() -> fetchAll(
            $this -> _getReadAdapter() -> select()
            -> from($this -> getTable('label')) -> where('rules_id in (?)', $rulesIds));
        return $result;
    }

    public function getCatalogLabels($productIds, $mode)
    {
        $rulesIds = $this->getCatalogsRuleIds($productIds);
        $catalogRuleIds = array();
        $catalogProductRules = array();
        foreach($rulesIds as $rule) {
            $catalogRuleIds[] = $rule['rules_id'];
            $catalogProductRules[$rule['product_id']][] = $rule['rules_id'];
        }
        $uniqCatalogRule = array_unique($catalogRuleIds);

        $manualLabelData = $this -> _getReadAdapter() -> fetchAll(
            $this->_getReadAdapter()->select()
                ->from($this->getTable('label'))
                ->where('rules_id in (?)', $uniqCatalogRule)
                ->where('label_status = ?', 1)
                ->where('rules_id > ?', 3)
            );
        $manualStoreData = $this -> _getReadAdapter() -> fetchAll(
            $this->_getReadAdapter()->select()
                ->from($this->getTable('store'))
                ->where('rule_id in (?)', $uniqCatalogRule)
            );
        $manualStores = array();
        foreach($manualStoreData as $store) {
            $manualStores[$store['rule_id']][] = $store['store_id'];
        }

        $systemLabelData = $this -> _getReadAdapter() -> fetchAll(
            $this->_getReadAdapter()->select()
                ->from($this->getTable('system'))
                ->where('l_status = ?', 1)
            );
        $systemStoreData = $this -> _getReadAdapter() -> fetchAll(
            $this->_getReadAdapter()->select()
                ->from($this->getTable('sysstore'))
            );
        $systemStores = array();
        foreach($systemStoreData as $sysStore) {
            $systemStores[$sysStore['system_id']][] = $sysStore['store_id'];
        }

        $labelsCatalogData = array();
        $labelsCatalogData['product_rule_ids'] = $catalogProductRules;
        $labelsCatalogData['catalog_rules'] = $manualLabelData;
        $labelsCatalogData['catalog_rules_store'] = $manualStores;
        $labelsCatalogData['catalog_system'] = $systemLabelData;
        $labelsCatalogData['catalog_system_store'] = $systemStores;

        return $labelsCatalogData;
    }

    public function getCatalogsRuleIds($productIds)
    {
        return $this->_getReadAdapter()->fetchAll(
                    $this->_getReadAdapter()->select()
                        ->from($this -> getTable('index'), array('rules_id', 'product_id'))
                        ->where('product_id in (?)', $productIds));
    }

    // public function getContentLabelsData($productId, $mode) {
    //     $rulesIds = $this->getProductRuleIds($productId);

    //     $result = $this->_getReadAdapter()->fetchAll(
    //         $this->_getReadAdapter()->select()
    //             ->from($this->getTable('label'))
    //             ->where('rules_id in (?)', $rulesIds)
    //             ->where('product_position=?','content')
    //     );

    //     return $result;
    // }

    public function loadLabelProductImage($object)
    {
        $value = $object->getProductImage();

        $path = Mage::getBaseDir('media') . DS . 'prolabel' . DS;

        if (is_array($value) && !empty($value['delete'])) {
            @unlink($path . $value['value']);
            $object -> setProductImage('');
            return $this;
        }

        if (empty($_FILES['product_image']['name'])) {
            if (is_array($value)) {
                $object -> setProductImage($value['value']);
            }
            return $this;
        }
        try {
            $uploader = new Varien_File_Uploader('product_image');
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
            $uploader->setAllowRenameFiles(true);
            $uploader->save($path);
            $object-> setProductImage($uploader -> getUploadedFileName());
        } catch (Exception $e) {
            $object -> unsProductImage();
            throw $e;
            //return $this;
        }
        return $this;
    }

    public function loadLabelCategoryImage($object) {
        $value = $object -> getCategoryImage();

        $path = Mage::getBaseDir('media') . DS . 'prolabel' . DS;

        if (is_array($value) && !empty($value['delete'])) {
            @unlink($path . $value['value']);
            $object -> setCategoryImage('');
            return $this;
        }

        if (empty($_FILES['category_image']['name'])) {
            if (is_array($value)) {
                $object -> setCategoryImage($value['value']);
            }
            return $this;
        }

        try {
            $uploader = new Varien_File_Uploader('category_image');
            $uploader -> setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
            $uploader -> setAllowRenameFiles(true);
            $uploader -> save($path);
            $object -> setCategoryImage($uploader -> getUploadedFileName());
        } catch (Exception $e) {
            $object -> unsCategoryImage();
            throw $e;
            //return $this;
        }
        return $this;
    }

    public function loadLabelCategoryOutImage($object) {
        $value = $object -> getCategoryOutStockImage();

        $path = Mage::getBaseDir('media') . DS . 'prolabel' . DS;

        if (is_array($value) && !empty($value['delete'])) {
            @unlink($path . $value['value']);
            $object -> setCategoryOutStockImage('');
            return $this;
        }

        if (empty($_FILES['category_out_stock_image']['name'])) {
            if (is_array($value)) {
                $object -> setCategoryOutStockImage($value['value']);
            }
            return $this;
        }

        try {
            $uploader = new Varien_File_Uploader('category_out_stock_image');
            $uploader -> setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
            $uploader -> setAllowRenameFiles(true);
            $uploader -> save($path);
            $object -> setCategoryOutStockImage($uploader -> getUploadedFileName());
        } catch (Exception $e) {
            $object -> unsCategoryOutStockImage();
            throw $e;
            //return $this;
        }
        return $this;
    }

    public function loadLabelProductOutImage($object) {
        $value = $object -> getProductOutStockImage();

        $path = Mage::getBaseDir('media') . DS . 'prolabel' . DS;

        if (is_array($value) && !empty($value['delete'])) {
            @unlink($path . $value['value']);
            $object -> setProductOutStockImage('');
            return $this;
        }

        if (empty($_FILES['product_out_stock_image']['name'])) {
            if (is_array($value)) {
                $object -> setProductOutStockImage($value['value']);
            }
            return $this;
        }

        try {
            $uploader = new Varien_File_Uploader('product_out_stock_image');
            $uploader -> setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
            $uploader -> setAllowRenameFiles(true);
            $uploader -> save($path);
            $object -> setProductOutStockImage($uploader -> getUploadedFileName());
        } catch (Exception $e) {
            $object -> unsProductOutStockImage();
            throw $e;
            //return $this;
        }
        return $this;
    }

    public function addLabelStoreIds(TM_ProLabels_Model_Label $object) {
        $deleteWhere = $this -> _getWriteAdapter() -> quoteInto('rule_id = ?', $object -> getId());

        /* store_view */
        $this -> _getWriteAdapter() -> delete($this -> getTable('prolabels/store'), $deleteWhere);
        if (null === $object->getData('store_ids')) {
            $stores = array();
        } else {
            $stores = $object->getData('store_ids');
        }
        foreach ($stores as $storeId) {
            $data = array('rule_id' => $object -> getId(), 'store_id' => $storeId);
            $this -> _getWriteAdapter() -> insert($this -> getTable('prolabels/store'), $data);
        }
        return $this;
    }

    public function loadStoreIds(TM_ProLabels_Model_Label $object) {
        $rulesId = $object -> getId();
        $storeIds = array();
        if ($rulesId) {
            $storeIds = $this -> lookupStoreIds($rulesId);
        }
        $object -> setStoreIds($storeIds);
    }

    /**
     * Get store ids, to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id) {
        return $this -> _getReadAdapter() -> fetchCol($this -> _getReadAdapter() -> select() -> from($this -> getTable('prolabels/store'), 'store_id') -> where('rule_id = ?', $id));
    }

    /**
     * Update products which are matched for rule
     *
     * @param Mage_CatalogRule_Model_Rule $rule
     * @return Mage_CatalogRule_Model_Resource_Rule
     */
    public function updateProlabelsRuleProductData($rule) {
        $ruleId = $rule -> getId();
        $write = $this -> _getWriteAdapter();
        $write -> beginTransaction();
        if ($rule -> getProductsFilter()) {
            $write -> delete($this -> getTable('prolabels/index'), $write -> quoteInto('rules_id=?', $ruleId) . $write -> quoteInto('and product_id in (?)', implode(',', $rule -> getProductsFilter())));
        } else {
            $write -> delete($this -> getTable('prolabels/index'), $write -> quoteInto('rules_id=?', $ruleId));
        }

        //        Varien_Profiler::start('__MATCH_PRODUCTS__');
        $productIds = $rule -> getMatchingProductIds();

        //        Varien_Profiler::stop('__MATCH_PRODUCTS__');

        $rows = array();

        try {
            foreach ($productIds as $productId) {
                $rows[] = array('rules_id' => $ruleId, 'product_id' => $productId, );

                if (count($rows) == 1000) {
                    $write -> insertMultiple($this -> getTable('prolabels/index'), $rows);
                    $rows = array();
                }
            }

            if (!empty($rows)) {
                $write -> insertMultiple($this -> getTable('prolabels/index'), $rows);
            }

            $write -> commit();
        } catch (Exception $e) {
            $write -> rollback();
            throw $e;
        }
        return $this;
    }

    /**
     * Get all product ids matched for rule
     *
     * @param int $ruleId
     * @return array
     */
    public function getRuleProductIds($ruleId) {
        $read = $this -> _getReadAdapter();
        $select = $read -> select() -> from($this -> getTable('prolabels/index'), 'product_id') -> where('rules_id=?', $ruleId);
        return $read -> fetchCol($select);
    }

    /**
     * Generate catalog price rules prices for specified date range
     * If from date is not defined - will be used previous day by UTC
     * If to date is not defined - will be used next day by UTC
     *
     * @param int|string|null $fromDate
     * @param int|string|null $toDate
     * @param int $productId
     * @return Mage_CatalogRule_Model_Resource_Rule
     */
    public function applyAllRulesForDateRange($fromDate = null, $toDate = null, $productId = null) {
        $write = $this -> _getWriteAdapter();
        $write -> beginTransaction();

        $clearOldData = false;

        $product = null;
        if ($productId instanceof Mage_Catalog_Model_Product) {
            $product = $productId;
            $productId = $productId -> getId();
        }

        $dayPrices = array();

        try {
            /**
             * Update products rules prices per each website separately
             * because of max join limit in mysql
             */
            foreach (Mage::app()->getWebsites(false) as $website) {
                $productsStmt = $this -> _getRuleProductsStmt($fromDate, $toDate, $productId, $website -> getId());

                $dayPrices = array();
                $stopFlags = array();
                $prevKey = null;

                while ($ruleData = $productsStmt -> fetch()) {
                    $ruleProductId = $ruleData['product_id'];
                    $productKey = $ruleProductId . '_' . $ruleData['website_id'] . '_' . $ruleData['customer_group_id'];

                    if ($prevKey && ($prevKey != $productKey)) {
                        $stopFlags = array();
                    }

                    /**
                     * Build prices for each day
                     */
                    for ($time = $fromDate; $time <= $toDate; $time += self::SECONDS_IN_DAY) {
                        if (($ruleData['from_time'] == 0 || $time >= $ruleData['from_time']) && ($ruleData['to_time'] == 0 || $time <= $ruleData['to_time'])) {
                            $priceKey = $time . '_' . $productKey;

                            if (isset($stopFlags[$priceKey])) {
                                continue;
                            }

                            if (!isset($dayPrices[$priceKey])) {
                                $dayPrices[$priceKey] = array('rule_date' => $time, 'website_id' => $ruleData['website_id'], 'customer_group_id' => $ruleData['customer_group_id'], 'product_id' => $ruleProductId, 'rule_price' => $this -> _calcRuleProductPrice($ruleData), 'latest_start_date' => $ruleData['from_time'], 'earliest_end_date' => $ruleData['to_time']);
                            } else {
                                $dayPrices[$priceKey]['rule_price'] = $this -> _calcRuleProductPrice($ruleData, $dayPrices[$priceKey]);
                                $dayPrices[$priceKey]['latest_start_date'] = max($dayPrices[$priceKey]['latest_start_date'], $ruleData['from_time']);
                                $dayPrices[$priceKey]['earliest_end_date'] = min($dayPrices[$priceKey]['earliest_end_date'], $ruleData['to_time']);
                            }

                            if ($ruleData['action_stop']) {
                                $stopFlags[$priceKey] = true;
                            }
                        }
                    }

                    $prevKey = $productKey;
                    if (count($dayPrices) > 1000) {
                        $this -> _saveRuleProductPrices($dayPrices);
                        $dayPrices = array();
                    }
                }
                $this -> _saveRuleProductPrices($dayPrices);
            }
            $this -> _saveRuleProductPrices($dayPrices);

            $write -> commit();
        } catch (Exception $e) {
            $write -> rollback();
            throw $e;
        }

        return $this;
    }

    /**
     * Apply catalog rule to product
     *
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param Mage_Catalog_Model_Product $product
     * @param array $websiteIds
     * @return Mage_CatalogRule_Model_Resource_Rule
     */
    public function applyToProduct($rule, $product, $websiteIds) {
        if (!$rule -> getIsActive()) {
            return $this;
        }

        $ruleId = $rule -> getId();
        $productId = $product -> getId();

        $write = $this -> _getWriteAdapter();
        $write -> beginTransaction();

        $customerGroupIds = $rule -> getCustomerGroupIds();

        $fromTime = strtotime($rule -> getFromDate());
        $toTime = strtotime($rule -> getToDate());
        $toTime = $toTime ? $toTime + self::SECONDS_IN_DAY - 1 : 0;

        $sortOrder = (int)$rule -> getSortOrder();
        $actionOperator = $rule -> getSimpleAction();
        $actionAmount = $rule -> getDiscountAmount();
        $actionStop = $rule -> getStopRulesProcessing();

        $rows = array();
        try {
            foreach ($websiteIds as $websiteId) {
                foreach ($customerGroupIds as $customerGroupId) {
                    $rows[] = array('rules_id' => $ruleId, 'product_id' => $productId, );

                    if (count($rows) == 1000) {
                        $write -> insertMultiple($this -> getTable('prolabels/index'), $rows);
                        $rows = array();
                    }
                }
            }

            if (!empty($rows)) {
                $write -> insertMultiple($this -> getTable('prolabels/index'), $rows);
            }
        } catch (Exception $e) {
            $write -> rollback();
            throw $e;
        }

        $this -> applyAllRulesForDateRange(null, null, $product);
        $write -> commit();

        return $this;
    }

    public function deleteAllLabelIndex() {
        try {
            $write = $this -> _getWriteAdapter();
            $write -> beginTransaction();
            $write -> delete($this -> getTable('prolabels/index'));
            $write -> commit();
            $write -> query("ALTER TABLE " . $this -> getTable('prolabels/index') . " AUTO_INCREMENT = 1");
        } catch (Exception $e) {
            $write -> rollback();
        }
        return $this;
    }

    public function applyAll()
    {
        $collection = Mage::getResourceModel('prolabels/label_collection');
        $collection->getSelect()
            ->where('rules_id>3')
            ->where('label_status=1');

        $collection->walk(array($this, 'updateProlabelsRuleProductData'));
    }

    public function apllySystemRule(TM_ProLabels_Model_Label $rule, $productId) {
        if (!$rule -> getData('label_status')) {
            return $this;
        }
        $this -> _validateSystemRule($rule -> getId(), $productId);

        return $this;
    }

    protected function _validateSystemRule($ruleId, $productId) {
        switch ($ruleId) {
            case '1' :
                $this -> validateOnSale($ruleId, $productId);
                break;
            case '2' :
                $this -> validateInStock($ruleId, $productId);
                break;
            case '3' :
                $this -> validateIsNew($ruleId, $productId);
                break;
        }
    }

    public function validateInStock($ruleId, $productId) {
        $result = array();
        $model = Mage::getModel('catalog/product');
        $out = Mage::getStoreConfig("prolabels/instock/out");
        $minItems = (int)Mage::getStoreConfig("prolabels/instock/minitems");
        try {
            $product = $model -> load($productId);
            if ($product -> getTypeInstance() instanceof Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Type_Configurable) {
                $model = new Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Type_Configurable();
                $simpleProductIds = $model -> getChildrenIds($product -> getId());
                foreach (current($simpleProductIds) as $productId) {
                    $simpleProduct = Mage::getModel('catalog/product') -> load($productId);
                    $productQty = $simpleProduct -> getData('stock_item') -> qty;
                    $quantity = $quantity + (int)$productQty;
                }
            } elseif ($product -> getTypeInstance() instanceof Mage_Bundle_Model_Product_Type) {
                $groupSum = array();
                foreach ($product->getTypeInstance()->getOptions() as $option) {
                    if (!$option -> getData('required')) {
                        return $this;
                    }
                    $selections = $option -> getSelections();
                    if (count($selections) < 1) {
                        continue;
                    }
                    //                    $sum = 0;
                    foreach ($selections as $simpleProduct) {
                        $sum += $simpleProduct -> getData('stock_item') -> qty;
                    }
                    $groupSum[] = $sum;
                    $sum = 0;
                }
                if (count($groupSum) < 1) {
                    $quantity = 999999;
                } else {
                    $quantity = min($groupSum);
                }

            } else {
                $quantity = $product -> getData('stock_item') -> qty;
            }

            if (!$product -> getData('stock_item') -> is_in_stock || $quantity == 0) {
                if ($out) {
                    $result = array('rules_id' => $ruleId, 'product_id' => $product -> getId(), );
                }
            } else {
                if ($quantity > 0 && $quantity < $minItems) {
                    $result = array('rules_id' => $ruleId, 'product_id' => $product -> getId(), );
                }
            }
            if (count($result) > 0) {
                $model = Mage::getModel('prolabels/index');
                $model -> addData($result);
                $model -> save();
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $this;
    }

    public function validateIsNew($ruleId, $productId) {
        $write = $this -> _getWriteAdapter();
        $model = Mage::getModel('catalog/product');
        try {
            $product = $model -> load($productId);
            if ($product -> getData('news_from_date') === null && $product -> getData('news_to_date') === null) {
                return $this;
            }

            $today = Mage::getModel('core/date')->timestamp(time());

            if ($product->getData('news_from_date') && null === $product->getData('news_to_date')) {
                $from = Mage::getModel('core/date')->timestamp($product->getData('news_from_date'));
                if ($today > $from) {
                    $result = array('rules_id' => $ruleId, 'product_id' => $product -> getId(), );
                    $model = Mage::getModel('prolabels/index');
                $model -> addData($result);
                $model -> save();
                    return $this;
                }
            }

            if ($product->getData('news_to_date') && null === $product->getData('news_from_date')) {
                $to =  Mage::getModel('core/date')->timestamp($product->getData('news_to_date'));
                if ($today < $to) {
                    $result = array('rules_id' => $ruleId, 'product_id' => $product -> getId(), );
                    $model = Mage::getModel('prolabels/index');
                $model -> addData($result);
                $model -> save();
                    return $this;
                }
            }
            $from = Mage::getModel('core/date')->timestamp($product->getData('news_from_date'));
            $to =  Mage::getModel('core/date')->timestamp($product->getData('news_to_date'));

            if ($from && $today < $from) {
                return false;
            }
            if ($to && $today > $to) {
                return false;
            }
            if (!$to && !$from) {
                return false;
            }

            $result = array('rules_id' => $ruleId, 'product_id' => $product -> getId(), );
            $model = Mage::getModel('prolabels/index');
            $model -> addData($result);
            $model -> save();
        } catch (Exception $e) {
            throw $e;
        }
        return $this;
    }

    public function checkProductCatalogRule($productId)
    {
        $product = Mage::getModel('catalog/product');
        $product->load($productId);
        $today  = date("Y-m-d", Mage::getModel('core/date')->timestamp(time()));
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('catalogrule/rule_product_price'))
            ->where('product_id = ?', $productId)
            ->where('rule_date = ?', $today)
            ->where('rule_price < ?', $product->getFinalPrice());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            return true;
        }
        return false;
    }

    public function validateOnSale($ruleId, $productId) {
        $result = array();
        $model = Mage::getModel('catalog/product');
        try {
            $product = $model -> load($productId);
            if ($product -> getTypeInstance() instanceof Mage_Catalog_Model_Product_Type_Grouped) {
                $simpleProductIds = $product -> getTypeInstance() -> getAssociatedProductIds();
                $price = 0;
                $finalPrice = 0;
                $i = 0;
                foreach ($simpleProductIds as $simpleProductId) {
                    $simpleProduct = Mage::getModel('catalog/product') -> load($simpleProductId);

                    if ($simpleProduct -> getData('special_price')) {
                        $finalPrice = $simpleProduct -> getData('special_price');
                        $price = $simpleProduct -> getData('price');
                        if ($i == 0) {
                            Mage::unregister('prolabelprice');
                            Mage::unregister('prolabelfinalprice');
                            Mage::register('prolabelprice', $price);
                            Mage::register('prolabelfinalprice', $finalPrice);
                        }

                        if ($i > 0) {
                            if (($price - $finalPrice) > (Mage::registry('prolabelprice') - Mage::registry('prolabelfinalprice'))) {
                                Mage::unregister('prolabelprice');
                                Mage::unregister('prolabelfinalprice');
                                Mage::register('prolabelprice', $price);
                                Mage::register('prolabelfinalprice', $finalPrice);
                            }
                        }
                        $i++;
                    }
                }
                if (Mage::registry('prolabelfinalprice') && Mage::registry('prolabelprice')) {
                    $result = array('rules_id' => $ruleId, 'product_id' => $product -> getId(), );
                }
            }
            if ($product -> getTypeInstance() instanceof Mage_Bundle_Model_Product_Type) {
                if ($product -> getData('special_price') && $product -> getData('special_price') !== null) {
                    $result = array('rules_id' => $ruleId, 'product_id' => $product -> getId(), );
                }
            } elseif ($product -> getData('price') > $product -> getData('special_price') && $product -> getData('special_price') > 0 && $this -> checkSpecailDate($product)) {
                $result = array('rules_id' => $ruleId, 'product_id' => $product -> getId(), );
            }
            if ($this->checkProductCatalogRule($productId)) {
                $result = array('rules_id' => $ruleId, 'product_id' => $product -> getId());
            }

            if (count($result) > 0) {
                $model = Mage::getModel('prolabels/index');
                $model -> addData($result);
                $model -> save();
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $this;
    }

    public function checkSpecailDate($product)
    {
        $today = Mage::getModel('core/date')->timestamp(time());

        if ($product->getData('special_from_date') && null === $product->getData('special_to_date')) {
            $from = Mage::getModel('core/date')->timestamp($product->getData('special_from_date'));
            if ($today > $from) {
                return true;
            }
            return false;
        }

        if ($product->getData('special_to_date') && null === $product->getData('special_from_date')) {
            $to =  Mage::getModel('core/date')->timestamp($product->getData('special_to_date'));
            if ($today < $to) {
                return true;
            }
            return false;
        }
        $from = Mage::getModel('core/date')->timestamp($product->getData('special_from_date'));
        $to =  Mage::getModel('core/date')->timestamp($product->getData('special_to_date'));

        if ($from && $today < $from) {
            return false;
        }
        if ($to && $today > $to) {
            return false;
        }
        if (!$to && !$from) {
            return false;
        }
        return true;
    }

    public function getItemsToProcess($count = 1, $step = 0) {
        $connection = $this -> _getReadAdapter();

        $labelSelect = $connection -> select() -> from(array('cp' => $this -> getTable('catalog/product')), 'entity_id') -> order('entity_id') -> limit($count, $count * $step);

        $result = $connection -> fetchCol($labelSelect);
        return $result;
    }

    public function getProductRuleIds($productId) {
        return $this->_getReadAdapter()->fetchCol(
                    $this -> _getReadAdapter()->select()
                        ->from($this -> getTable('index'), 'rules_id')
                        ->where('product_id = ?', $productId));
    }

    public function reindexAllSystemLabels() {
        $collection = Mage::getModel('catalog/product') -> getCollection();
        $collection -> walk(array($this, 'updateLabelDataForProduct'));
        return $this;
    }

    public function updateLabelDataForProduct($product) {
        $productId = $product -> getId();
        $rule = Mage::getModel('prolabels/label');
        for ($i = 1; $i <= 3; $i++) {
            $rule -> load($i);
            $this -> apllySystemRule($rule, $productId);
        }

        return $this;
    }

}
