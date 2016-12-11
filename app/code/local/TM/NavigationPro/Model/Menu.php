<?php

/**
 * @method TM_NavigationPro_Model_Resource_Menu_Collection getCollection()
 */
class TM_NavigationPro_Model_Menu extends Mage_Core_Model_Abstract
{
    const COLUMNS_MODE_MENU   = 'menu';
    const COLUMNS_MODE_PARENT = 'parent';
    const COLUMNS_MODE_CUSTOM = 'custom';

    const COLUMNS_MODE_DEFAULT = 'menu';

    const CACHE_TAG = 'navigationpro_menu';

    /**
     * Model cache tag for clear cache in after save and after delete
     */
    protected $_cacheTag = self::CACHE_TAG;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('navigationpro/menu');
    }

    /**
     * Overriden to convert the json saved configuration to array style
     *
     * @param string $key
     * @param mixed $value
     * @return TM_NavigationPro_Model_Menu
     */
    public function setData($key, $value = null)
    {
        parent::setData($key, $value);

        if ((is_array($key) && array_key_exists('configuration', $key))
            || 'configuration' === $key) {

            if (is_array($key)) {
                $value = $key['configuration'];
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
     * The only way to set the configuration in json format before save
     *
     * @param string $value
     * @return TM_NavigationPro_Model_Menu
     */
    public function setConfiguration($value)
    {
        $this->_data['configuration'] = $value;
        return $this;
    }

    /**
     * @return TM_NavigationPro_Model_Resource_Column_Collection
     */
    public function getDropdownColumnsCollection()
    {
        /**
         * @var TM_NavigationPro_Model_Resource_Column_Collection
         */
        $collection = Mage::getResourceModel('navigationpro/column_collection');
        $collection->addFieldToFilter('menu_id', $this->getId())
            ->setOrder('sort_order', 'ASC');

        if (!$this->getId()) {
            // add subcategory column for new menu
            $column = $collection->getNewEmptyItem();
            $column->addData($column->getDefaultData());
            $collection->addItem($column);
        }

        return $collection;
    }

    /**
     * @return TM_NavigationPro_Model_Resource_Sibling_Collection
     */
    public function getSiblingsCollection()
    {
        /**
         * @var TM_NavigationPro_Model_Resource_Sibling_Collection
         */
        $collection = Mage::getResourceModel('navigationpro/sibling_collection');
        $collection->addFieldToFilter('menu_id', $this->getId())
            ->setOrder('sort_order', 'ASC');

        return $collection;
    }

    public function getContent()
    {
        $content = $this->getData('content');
        if (is_null($content)) {
            $content = $this->getResource()->getContent(
                $this->getId(), $this->getStoreId()
            );
            $this->setContent($content);
        }
        return $content;
    }

    public function isRoot()
    {
        return $this->getRootMenuId() === null;
    }

    public function loadAllData()
    {
        if ($this->getName()) {
            $this->getResource()->loadTreeByName($this);
        }

        return $this;
    }

    public function getIsCustomColumnsMode()
    {
        return $this->getColumnsMode() == self::COLUMNS_MODE_CUSTOM;
    }

    public function getIsParentColumnsMode()
    {
        return $this->getColumnsMode() == self::COLUMNS_MODE_PARENT;
    }

    public function getIsMenuColumnsMode()
    {
        return $this->getColumnsMode() == self::COLUMNS_MODE_MENU;
    }
}
