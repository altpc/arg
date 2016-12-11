<?php

class TM_EasyBanner_Model_Mysql4_Banner_Statistic_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('easybanner/banner_statistic');
    }

    public function prepareSummary($range, $customStart, $customEnd, $isFilter=0)
    {
        $this->addExpressionAttributeToSelect('displays',
                'SUM({{display_count}})',
                'display_count')
            ->addExpressionAttributeToSelect('clicks',
                'SUM({{clicks_count}})',
                'clicks_count')
            ->addExpressionAttributeToSelect('range',
                $this->_getRangeExpression($range),
                'date')
            ->addFieldToFilter('date', $this->getDateRange($range, $customStart, $customEnd))
            ->getSelect()
            ->group('range');

        return $this;
    }

    protected function _getRangeExpression($range)
    {
        switch ($range) {
            case '7d':
            case '1m':
               $expression = 'DATE_FORMAT(date, \'%Y-%m-%d\')';
               break;
            case '1y':
            case '2y':
            case 'custom':
            default:
                $expression = 'DATE_FORMAT(date, \'%Y-%m\')';
                break;
        }

        return $expression;
    }

    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false)
    {
        $dateEnd = new Zend_Date(Mage::getModel('core/date')->gmtTimestamp());
        $dateStart = clone $dateEnd;

        // go to the end of a day
        $dateEnd->setHour(23);
        $dateEnd->setMinute(59);
        $dateEnd->setSecond(59);

        $dateStart->setHour(0);
        $dateStart->setMinute(0);
        $dateStart->setSecond(0);

        switch ($range) {
            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->subDay(6);
                break;

            case '1m':
                $dateStart->setDay(Mage::getStoreConfig('reports/dashboard/mtd_start'));
                break;

            case 'custom':
                $dateStart = $customStart ? $customStart : $dateEnd;
                $dateEnd   = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
                $startMonthDay = explode(',', Mage::getStoreConfig('reports/dashboard/ytd_start'));
                $startMonth = isset($startMonthDay[0]) ? (int)$startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int)$startMonthDay[1] : 1;
                $dateStart->setMonth($startMonth);
                $dateStart->setDay($startDay);
                if ($range == '2y') {
                    $dateStart->subYear(1);
                }
                break;
        }

        if ($returnObjects) {
            return array($dateStart, $dateEnd);
        } else {
            return array('from'=>$dateStart, 'to'=>$dateEnd, 'datetime'=>true);
        }
    }

    /**
     * Add attribute expression (SUM, COUNT, etc)
     *
     * Example: ('sub_total', 'SUM({{attribute}})', 'revenue')
     * Example: ('sub_total', 'SUM({{revenue}})', 'revenue')
     *
     * For some functions like SUM use groupByAttribute.
     *
     * @param string $alias
     * @param string $expression
     * @param string $attribute
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addExpressionAttributeToSelect($alias, $expression, $attribute)
    {
        if(!is_array($attribute)) {
            $attribute = array($attribute);
        }

        $fullExpression = $expression;
        // Replacing multiple attributes
        foreach($attribute as $attributeItem) {
            $attrField = $attributeItem;
            $fullExpression = str_replace('{{attribute}}', $attrField, $fullExpression);
            $fullExpression = str_replace('{{' . $attributeItem . '}}', $attrField, $fullExpression);
        }

        $this->getSelect()->columns(array($alias=>$fullExpression));

        return $this;
    }





//    /**
//     * Adding banner placeholder names to result collection
//     * Add for each banner placeholder information
//     *
//     * @return TM_EasyBanner_Model_Mysql4_Banner_Collection
//     */
//    public function addPlaceholderNamesToResult()
//    {
//        $bannerIds = $this->getColumnValues('banner_id');
//        $placeholdersToBanner = array();
//
//        if (count($bannerIds) > 0) {
//            $select = $this->getConnection()->select()
//                ->from(array('placeholder' => $this->getTable('easybanner/placeholder')), 'name')
//                ->join(array('banner_placeholder' => $this->getResource()->getTable('easybanner/banner_placeholder')),
//                    'banner_placeholder.placeholder_id = placeholder.placeholder_id',
//                    array('banner_placeholder.banner_id'))
//                ->where('banner_placeholder.banner_id IN (?)', $bannerIds);
//            $result = $this->getConnection()->fetchAll($select);
//
//            foreach ($result as $row) {
//                if (!isset($placeholdersToBanner[$row['banner_id']])) {
//                    $placeholdersToBanner[$row['banner_id']] = array();
//                }
//                $placeholdersToBanner[$row['banner_id']][] = $row['name'];
//            }
//        }
//
//        foreach ($this as $item) {
//            if (isset($placeholdersToBanner[$item->getId()])) {
//                $item->setPlaceholder(implode(", ", $placeholdersToBanner[$item->getId()]));
//            } else {
//                $item->setPlaceholder(null);
//            }
//        }
//
//        return $this;
//    }
//
//    /**
//     * Adding banner statistics to result collection
//     *
//     * @return TM_EasyBanner_Model_Mysql4_Banner_Collection
//     */
//    public function addStatisticsToResult()
//    {
//        $bannerIds = $this->getColumnValues('banner_id');
//        $statToBanner = array();
//
//        if (count($bannerIds) > 0) {
//            $select = $this->getConnection()->select()
//                ->from(array('stat' => $this->getTable('easybanner/banner_statistic')))
//                ->where('stat.banner_id IN (?)', $bannerIds);
//            $result = $this->getConnection()->fetchAll($select);
//
//            foreach ($result as $row) {
//                $statToBanner[$row['banner_id']] = $row;
//            }
//        }
//
//        foreach ($this as $item) {
//            if (isset($statToBanner[$item->getId()])) {
//                $item->setDisplayCount($statToBanner[$item->getId()]['display_count']);
//                $item->setClicksCount($statToBanner[$item->getId()]['clicks_count']);
//            } else {
//                $item->setDisplayCount(null);
//                $item->setClicksCount(null);
//            }
//        }
//
//        return $this;
//    }
//
//    public function joinLeft($table, $cond, $cols='*')
//    {
//        if (!isset($this->_joinedTables[$table])) {
//            $this->getSelect()->joinLeft(array($table => $this->getTable($table)), $cond, $cols);
//            $this->_joinedTables[$table] = true;
//        }
//        return $this;
//    }
//
}