<?php

class TM_EasyBanner_Model_Banner extends Mage_Rule_Model_Rule
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('easybanner/banner');
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('easybanner/rule_condition_combine');
    }

    /**
     * Return true if banner status = 1
     * and banner linked to active placeholder
     *
     * @return boolean
     */
    public function isActive()
    {
        if ($this->getStatus()/* && count($this->getPlaceholderIds(true))*/) {
            return true;
        }
        return false;
    }

    public function getPlaceholderIds($isActive = false)
    {
        $key = $isActive ? 'placeholder_ids_active' : 'placeholder_ids';
        $ids = $this->_getData($key);
        if (null === $ids) {
            $this->_getResource()->loadPlaceholderIds($this, $isActive);
            $ids = $this->_getData($key);
        }
        return $ids;
    }

    public function getStoreIds()
    {
        $ids = $this->_getData('store_ids');
        if (null === $ids) {
            $this->_getResource()->loadStoreIds($this);
            $ids = $this->_getData('store_ids');
        }
        return $ids;
    }

    public function getClicksCount()
    {
        return $this->getStatistics('clicks_count');
    }

    public function getCookieValues()
    {
        $values = $this->_getData('cookie_values');
        if (null === $values) {
            $data = Mage::app()->getCookie()->get('easybanner');
            try {
                $data = Mage::helper('core')->jsonDecode($data);
                if (!$data) {
                    $data = array();
                }
            } catch (Exception $e) {
                $data = array();
            }
            $values = new Varien_Object($data);
            $this->setData('cookie_values', $values);
        }
        return $values;
    }

    public function getDisplayCount()
    {
        return $this->getStatistics('display_count');
    }

    public function getDisplayCountPerCustomer()
    {
        $key = Mage::helper('easybanner')->getBannerClassName($this->getIdentifier());
        return (int)$this->getCookieValues()->getData($key . '/display_count');
    }

    public function getSubtotal($skipTax = true)
    {
        $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
        if (isset($totals['subtotal'])) {
            $config = Mage::getSingleton('tax/config');
            if ($config->displayCartSubtotalBoth()) {
                if ($skipTax) {
                    $subtotal = $totals['subtotal']->getValueExclTax();
                } else {
                    $subtotal = $totals['subtotal']->getValueInclTax();
                }
            } elseif ($config->displayCartSubtotalInclTax()) {
                $subtotal = $totals['subtotal']->getValueInclTax();
            } else {
                $subtotal = $totals['subtotal']->getValue();
                if (!$skipTax && isset($totals['tax'])) {
                    $subtotal+= $totals['tax']->getValue();
                }
            }
            return $subtotal;
        }
        return false;
    }

    public function getStatistics($key)
    {
        $stat = $this->_getData($key);
        if (null === $stat) {
            $this->_getResource()->loadStatistics($this);
            $stat = $this->_getData($key);
        }
        return $stat;
    }

    /**
     * Checks is banner is active for requested store
     * Used to check is it possible to click on banner
     *
     * @param int $store
     * @return mixed int|boolean
     */
    public function check($store)
    {
        return $this->isActive() && (in_array($store, $this->getStoreIds()) || in_array(0, $this->getStoreIds()));
    }

    public function duplicate()
    {
        $newBanner = Mage::getModel('easybanner/banner')->setData($this->getData())
            ->setIsDuplicate(true)
            // ->setOriginalId($this->getId())
            ->setIdentifier($this->getIdentifier() . '_duplicate')
            ->setId(null)
            ->setStoreIds($this->getStoreIds())
            ->setPlaceholderIds($this->getPlaceholderIds())
            ->setConditions($this->getConditions());

        $newBanner->save();
        return $newBanner;
    }

    /**
     * @param mixed $name
     * @return TM_EasyBanner_Model_Mysql4_Banner_Collection
     */
    public function getCollectionByPlaceholderName($name)
    {
        /**
         * @var TM_EasyBanner_Model_Mysql4_Banner_Collection
         */
        $collection = $this->getCollection();
        $collection->addStatistics()
            ->joinLeft(
                'banner_placeholder',
                'banner_placeholder.banner_id = main_table.banner_id',
                ''
            )
            ->joinLeft(
                'placeholder',
                'placeholder.placeholder_id = banner_placeholder.placeholder_id',
                ''
            )
//            ->joinLeft(
//                'banner_store',
//                'banner_store.banner_id = main_table.banner_id',
//                ''
//            )
//            ->addFieldToFilter('banner_store.store_id', array(
//                'in' => array(0, (int)Mage::app()->getStore()->getId())
//            ))
            ->addFieldToFilter('status', 1);

        if (is_numeric($name)) {
            $collection->addFieldToFilter('placeholder.placeholder_id', $name);
        } else {
            $collection->addFieldToFilter('placeholder', $name);
        }

        return $collection;
    }

    /**
     * Checks all conditions of the banner
     *
     * @return bool
     */
    public function isVisible()
    {
        if (!$this->getStatus()
            || (!in_array(Mage::app()->getStore()->getId(), $this->getStoreIds())
                && !in_array(0, $this->getStoreIds()))) { // all stores

            return false;
        }

        $conditions = unserialize($this->getConditionsSerialized());
        return $this->_validateConditions($conditions);
    }

    protected function _validateConditions(&$filter, $aggregator = null, $value = null, $level = 0)
    {
        $result = true;
        $finalResult = null;
        if (isset($filter['aggregator']) && !empty($filter['conditions'])) {
            foreach ($filter['conditions'] as $key => $condition) {
                $result = $this->_validateConditions(
                    $condition,
                    $filter['aggregator'],
                    $filter['value'],
                    $level + 1
                );

                // unset false conditions to skip their validation on client side
                // @see js_conditions
                if (($filter['value'] == '1' && !$result)
                    || ($filter['value'] == '0' && $result)) {

                    unset($filter['conditions'][$key]);
                    $filter['conditions'] = array_values($filter['conditions']);
                }

                if (($filter['aggregator'] == 'all' && $filter['value'] == '1' && !$result)
                    || ($filter['aggregator'] == 'any' && $filter['value'] == '1' && $result)) {

                    if (null === $finalResult) {
                        $finalResult = $result;
                    }
                } elseif (($filter['aggregator'] == 'all' && $filter['value'] == '0' && $result)
                    || ($filter['aggregator'] == 'any' && $filter['value'] == '0' && !$result)) {

                    $result = !$result;
                    if (null === $finalResult) {
                        $finalResult = $result;
                    }
                }
            }
        } elseif (!empty($filter['attribute'])) {
            switch($filter['attribute']) {
                case 'category_ids':
                    if ($category = Mage::registry('current_category')) {
                        $comparator = $category->getId();
                    } else {
                        $comparator = $this->_getRequestParam('category_id');
                    }
                    break;
                case 'product_ids':
                    if ($product = Mage::registry('current_product')) {
                        $comparator = $product->getId();
                    } else {
                        $comparator = $this->_getRequestParam('product_id');
                    }
                    break;
                case 'date': case 'time':
                    $filter['value'] = strtotime($filter['value']);
                    $date = Mage::app()->getLocale()->date(time());
                    $date->setHour(0)
                        ->setMinute(0)
                        ->setSecond(0)
                        ->setMilliSecond(0);
                    $comparator = $date->get(Zend_Date::TIMESTAMP)
                        + $date->get(Zend_Date::TIMEZONE_SECS);
                    unset($date);
                    break;
                case 'handle':
                    $comparator = Mage::getSingleton('core/layout')
                        ->getUpdate()
                        ->getHandles();
                    break;
                case 'clicks_count':
                    $comparator = $this->getClicksCount();
                    break;
                case 'display_count':
                    $comparator = $this->getDisplayCount();
                    break;
                case 'display_count_per_customer':
                    $comparator = $this->getDisplayCountPerCustomer();
                    break;
                case 'customer_group':
                    $comparator = Mage::getSingleton('customer/session')
                        ->getCustomerGroupId();
                    break;
                case 'subtotal_excl':
                    $comparator = $this->getSubtotal();
                    if (false === $comparator) {
                        return true;
                    }
                    break;
                case 'subtotal_incl':
                    $comparator = $this->getSubtotal(false);
                    if (false === $comparator) {
                        return true;
                    }
                    break;
                default:
                    // client side filters: activity|inactivity
                    // filters always has only 1 element, so we can return here
                    return true;
            }
            $result = $this->_compareCondition(
                $filter['value'], $comparator, $filter['operator']
            );
        }

        if (0 === $level) {
            $this->setJsConditions($filter);
        }

        if (null !== $finalResult) {
            return $finalResult;
        }
        return $result;
    }

    protected function _compareCondition($v1, $v2, $op)
    {
        if ($op=='()' || $op=='!()' || $op=='!=' || $op=='==' || $op=='{}' || $op=='!{}') {
            $v1 = explode(',', $v1);
            foreach ($v1 as &$v) {
                $v = trim($v);
            }
            if (!is_array($v2)) {
                $v2 = array($v2);
            }
        }

        $result = false;

        switch ($op) {
            case '==': case '!=':
                if (is_array($v1)) {
                    if (is_array($v2)) {
                        $result = array_diff($v2, $v1);
                        $result = empty($result) && (sizeof($v2) == sizeof($v1));
                    } else {
                        return false;
                    }
                } else {
                    if (is_array($v2)) {
                        $result = in_array($v1, $v2);
                    } else {
                        $result = $v2==$v1;
                    }
                }
                break;

            case '<=': case '>':
                if (is_array($v2)) {
                    $result = false;
                } else {
                    $result = $v2<=$v1;
                }
                break;

            case '>=': case '<':
                if (is_array($v2)) {
                    $result = false;
                } else {
                    $result = $v2>=$v1;
                }
                break;

            case '{}': case '!{}':
                if (is_array($v1)) {
                    if (is_array($v2)) {
                        $result = array_diff($v1, $v2);
                        $result = empty($result);
                    } else {
                        return false;
                    }
                } else {
                    if (is_array($v2)) {
                        $result = false;
                    } else {
                        $result = stripos((string)$v2, (string)$v1)!==false;
                    }
                }
                break;

            case '()': case '!()':
                if (is_array($v2)) {
                    $result = count(array_intersect($v2, (array)$v1)) > 0;
                } else {
                    $result = in_array($v2, (array)$v1);
                }
                break;
        }

        if ('!='==$op || '>'==$op || '<'==$op || '!{}'==$op || '!()'==$op) {
            $result = !$result;
        }

        return $result;
    }

    /**
     * TM_Cache compatibility
     *
     * @param  string $param
     * @return string
     */
    protected function _getRequestParam($param)
    {
        $value = null;
        $request = Mage::app()->getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        switch ($param) {
            case 'category_id':
                if ('catalog' === $module && 'view' === $action) {
                    if ('category' === $controller) {
                        $value = $request->getParam('id');
                    } elseif ('product' === $controller) {
                        $value = $request->getParam('category');
                    }
                }
                break;
            case 'product_id':
                if ('catalog' === $module
                    && 'product' === $controller
                    && 'view' === $action) {

                    $value = $request->getParam('id');
                }
                break;
        }
        return $value;
    }
}
