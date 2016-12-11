<?php
/**
 * This is the part of 'Highlight' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 *
 * @author Templates-Master
 * @copyright Templates Master www.templates-master.com
 */

class TM_Highlight_Block_Product_Attribute_Date extends TM_Highlight_Block_Product_List
{
    protected function _beforeToHtml()
    {
        $code_parts = explode(',', $this->getAttributeCode());
        $todayStartOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $todayEndOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $collection = $this->getCollection();

        if (!empty($code_parts[0]) && !empty($code_parts[1])) {
            $collection->addAttributeToFilter("{$code_parts[0]}", array('or' => array(
                    0 => array('date' => true, 'to' => $todayEndOfDayDate),
                    1 => array('is' => new Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToFilter("{$code_parts[1]}", array('or' => array(
                    0 => array('date' => true, 'from' => $todayStartOfDayDate),
                    1 => array('is' => new Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToFilter(
                    array(
                        array('attribute' => "{$code_parts[0]}", 'is' => new Zend_Db_Expr('not null')),
                        array('attribute' => "{$code_parts[1]}", 'is' => new Zend_Db_Expr('not null'))
                    )
                )
                ->addAttributeToSort("{$code_parts[0]}", 'desc');
        } elseif (!empty($code_parts[0])) {
            $collection->addAttributeToFilter("{$code_parts[0]}", array(
                    'date' => true, 'to' => $todayEndOfDayDate
                ))
                ->addAttributeToSort("{$code_parts[0]}", 'desc');
        } elseif (!empty($code_parts[1])) {
            $collection->addAttributeToFilter("{$code_parts[1]}", array(
                    'date' => true, 'from' => $todayStartOfDayDate
                ))
                ->addAttributeToSort("{$code_parts[1]}", 'desc');
        }
        return parent::_beforeToHtml();
    }
}
