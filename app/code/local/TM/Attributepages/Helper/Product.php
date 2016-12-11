<?php

class TM_Attributepages_Helper_Product extends Mage_Core_Helper_Abstract
{
    /**
     * Array of helper variables
     *
     * @var array
     */
    protected $_data = array();

    /**
     * @var TM_Attributepages_Block_Product_Option
     */
    protected $_block = null;

    public function toHtml()
    {
        if (!$this->getBlock() || !$this->getBlock()->getProduct()) {
            return '';
        }

        $collection = $this->getData('collection');
        if ($this->getData('attribute_code')) {
            $this->appendPages(
                $collection ? $collection : $this->getBlock()->getProduct(),
                $this->getData('attribute_code'),
                $this->getData('parent_page_identifier')
            );
        }
        $output = $this->getBlock()->toHtml();

        // fix to reset configuration in case if block is rendered in
        // another template with different config
        $this->_block = null;

        return $output;
    }

    /**
     * @return TM_Attributepages_Block_Product_Option
     */
    public function getBlock()
    {
        if (null === $this->_block) {
            $this->_block = Mage::app()->getLayout()
                ->createBlock('attributepages/product_option')
                ->setTemplate('tm/attributepages/product/options.phtml');
        }
        return $this->_block;
    }

    /**
     * Magic method to call TM_Attributepages_Block_Product_Option methods
     *
     * @param  string $name
     * @param  array $arguments
     * @return TM_Attributepages_Helper_Product
     */
    public function __call($name, $arguments)
    {
        call_user_func_array(array($this->getBlock(), $name), $arguments);
        return $this;
    }

    /**
     * Set image width and height
     *
     * @param int $width
     * @param int $height
     */
    public function setSize($width, $height)
    {
        $this->getBlock()->setWidth($width)->setHeight($height);
        return $this;
    }

    /**
     * Set collection to load attributepages for each of the collection items
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     */
    public function setCollection($collection)
    {
        return $this->setData('collection', $collection);
    }

    /**
     * Set attribute code to load and show
     *
     * @param mixed $code String of array
     */
    public function setAttributeCode($code)
    {
        $this->getBlock()->setAttributeToShow($code);
        return $this->setData('attribute_code', $code);
    }

    /**
     * Set parent page identifier. Usefull when attributepage options belongs
     * to multiple attributepages
     *
     * @param mixed $identifier key=>value array or string
     */
    public function setParentPageIdentifier($identifier)
    {
        return $this->setData('parent_page_identifier', $identifier);
    }

