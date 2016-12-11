<?php
/**
 * This is the part of 'Highlight' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 *
 * @author Templates-Master
 * @copyright Templates Master www.templates-master.com
 */

class TM_Highlight_Block_Product_Bestseller extends TM_Highlight_Block_Product_List
{
    const DEFAULT_DATE_PERIOD = 90;
    const PAGE_TYPE = 'bestsellers';

    protected $_title       = 'Bestsellers';
    protected $_priceSuffix = '-bestseller';
    protected $_className   = 'highlight-bestseller';

    public function getCacheKeyInfo()
    {
        $keyInfo = parent::getCacheKeyInfo();
        $keyInfo[] = $this->getPeriod();
        return $keyInfo;
    }

    protected function _beforeToHtml()
    {
        $collection = $this->getCollection('highlight/reports_product_collection')
            ->addOrderedQty()
            ->addAttributeToSelect('ordered_qty')
            ->setOrder('ordered_qty', 'desc');

        if ($period = $this->getPeriod()) {
            $from = Mage::app()->getLocale()->date()
                ->setTime('00:00:00')
                ->sub($period, Zend_Date::DAY)
                ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            $collection->getSelect()->where('order.created_at > ?', $from);
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
