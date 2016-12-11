<?php
/**
 * This is the part of 'Highlight' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 *
 * @author Templates-Master
 * @copyright Templates Master www.templates-master.com
 */

class TM_Highlight_Block_Review_New extends TM_Highlight_Block_Product_List
{
    protected $_title       = 'Recent Reviews';
    protected $_priceSuffix = '-review';
    protected $_className   = 'highlight-review';

    protected function _beforeToHtml()
    {
        $collection = $this->getCollection('highlight/review_product_collection')
            ->addStatusFilter(1)
            ->setPageSize($this->getProductsCount());

        $collection->getSelect()->order('rt.created_at DESC');

        // return parent::_beforeToHtml(); do not call for parent to prevent event dispatching
    }

    public function getReviewUrl($id)
    {
        return Mage::getUrl('review/product/view', array('id' => $id));
    }

    public function getShortReviewText($review, $length, $offset = 0.2)
    {
        if (strlen($review) > $length) {
            $first = strrpos(substr($review, 0, $length), ' ');
            $last  = strpos(substr($review, $length), ' ');
            if (false === $last) {
                // short review
            } elseif ($last < $length * $offset) {
                $review = substr($review, 0, $length + $last) . '...';
            } else {
                $review = substr($review, 0, $length - $first) . '...';
            }
        }
        return trim($review, " ,-(:;\r\n");
    }
}
