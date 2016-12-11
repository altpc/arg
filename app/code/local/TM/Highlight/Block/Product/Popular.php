<?php
/**
 * This is the part of 'Highlight' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 *
 * @author Templates-Master
 * @copyright Templates Master www.templates-master.com
 */

class TM_Highlight_Block_Product_Popular extends TM_Highlight_Block_Product_List
{
    const DEFAULT_DATE_PERIOD = 30;
    const PAGE_TYPE = 'popular';

    protected $_title       = 'Popular Products';
    protected $_priceSuffix = '-popular';
    protected $_className   = 'highlight-popular';

    public function getCacheKeyInfo()
    {
        $keyInfo = parent::getCacheKeyInfo();
        $keyInfo[] = $this->getPeriod();
        return $keyInfo;
    }

    protected function _beforeToHtml()
    {
        $collection = $this->getCollection('highlight/reports_product_collection')
            ->addViewsCount();

        if ($period = $this->getPeriod()) {
            $from = Mage::app()->getLocale()->date()
                ->setTime('00:00:00')
                ->sub($period, Zend_Date::DAY)
                ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            $collection->getSelect()->where('_table_views.logged_at > ?', $from);
        }

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve date period to filter collection.
     *
     * @return int Days
     */
    public function getPeriod()
    {
        $period = $this->getData('period');
        if (null === $period) {
            return self::DEFAULT_DATE_PERIOD;
        }
        return $period;
    }
}
