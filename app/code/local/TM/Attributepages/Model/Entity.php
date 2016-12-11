<?php

class TM_Attributepages_Model_Entity extends Mage_Core_Model_Abstract
{
    const DISPLAY_MODE_MIXED       = 'mixed';
    const DISPLAY_MODE_DESCRIPTION = 'description';
    const DISPLAY_MODE_CHILDREN    = 'children';

    const LISTING_MODE_IMAGE = 'image';
    const LISTING_MODE_LINK  = 'link';
    const LISTING_MODE_GRID  = 'grid';
    const LISTING_MODE_LIST  = 'list';

    const DELIMITER = ',';

    const IMAGE_PATH = 'tm/attributepages';

    const CACHE_TAG = 'attributepages_entity';

    protected $_cacheTag = self::CACHE_TAG;

    /**
     * URL instance
     *
     * @var Mage_Core_Model_Url
     */
    protected  $_url;

    protected function _construct()
    {
        $this->_init('attributepages/entity');
    }

    /**
     * Retrieve page title
     *
     * @return string
     */
    public function getPageTitle()
    {
        $pageTitle = $this->getData('page_title');
        if (empty($pageTitle)) {
            $pageTitle = $this->getTitle();
        }
        return $pageTitle;
    }

    /**
     * Retrieve URL Instance
     *
     * @return Mage_Core_Model_Url
     */
    public function getUrlInstance()
    {
        if (null === $this->_url) {
            $this->_url = Mage::getModel('core/url');
        }
        return $this->_url;
    }

    /**
     * Retrieve page url
     *
     * @return string
     */
    public function getUrl()
    {
        $url = $this->getIdentifier();
        if ($parent = $this->getParentPage()) {
            $url = $parent->getIdentifier() . '/' . $url;
        }
        return $this->getUrlInstance()->getUrl($url);
    }

    /**
     * Retrieve parent page for current entity
     *
     * @return mixed
     */
    public function getParentPage()
    {
        if ($this->isAttributeBasedPage()) {
            return false;
        }

        $parentPage = $this->getData('parent_page');
        if (null === $parentPage) {
            $storeId = Mage::app()->getStore()->getId();
            $collection = Mage::getResourceModel('attributepages/entity_collection')
                ->addAttributeOnlyFilter()
                ->addFieldToFilter('attribute_id', $this->getAttributeId())
                ->addUseForAttributePageFilter() // enabled flag
                ->addStoreFilter($storeId);

            if ($identifier = $this->getParentPageIdentifier()) {
                $collection->addFieldToFilter('identifier', $identifier);
            }

            $parentPage = Mage::helper('attributepages/product')->findParentPage(
                $this, $collection, $storeId, $this->getParentPageIdentifier()
            );

            $this->setData('parent_page', $parentPage);
        }
        return $parentPage;
    }

    public function getExcludedOptionIdsArray()
    {
        $ids = $this->getExcludedOptionIds();
        if (!$ids) {
            $ids = array();
        } else if (!is_array($ids)) {
            $ids = explode(self::DELIMITER, $ids);
        }
        return $ids;
    }

    /**
     * Entity could be the page or option for page
     *
     * @return boolean
     */
    public function isAttributeBasedPage()
    {
        return !(bool) $this->getOptionId();
    }

    /**
     * Entity could be the page or option for page
     *
     * @return boolean
     */
    public function isOptionBasedPage()
    {
        return (bool) $this->getOptionId();
    }

    /**
     * Retrieve attribute object
     *
     * @return Mage_Eav_Model_Entity_Attribute
     */
    public function getAttribute()
    {
        $attribute = $this->_getData('attribute');
        if (!$attribute && $this->getAttributeId()) {
            $attribute = Mage::getResourceModel('catalog/product_attribute_collection')
                ->addFieldToFilter('main_table.attribute_id', $this->getAttributeId())
                ->getFirstItem();

            if ($attribute) {
                $this->setData('attribute', $attribute);
            }
        }
        return $this->_getData('attribute');
    }

