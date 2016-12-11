<?php

class TM_EasyBanner_Block_Placeholder extends Mage_Core_Block_Template
{
    protected $_filters = array();
    protected $_banners = array();

    public function getTemplate()
    {
        $template = parent::getTemplate();
        if ($template) {
            $this->setData('template', $template);
        } else if (!$this->hasData('template')) {
            $this->setData('template', 'tm/easybanner/placeholder.phtml');
        }
        return $this->_getData('template');
    }

    /**
     * Adds banner object to array.
     * Before add, banner is checking with filters
     *
     * @param string $id
     * @param array $filters
     * @return TM_EasyBanner_Block_Placeholder Provides fluent interface
     */
    public function addBanner()
    {
        $args = func_get_args();
        if (count($args)) {
            $this->addFilter(array_shift($args), $args);
        }
        return $this;
    }

    public function addFilter($key, $value)
    {
        $this->_filters[$key] = $value;
    }

    public function getFilters()
    {
        return $this->_filters;
    }

    /**
     * Retrieve filtered and sorted banners
     *
     * @return array
     */
    public function getBanners()
    {
        if (($placeholderId = $this->getPlaceholderId()) && count($this->_filters)) { // from db
            $collection = Mage::getModel('easybanner/banner')->getCollection()
                ->addStatistics()
                ->addFieldToFilter('main_table.identifier', array(
                    'in' => array_keys($this->_filters)
                ));

            $placeholder = Mage::getModel('easybanner/placeholder')
                ->load($placeholderId);
        } else if (($name = $this->getPlaceholderName()) // inline call for placeholder
            || ($placeholderId = $this->getPlaceholderId())) { // EE_PageCache

            $field = $name ? 'name' : 'placeholder_id';
            $value = $name ? $name : $placeholderId;
            $collection = Mage::getModel('easybanner/banner')
                ->getCollectionByPlaceholderName($value);
            $placeholder = Mage::getModel('easybanner/placeholder')
                ->load($value, $field);
        } else {
            return array(); // invalid arguments supplied
        }

        $bannerCss = $this->getBannerCssClass();
        foreach ($collection->getItems() as $banner) {
            if (!$banner->isVisible()) {
                continue;
            }

            if ($bannerCss) {
                $banner->setAdditionalCssClass($bannerCss);
            }
            $this->_banners[$banner->getIdentifier()] = $banner->getData();
        }

        if (!count($this->_banners)) {
            return array();
        }

        uasort($this->_banners, array($this, '_sortBanners'));

        $count = count($this->_banners);
        if ($placeholder->getIsRandomSortMode()) {
            $offset = rand(0, $count - 1);
        } else {
            // sort banners according to placeholder offset iterator
            $offset = $placeholder->getBannerOffset();
            $offset = ($count > $offset ? $offset : 0);
        }
        $head = array_splice($this->_banners, $offset);
        $this->_banners = $head + $this->_banners;

        $increment = $placeholder->getLimit();
        if ($count < $increment) {
            $increment = $count;
        }
        $placeholder->setDoNotUpdateLayout(true)
            ->setBannerOffset($offset + $increment)
            ->save();

        // limit by placeholder config
        array_splice($this->_banners, $placeholder->getLimit());

        $this->setPlaceholder($placeholder);
        $this->setBannerCollection($collection);
        return $this->_banners;
    }

    private function _sortBanners($a, $b)
    {
        if ($a['sort_order'] == $b['sort_order']) {
            return 0;
        }
        return $a['sort_order'] < $b['sort_order'] ? -1 : 1;
    }

    public function getClassName()
    {
        $name = Mage::helper('easybanner')->getPlaceholderClassName(
            $this->getPlaceholder()->getName()
        );
        $mode = $this->getPlaceholder()->getMode();
        if ($this->getPlaceholder()->isPopupMode()) {
            $mode .= ' placeholder-popup';
        }
        $class = 'placeholder-' . $mode . ' ' . $name;
        if ($this->getAdditionalCssClass()) {
            $class .= ' ' . $this->getAdditionalCssClass();
        }
        return $class;
    }

    /**
     * EE_PageCache integration
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $items = array(
            'placeholder_id'   => $this->getPlaceholderId(),
            'placeholder_name' => $this->getPlaceholderName()
        );
        $items = parent::getCacheKeyInfo() + $items;
        return $items;
    }
}
