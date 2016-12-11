<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Model_Label extends Mage_CatalogRule_Model_Rule
{
     /**
     * Matched product ids array
     *
     * @var array
     */
    protected $_productIds;

    /**
     * Limitation for products collection
     *
     * @var int|array|null
     */
    protected $_productsFilter = null;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('prolabels/label');
        $this->setIdFieldName('rules_id');
    }

    public function setProductsFilter($productIds)
    {
        $this->_productsFilter = $productIds;
    }

    /**
     * Returns products filter
     *
     * @return array|int|null
     */
    public function getProductsFilter()
    {
        return $this->_productsFilter;
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('prolabels/rule_condition_combine');
    }

    protected function _beforeSave()
    {
        $this->_getResource()->loadLabelProductImage($this);
        $this->_getResource()->loadLabelCategoryImage($this);

        parent::_beforeSave();
    }

    protected function _afterSave()
    {
        if ((int)$this->getData('label_status') != 0 and (int)$this->getId() > 3) {
            $this->_getResource()->updateProlabelsRuleProductData($this);
        }

        $this->_getResource()->addLabelStoreIds($this);

        parent::_afterSave();
    }

    protected function _afterLoad()
    {
        if (Mage::getStoreConfig("prolabels/general/customer_group")) {
            $this->setData('customer_group_ids', unserialize($this->getCustomerGroup()));
        }

        parent::_afterLoad();
    }

    public function loadStoreIds(TM_ProLabels_Model_Label $object)
    {
        $rulesId = $object->getId();
        $storeIds = array();
        if ($rulesId) {
            $storeIds = $this->lookupStoreIds($rulesId);
        }
        $object->setStoreIds($storeIds);
    }

    /**
     * Get store ids, to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        return $this->_getResource()->lookupStoreIds($id);
    }

    public function getLabelsData($productId, $mode)
    {
        return $this->_getResource()->getProductLabelsData($productId, $mode);
    }

    public function getCatalogLabels($productIds, $mode)
    {
        return $this->_getResource()->getCatalogLabels($productIds, $mode);
    }

    public function getContentLabelsData($productId, $mode)
    {
        return $this->_getResource()->getContentLabelsData($productId, $mode);
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if (is_null($this->_productIds)) {
            $this->_productIds = array();
            $this->setCollectedAttributes(array());

            $productCollection = Mage::getResourceModel('catalog/product_collection');
            $this->getConditions()->collectValidatedAttributes($productCollection);
            Mage::getSingleton('core/resource_iterator')->walk(
                $productCollection->getSelect(),
                array(array($this, 'callbackValidateProduct')),
                array(
                    'attributes' => $this->getCollectedAttributes(),
                    'product'    => Mage::getModel('catalog/product'),
                )
            );
        }
        return $this->_productIds;
    }

    /**
     * Callback function for product matching
     *
     * @param $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        if ($this->getConditions()->validate($product)) {
            $this->_productIds[] = $product->getId();
        }
    }

    /**
     * Apply rule to product
     *
     * @param int|Mage_Catalog_Model_Product $product
     * @param array $websiteIds
     * @return void
     */
    public function applyToProduct($product, $websiteIds=null)
    {
        if (is_numeric($product)) {
            $product = Mage::getModel('catalog/product')->load($product);
        }
        if (is_null($websiteIds)) {
            $websiteIds = explode(',', $this->getWebsiteIds());
        }
        $this->getResource()->applyToProduct($this, $product, $websiteIds);
    }

    /**
     * Apply all price rules, invalidate related cache and refresh price index
     *
     * @return Mage_CatalogRule_Model_Rule
     */
    public function applyAll()
    {
        $collection = $this->getResourceCollection();
        $collection->getSelect()
            ->where('rules_id>3')
            ->where('label_status=1');

        $collection->walk(array($this->_getResource(), 'updateProlabelsRuleProductData'));
    }


    /**
     * Apply all price rules to product
     *
     * @param  int|Mage_Catalog_Model_Product $product
     * @return Mage_CatalogRule_Model_Rule
     */
    public function applyAllRulesToProduct($product)
    {
        $this->_getResource()->applyAllRulesForDateRange(NULL, NULL, $product);
        $this->_invalidateCache();

        if ($product instanceof Mage_Catalog_Model_Product) {
            $productId = $product->getId();
        } else {
            $productId = $product;
        }

        if ($productId) {
            Mage::getSingleton('index/indexer')->processEntityAction(
                new Varien_Object(array('id' => $productId)),
                Mage_Catalog_Model_Product::ENTITY,
                Mage_Catalog_Model_Product_Indexer_Price::EVENT_TYPE_REINDEX_PRICE
            );
        }
    }


    /**
     * Return true if banner status = 1
     * and banner linked to active placeholder
     *
     * @return boolean
     */
    public function isActive()
    {
        if ($this->getData('label_status')/* && count($this->getPlaceholderIds(true))*/) {
            return true;
        }
        return false;
    }

    public function getStoreIds()
    {
        return $this->_getResource()->loadStoreIds($this);;
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

        $conditions = array(unserialize($this->getConditionsSerialized()));
        if (!$this->_validateConditions($conditions)) {
            return false;
        }

        return true;
    }
}
