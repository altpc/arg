<?php

class TM_EasyBanner_Model_Mysql4_Handle_Collection extends Varien_Data_Collection
{
    private $_handles = array();

    private $_filterIncrement = 0;

    /**
     * Load data
     *
     * @return TM_EasyBanner_Model_Mysql4_Handle_Collection
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->_handles = $this->getHandles();
        $this->_filterAndSort();
        $this->_totalRecords = count($this->_handles);
        $this->_setIsLoaded();

        $from = ($this->getCurPage() - 1) * $this->getPageSize();
        $to = $from + $this->getPageSize() - 1;
        $cnt = 0;

        foreach ($this->_handles as $row) {
            $cnt++;
            if ($cnt < $from || $cnt > $to) {
                continue;
            }
            $item = new Varien_Object();
            $item->addData($row);
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * With specified collected items:
     *  - apply filters
     *  - sort
     *
     * @return void
     */
    private function _filterAndSort()
    {
        if (!empty($this->_filters)) {
            foreach ($this->_handles as $key => $row) {
                foreach ($this->_filters as $filter) {
                    if (!$this->$filter['callback']($filter['field'], $filter['value'], $row)) {
                        unset($this->_handles[$key]);
                    }
                }
            }
        }

        if (!empty($this->_orders)) {
            foreach ($this->_orders as $key => $direction) {
                if (self::SORT_ORDER_ASC === strtoupper($direction)) {
                    asort($this->_handles);
                } else {
                    arsort($this->_handles);
                }
                break;
            }
        }
    }

    /**
     * Set select order
     * Currently supports only sorting by one column
     *
     * @param   string $field
     * @param   string $direction
     * @return  TM_EasyBanner_Model_Mysql4_Handle_Collection
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        $this->_orders = array($field => $direction);
        return $this;
    }

    /**
     * Fancy field filter
     *
     * @param string $field
     * @param mixed $cond
     * @see Varien_Data_Collection_Db::addFieldToFilter()
     * @return TM_EasyBanner_Model_Mysql4_Handle_Collection
     */
    public function addFieldToFilter($field, $cond)
    {
        if (isset($cond['like'])) {
            return $this->addCallbackFilter($field, $cond['like'], 'filterCallbackLike');
        }
        return $this;
    }

    /**
     * Set a custom filter with callback
     * The callback must take 3 params:
     *     string $field       - field key,
     *     mixed  $filterValue - value to filter by,
     *     array  $row         - a generated row (before generaring varien objects)
     *
     * @param string $field
     * @param mixed $value
     * @param string $callback
     * @return TM_EasyBanner_Model_Mysql4_Handle_Collection
     */
    public function addCallbackFilter($field, $value, $callback)
    {
        $this->_filters[$this->_filterIncrement] = array(
            'field'       => $field,
            'value'       => $value,
            'callback'    => $callback
        );
        $this->_filterIncrement++;
        return $this;
    }

    /**
     * Callback method for 'like' fancy filter
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     */
    public function filterCallbackLike($field, $filterValue, $row)
    {
        //preg_quote($filterValue, '/'); - removed to allow to use regexp at user input
        $filterValueRegex = str_replace('%', '(.*?)', $filterValue);
        return (bool)preg_match("/^{$filterValueRegex}$/i", $row[$field]);
    }

    /**
     * Load handles from xml files
     *
     * @see Mage_Core_Model_Layout_Upadate::fetchFileLayoutUpdates()
     * @todo fetch handles from all active frontend designs
     * @return array
     */
    public function getHandles()
    {
        $elementClass =  Mage::getConfig()->getModelClassName('core/layout_element');
        $design = Mage::getSingleton('core/design_package');

        $oldTheme = $design->getTheme('layout');
        $design->setArea('frontend');
        $design->setTheme('layout', Mage::getStoreConfig('design/theme/layout'));

        $area = $design->getArea();
        $storeId = Mage::app()->getStore()->getId();
        $cacheKey = 'LAYOUT_'.$area.'_STORE'.$storeId.'_'.$design->getPackageName().'_'.$design->getTheme('layout');
        $cacheTags = array('layout');

        if (Mage::app()->useCache('layout') && ($layoutStr = Mage::app()->loadCache($cacheKey))) {
            $this->_packageLayout = simplexml_load_string($layoutStr, $elementClass);
        }

        $updatesRoot = Mage::app()->getConfig()->getNode($area.'/layout/updates');
        $updateFiles = array();
        foreach ($updatesRoot->children() as $updateNode) {
            if ($updateNode->file) {
                $module = $updateNode->getAttribute('module');
                if ($module && Mage::getStoreConfigFlag('advanced/modules_disable_output/' . $module)) {
                    continue;
                }
                $updateFiles[] = (string)$updateNode->file;
            }
        }

        $layoutStr = '';
        foreach ($updateFiles as $file) {
            $filename = $design->getLayoutFilename($file);
            if (!is_readable($filename)) {
                continue;
            }
            $fileStr = file_get_contents($filename);
            $fileXml = simplexml_load_string($fileStr, $elementClass);
            if (!$fileXml instanceof SimpleXMLElement) {
                continue;
            }
            $layoutStr .= $fileXml->innerXml();
        }

        $layoutXml = simplexml_load_string('<layouts>'.$layoutStr.'</layouts>', $elementClass);

        $handles = array();
        foreach ($layoutXml as $handle => $values) {
            $handles[$handle] = array(
                'id'    => $handle,
                'name'  => $handle
            );
        }

        $design->setArea('adminhtml');
        $design->setTheme('layout', $oldTheme);

        return $handles;
    }
}