    public function setData($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    public function getData($key, $default = null)
    {
        if (!array_key_exists($key, $this->_data)) {
            return $default;
        }
        return $this->_data[$key];
    }

    /**
     * Append attributepages to product collection or single product
     *
     * @param  mixed $collection                Product collection of product itself
     * @param  mixed $attributes                Attribute code or array of codes
     * @param  mixed $parentPageIdentifiers     Attributepage identifier. Optional parameter.
     * @return TM_Attributepages_Helper_Data
     */
    public function appendPages($collection, $attributes, $parentPageIdentifiers = null)
    {
        if (!$attributes) {
            return $this;
        }

        if ($collection instanceof Mage_Catalog_Model_Product) {
            $product = $collection;
            $collection = array($collection);
        } else {
            $product = $collection->getFirstItem();
        }

        if (!is_array($attributes)) {
            if ($parentPageIdentifiers && !is_array($parentPageIdentifiers)) {
                $parentPageIdentifiers = array(
                    $attributes => $parentPageIdentifiers
                );
            }
            $attributes = array($attributes);
        }

        // do not load already loaded collection
        $loaded = $product->getAttributepages();
        if (null !== $loaded) {
            $notLoaded = array_diff($attributes, array_keys($loaded));
            if (!$notLoaded) {
                return $this;
            }
        }

        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('attribute_code', array('IN' => $attributes));
        if (!$attributeCollection->count()) {
            return $this;
        }

        $storeId = Mage::app()->getStore()->getId();
        $attributepageCollection = Mage::getResourceModel('attributepages/entity_collection')
            ->addUseForAttributePageFilter() // enabled flag
            ->addFieldToFilter('attribute_id', array(
                'IN' => $attributeCollection->getColumnValues('attribute_id')
            ))
            ->addStoreFilter($storeId);

        foreach ($attributepageCollection as $attributepage) {
            $item = $attributeCollection->getItemById($attributepage->getAttributeId());
            if (!$item) {
                continue;
            }
            $attributepage->setAttributeCode($item->getAttributeCode());
        }

        // prepare each of the possible options
        $result = array();
        foreach ($collection as $product) {
            foreach ($attributes as $attributeCode) {
                $optionId = $product->getData($attributeCode);
                if (!$optionId || !empty($result[$attributeCode][$optionId])) {
                    continue;
                }

                if (!$option = $this->findOption($attributepageCollection, $optionId, $storeId)) {
                    continue;
                }

                if (is_array($parentPageIdentifiers)) {
                    if (isset($parentPageIdentifiers[$attributeCode])) {
                        $parentPageIdentifier = $parentPageIdentifiers[$attributeCode];
                    } else {
                        $parentPageIdentifier = null;
                    }
                } else {
                    $parentPageIdentifier = $parentPageIdentifiers;
                }

                $option->setParentPageIdentifier($parentPageIdentifier);
                $parentPage = $this->findParentPage(
                    $option, $attributepageCollection, $storeId, $parentPageIdentifier
                );
                if (!$parentPage) { // disabled page
                    continue;
                }

                $option->setParentPage($parentPage);
                $result[$attributeCode][$optionId] = $option;
            }
        }

        foreach ($collection as $product) {
            $attributepages = array();
            foreach ($attributes as $attributeCode) {
                $optionId = $product->getData($attributeCode);
                if (!$optionId || empty($result[$attributeCode][$optionId])) {
                    $attributepages[$attributeCode] = null;
                    continue;
                }
                $attributepages[$attributeCode] = $result[$attributeCode][$optionId];
            }
            $product->setAttributepages($attributepages);
        }

        return $this;
    }

    /**
     * Find the most suitable option page from $collection
     *
     * @param  TM_Attributepages_Model_Resource_Entity_Collection $collection
     * @param  int $storeId
     * @return TM_Attributepages_Model_Entity or false
     */
    public function findOption(
        TM_Attributepages_Model_Resource_Entity_Collection $collection,
        $optionId,
        $storeId)
    {
        $option = false;
        foreach ($collection as $possibleOption) {
            if (!$possibleOption->getOptionId()) { // attribute based page
                continue;
            }
            if ($possibleOption->getOptionId() !== $optionId) {
                continue;
            }

            if ($option) {
                if ($possibleOption->getStoreId() != $storeId) {
                    continue;
                }
            }
            $option = $possibleOption;
            if ($option->getStoreId() == $storeId) {
                break;
            }
        }
        return $option;
    }

    /**
     * Find parent page among $collection for $option
     *
     * @param  TM_Attributepages_Model_Entity $option
     * @param  TM_Attributepages_Model_Resource_Entity_Collection $collection
     * @param  int $storeId
     * @param  string $identifier
     * @return TM_Attributepages_Model_Entity or false
     */
    public function findParentPage(
        TM_Attributepages_Model_Entity $option,
        TM_Attributepages_Model_Resource_Entity_Collection $collection,
        $storeId,
        $identifier = null)
    {
        if ($identifier) {
            return $collection->getItemByColumnValue('identifier', $identifier);
        }

        $parentPage = false;
        $parentPages = $collection->getItemsByColumnValue('option_id', null);
        foreach ($parentPages as $page) {
            if ($page->getAttributeId() !== $option->getAttributeId()) {
                continue;
            }

            $excludedOptions = $page->getExcludedOptionIdsArray();
            if (in_array($option->getOptionId(), $excludedOptions)) {
                continue;
            }
            if ($parentPage) {
                if ($page->getStoreId() != $storeId) {
                    continue;
                }
            }
            $parentPage = $page;
            if ($parentPage->getStoreId() == $storeId) {
                break;
            }
        }
        return $parentPage;
    }
}