    /**
     * Retreive option object
     *
     * @return Mage_Eav_Model_Entity_Attribute_Option
     */
    public function getOption()
    {
        $option = $this->_getData('option');
        if (!$option && $this->getOptionId()) {
             $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->addFieldToFilter('main_table.option_id', $this->getOptionId());

            $resource = Mage::getSingleton('core/resource');
            $collection->getSelect()
                ->joinLeft(
                    array('sort_alpha_value' => $resource->getTableName('eav/attribute_option_value')),
                    'sort_alpha_value.option_id = main_table.option_id AND sort_alpha_value.store_id = 0',
                    array('value')
                );

            $option = $collection->getFirstItem();
            if ($option) {
                $this->setData('option', $option);
            }
        }
        return $this->_getData('option');
    }

    /**
     * Retrieve related options. Callable on attribute based page only.
     *
     * @return Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection
     */
    public function getRelatedOptions()
    {
        $options = $this->_getData('related_options');
        if (!$options && $this->isAttributeBasedPage()) {
            $options = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($this->getAttributeId());

            $table = Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value');
            $options->getSelect()
                ->joinLeft(
                    array('sort_alpha_value' => $table),
                    'sort_alpha_value.option_id = main_table.option_id AND sort_alpha_value.store_id = 0',
                    array('value')
                );
        }
        return $options;
    }

    public function duplicate()
    {
        $newEntity = Mage::getModel('attributepages/entity')
            ->setData($this->getData())
            ->setIsDuplicate(true)
            ->setIdentifier($this->getIdentifier() . '-duplicate')
            ->setId(null)
            ->setStoreId($this->getStoreId());

        $newEntity->save();
        return $newEntity;
    }

    /**
     * Check if entity identifier exist for specific store
     * return entity id if page exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    /**
     * Overriden to convert the json saved display settings to array style
     *
     * @param string $key
     * @param mixed $value
     * @return TM_Attributepages_Model_Entity
     */
    public function setData($key, $value = null)
    {
        parent::setData($key, $value);

        if ((is_array($key) && array_key_exists('display_settings', $key))
            || 'display_settings' === $key) {

            if (is_array($key)) {
                $value = $key['display_settings'];
            }

            try {
                $config = Mage::helper('core')->jsonDecode($value);
            } catch (Exception $e) {
                $config = array();
            }

            foreach ($config as $key => $value) {
                parent::setData($key, $value);
            }
        }
        return $this;
    }

    /**
     * The only way to set the display settings in json format before save
     *
     * @param string $value
     * @return TM_Attributepages_Model_Entity
     */
    public function setDisplaySettings($value)
    {
        $this->_data['display_settings'] = $value;
        return $this;
    }

    public function isMixedMode()
    {
        return $this->getDisplayMode() == self::DISPLAY_MODE_MIXED;
    }

    public function isDescriptionMode()
    {
        return $this->getDisplayMode() == self::DISPLAY_MODE_DESCRIPTION;
    }

    public function isChildrenMode()
    {
        return $this->getDisplayMode() == self::DISPLAY_MODE_CHILDREN;
    }

    public function importOptionData($option, $applyDefaults = true)
    {
        $this->setAttributeId($option->getAttributeId())
            ->setOptionId($option->getOptionId())
            ->setTitle($option->getValue())
            ->setName($option->getValue());

        $identifier = $option->getValue();
        if (function_exists('mb_strtolower')) {
            $identifier = mb_strtolower($identifier, 'UTF-8');
        }
        $this->setIdentifier($identifier);

        if ($applyDefaults) {
            $this->setDisplayMode(self::DISPLAY_MODE_MIXED)
                ->setStores(array(Mage_Core_Model_App::ADMIN_STORE_ID));
        }
        return $this;
    }

    /**
     * Get cache tags associated with object id
     *
     * @return array
     */
    public function getCacheIdTags()
    {
        $tags   = parent::getCacheIdTags();
        $tags[] = Mage_Eav_Model_Entity_Attribute::CACHE_TAG . '_' . $this->getAttributeId();
        return $tags;
    }
}
