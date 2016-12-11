<?php
if (!@class_exists("Mobile_Detect")) {
    require_once("Mobile_Detect.php");
}

class TM_ProLabels_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_images = array();

    public function isMobileMode()
    {
        $mobileDetect = new Mobile_Detect;
        return $mobileDetect->isMobile() && !$mobileDetect->isTablet();
    }

    public function getRegistryLabelsData($productId, $mode)
    {
        if ('product' == $mode) {
            $catalogLabels = Mage::registry('tm_product_page_labels');
        } else {
            $catalogLabels = Mage::registry('tm_product_catalog_labels');
        }
        $labelsData = array();
        if (empty($catalogLabels)) { return $labelsData; }
        if (array_key_exists($productId, $catalogLabels['product_rule_ids'])) {
            $productRuleIds = $catalogLabels['product_rule_ids'][$productId];
        } else {
            $productRuleIds = array();
        }
        $catalogSystem = $catalogLabels['catalog_system'];
        foreach($catalogSystem as $rule) {
            $labelsData[] = $rule;
        }
        $catalogRules = $catalogLabels['catalog_rules'];
        foreach($catalogRules as $rule) {
            if (in_array($rule['rules_id'], $productRuleIds)) {
                $labelsData[] = $rule;
            }
        }

        return $labelsData;
    }

    public function getRegistryContentLabels($productId, $mode)
    {
        if ('product' == $mode) {
            $catalogLabels = Mage::registry('tm_product_page_labels');
        } else {
            $catalogLabels = Mage::registry('tm_product_catalog_labels');
        }
        $labelsData = array();
        if (empty($catalogLabels)) { return $labelsData; }
        if (array_key_exists($productId, $catalogLabels['product_rule_ids'])) {
            $productRuleIds = $catalogLabels['product_rule_ids'][$productId];
        } else {
            $productRuleIds = array();
        }
        $catalogSystem = $catalogLabels['catalog_system'];
        foreach($catalogSystem as $rule) {
            if ($rule[$mode.'_position'] == 'content') {
                $labelsData[] = $rule;
            }

        }
        $catalogRules = $catalogLabels['catalog_rules'];
        foreach($catalogRules as $rule) {
            if (in_array($rule['rules_id'], $productRuleIds)) {
                if ($rule[$mode.'_position'] == 'content') {
                    $labelsData[] = $rule;
                }
            }
        }

        return $labelsData;
    }

    public function checkLabelPriority($labelsData, $mode, $product)
    {
        $result = array();
        $priorityResult = array();
        foreach($labelsData as $label) {
            if ($label[$mode . "_position"] == 'content') {
                continue;
            }
            if (array_key_exists('system_id', $label)) {
                if (!$this->checkSystemLabelStore($label['system_id'], $mode)) {
                    continue;
                }
                //validete OnSale
                if ($label['rules_id'] == '1') {
                    if (!$this->_isOnSale($product, $mode, $label)) {
                        continue;
                    }
                }
                //validete Stock
                if (!$label['rules_id'] == '2' && $this->_canShowQuantity($product, $mode, $label) != 'out') {
                    continue;
                }
                //validete New
                if ($label['rules_id'] == '3') {
                    if (!$this->checkNewDate($product)) {
                        continue;
                    }
                }
            } else {
                if (!$this->checkLabelStore($label['rules_id'], $mode)) {
                    continue;
                }
            }
            $result[$label[$mode . '_position']][$label['priority']][] = $label;
        }
        $priorityLabelExist = false;
        foreach ($result as $key => $value) {
            foreach ($value as $priotity => $labelTmpData) {
                foreach ($labelTmpData as $labelTmp) {
                    if ($priorityLabelExist) { continue; }
                    if ($labelTmp['use_priority']) {
                        $priorityLabelExist = true;
                    }
                }
            }
            if ($priorityLabelExist) {
                $minKeyPriority = min(array_keys($result[$key]));
                $priorityResult[] = $result[$key][$minKeyPriority][0];
            } else {
                foreach ($result[$key] as $pr => $noPriorityLabel) {
                    $priorityResult[] = $noPriorityLabel[0];
                }
            }
        }

        return $priorityResult;
    }

    public function getLabel(Mage_Catalog_Model_Product $product, $mode = 'product')
    {
        if (!Mage::getStoreConfig("prolabels/general/enabled")){
            return;
        }

        if ($this->isMobileMode() && Mage::getStoreConfig("prolabels/general/mobile")) {
            return false;
        }

        $labelsData = $this->getRegistryLabelsData($product->getId(), $mode);

        if (Mage::getStoreConfig("prolabels/general/priority")) {
            $labelsData = $this->checkLabelPriority($labelsData, $mode, $product);
        }

        $html     = "";
        $labelImg = "";
        foreach ($labelsData as $data) {
            if (Mage::getStoreConfig("prolabels/general/customer_group")) {
                $labelCustomerGroups = unserialize($data['customer_group']);
                $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                if ($labelCustomerGroups) {
                    if (!in_array($roleId, $labelCustomerGroups)) {
                        continue;
                    }
                }
            }
            if ($data[$mode . "_position"] == 'content') {
                continue;
            }
            if (!array_key_exists('system_id', $data)) {
                if (!$this->checkLabelStore($data['rules_id'], $mode)) {
                    continue;
                }
            } else {
                if (!$this->checkSystemLabelStore($data['system_id'], $mode)) {
                    continue;
                }
            }

            if ($data['rules_id'] == '1') {
                if (!$this->_isOnSale($product, $mode, $data)) {
                    continue;
                }
            }

            if ($data['rules_id'] == '3') {
                if (!$this->checkNewDate($product)) {
                    continue;
                }
            }
            $html .= $this->labelDataToHtml($product, $mode, $data);
        }

        $objectHtml = new Varien_Object();
        $objectHtml->setValue($html);
        Mage::dispatchEvent(
            'prolabels_get_label_html_after',
            array('product' => $product, 'mode' => $mode, 'html' => $objectHtml)
        );
        $html = $objectHtml->getValue();

        return $html;
    }

    public function getMobileLabels($product, $mode)
    {
        if (!Mage::getStoreConfig("prolabels/general/mobile")) {
            return "";
        }

        $labelsData = $this->getRegistryLabelsData($product->getId(), $mode);
        if (Mage::getStoreConfig("prolabels/general/priority")) {
            $labelsData = $this->checkLabelPriority($labelsData, $mode, $product);
        }
        $html = '<li>';
        $labelImg = "";
        foreach ($labelsData as $data) {
            if (Mage::getStoreConfig("prolabels/general/customer_group")) {
                $labelCustomerGroups = unserialize($data['customer_group']);
                $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                if ($labelCustomerGroups) {
                    if (!in_array($roleId, $labelCustomerGroups)) {
                        continue;
                    }
                }
            }
            if (!array_key_exists('system_id', $data)) {
                if (!$this->checkLabelStore($data['rules_id'], $mode)) {
                    continue;
                }
            } else {
                if (!$this->checkSystemLabelStore($data['system_id'], $mode)) {
                    continue;
                }
            }

            if (empty($data[$mode . '_image'])) {
                if (!$data['rules_id'] == '2' && $this->_canShowQuantity($product, $mode, $data) != 'out') {
                    continue;
                }
            }

            if ($data['rules_id'] == '1') {
                if (!$this->_isOnSale($product, $mode, $data)) {
                    continue;
                }
            }

            if ($data['rules_id'] == '3') {
                if (!$this->checkNewDate($product)) {
                    continue;
                }
            }
            if ($mode == 'category') {
                $html .= $this->getCategoryProductUrl($product, $mode, $data);
            }
            if ($data['rules_id'] == '2') {
                $out = $this->_canShowQuantity($product, $mode, $data);
                if (!$out) {
                    continue;
                }
                if ($out == 'out') {
                    if ($data[$mode . "_out_stock"] == '1' && !empty($data[$mode . "_out_stock_image"])) {
                        $labelImg = $data[$mode . "_out_stock_image"];
                        $html     .= '<span style="'
                            . $this->_getTableSize(Mage::getBaseDir('media') . "/prolabel/" . $labelImg);
                    }
                } else {
                    $labelImg = $data[$mode . "_image"];
                    $html .= '<span  style="'
                        . $this->_getTableSize(Mage::getBaseDir('media') . "/prolabel/" . $labelImg);
                }
            } else {
                $labelImg = $data[$mode . "_image"];
                $html .= '<span style="'
                    . $this->_getTableSize(Mage::getBaseDir('media') . "/prolabel/" . $labelImg);
            }


            if (!$this->_hasLabelPosition($data[$mode . "_position"])) {

                $html .= $this->_getTableMargins(
                    $data[$mode . "_position"],
                    Mage::getBaseDir('media') . '/prolabel/' . $labelImg
                );
            }
            $imgPath = Mage::getBaseDir('media') . '/prolabel/' . $labelImg;
            $onClick = '';
            if ($mode == "category") {
                $separator = "'";
                $onClick = 'onclick="return false;"';
            }
            $background = '';
            if ($labelImg) {
                $background = sprintf(
                    'background: url(%s) no-repeat 0 0;',
                    Mage::getBaseUrl('media'). 'prolabel/' . $labelImg
                );
            }
            $html .= $data[$mode . '_position_style'].'"
                    class = "prolabel-mobile">
                <span class="prolabels-image-mobile" ' . $onClick . ' style="cursor:pointer;'
                    . $background
                    . $this->_getTableSize(Mage::getBaseDir('media') . "/prolabel/" . $labelImg).'">' .
                $this->_getProductUrl($product, $imgPath, $mode, $data) .
                '</span>
                </span>';
                if ($mode == 'category') {
                    $html .= '</a>';
                }
        }
        $html .= '</li>';
        return $html;
    }

    public function checkLabelStore($labelId, $mode)
    {
        if ('product' == $mode) {
            $catalogLabels = Mage::registry('tm_product_page_labels');
        } else {
            $catalogLabels = Mage::registry('tm_product_catalog_labels');
        }
        $rulesStoreData = $catalogLabels['catalog_rules_store'];
        $storeId  = Mage::app()->getStore()->getStoreId();
        $storeIds = $rulesStoreData[$labelId];

        if (count($storeIds) > 0) {
            if ($storeIds[0] == 0) {
                return true;
            } elseif (in_array($storeId, $storeIds)) {
                return true;
            }
        }

        return false;
    }

    public function checkSystemLabelStore($labelId, $mode)
    {
        if ('product' == $mode) {
            $catalogLabels = Mage::registry('tm_product_page_labels');
        } else {
            $catalogLabels = Mage::registry('tm_product_catalog_labels');
        }
        $rulesStoreData = $catalogLabels['catalog_system_store'];
        $storeId  = Mage::app()->getStore()->getStoreId();
        // if (!array_key_exists($labelId, $rulesStoreData)) { print_r($labelId);die; }
        $storeIds = $rulesStoreData[$labelId];
        if (count($storeIds) > 0) {
            if ($storeIds[0] == 0) {
                return true;
            } elseif (in_array($storeId, $storeIds)) {
                return true;
            }
        }

        return false;
    }

    public function _hasLabelPosition($conf)
    {
        if (empty($conf)) {
            return false;
        }
        preg_match_all("/(top|left|rigth|bottom)\s*:/", $conf, $matches);

        return (bool)count($matches[0]);
    }

    public function getGroupedProductPriceAmount($product)
    {
        if ($product->getTypeInstance() instanceof Mage_Catalog_Model_Product_Type_Grouped) {
            $simpleProductIds = $product->getTypeInstance()->getAssociatedProductIds();
            $price = 0;
            $finalPrice = 0;
            $sum = 0;
            $maxResult = 0;
            foreach ($simpleProductIds as $simpleProductId) {
                $simpleProduct = Mage::getModel('catalog/product')->load($simpleProductId);
                $price = $simpleProduct->getData('price');
                $calculatedPrice = $this->calculateProductSpecialPrice($simpleProduct);
                if ($price > $calculatedPrice) {
                    $sum = ($price - $calculatedPrice);
                    if ($sum > $maxResult) {
                        $maxResult = $sum;
                    }
                }
            }

            if ($maxResult > 0) {
                return $maxResult;
            }

            return false;
        }
    }

    public function getGroupedProductPricePercent($product, $label, $mode)
    {
        if ($product->getTypeInstance() instanceof Mage_Catalog_Model_Product_Type_Grouped) {
            $simpleProductIds = $product->getTypeInstance()->getAssociatedProductIds();
            $price = 0;
            $finalPrice = 0;
            $maxResult = 0;
            $result = 0;
            foreach ($simpleProductIds as $simpleProductId) {
                $simpleProduct = Mage::getModel('catalog/product')->load($simpleProductId);

                $price = $simpleProduct->getData('price');
                $calculatedPrice = $this->calculateProductSpecialPrice($simpleProduct);
                if ($price > $calculatedPrice) {
                    $result = (100- ($calculatedPrice * 100 / $price)) / $label[$mode . '_round'];
                    if ($result > $maxResult) {
                        $maxResult = $result;
                    }
                }
            }

            return $maxResult;
        }
    }

    /**
     * @param Mage_Catalog_Model_Abstract $object
     * @param string $mode [onsale|recommended|stock|new]
     * @return string
     */
    public function _getText(Mage_Catalog_Model_Abstract $object, $mode, $label)
    {
        if ($label['rules_id'] == '2') {
            if ($this->_canShowQuantity($object, $mode, $label) == 'out') {
                $pattern = $label[$mode . '_out_text'];
            } else {
                $pattern = $label[$mode . '_image_text'];
            }

        } else {
            $pattern = $label[$mode . '_image_text'];
        }

        preg_match_all('/#.+?#/', $pattern, $vars);
        $data = array();
        foreach (current($vars) as $var) {

            if (strpos($var, '#attr:') !== false) {
                $attribute = str_replace('#attr:', '', $var);
                $attribute = str_replace('#', '', $attribute);
                $attribute = $object->getResource()->getAttribute($attribute);
                $data[$var] = $attribute->getFrontend()->getValue($object);

                continue;
            }

            if ($var == '#discount_amount#') {
                if ($object->getData('type_id') === 'bundle') {
                    $price = $object->getPriceModel()->getPrices($object);
                    $fullPrice = ($price[1] * 100) / ($object->getData('special_price'));
                    $data[$var] = $fullPrice - $price[1];
                } elseif ($object->getData('type_id') === 'grouped') {
                    if ($this->getGroupedProductPriceAmount($object)) {
                        $data[$var] = $this->getGroupedProductPriceAmount($object);
                    }
                } else {
                    $calculatedPrice = $this->calculateProductSpecialPrice($object);
                    $data[$var] = $object->getPrice() - $calculatedPrice;
                }

                $data[$var] = $data[$var] / $label[$mode . '_round'];
                $roundMethod = $label[$mode . '_round_method'];
                $data[$var] = $roundMethod($data[$var]);
                $data[$var] = $data[$var] * $label[$mode . '_round'];
                $data[$var] = Mage::helper('core')->currency($data[$var], true);
                $tmp = str_replace('<span class="price">', '', $data[$var]);
                $newTmp = str_replace('</span>', '', $tmp);
                $data[$var] = $newTmp;

                if ($object->getData('type_id') === 'bundle') {
                    $data[$var] = Mage::helper('prolabels')->__('up to ') . $data[$var];
                }
                if ($object->getData('type_id') === 'grouped') {
                    $data[$var] = Mage::helper('prolabels')->__('up to ') . $data[$var];
                }
                continue;
            }
            if ($var == '#special_date#') {
                if ($object->getData('special_to_date')){
                    $currentData = Mage::app()->getLocale()->date();
                    $subtractingDate = Mage::app()->getLocale()->date($object->getData('special_to_date'))->sub($currentData);
                    $toDate = $object->getData('special_to_date');
                    $data[$var] = $this->_subtractingDate($toDate);
                }
                continue;
            }
            if ($var == '#discount_percent#') {
                if ($object->getData('type_id') === 'bundle') {
                    $data[$var] = (100 - $object->getData('special_price')) / $label[$mode . '_round'];
                } elseif ($object->getData('type_id') === 'grouped') {
                    if ($this->getGroupedProductPricePercent($object, $label, $mode)) {
                        $data[$var] = $this->getGroupedProductPricePercent($object, $label, $mode);
                    }

                } else {
                    $calculatedPrice = $this->calculateProductSpecialPrice($object);
                    $data[$var] = (100 - $calculatedPrice * 100 / $object->getData('price')) / $label[$mode . '_round'];
                }

                $roundMethod = $label[$mode . '_round_method'];

                $data[$var] = $roundMethod($data[$var]);
                $data[$var] = $data[$var] * $label[$mode . '_round'];
                if ($object->getData('type_id') === 'grouped') {
                    $data[$var] = Mage::helper('prolabels')->__('up to ') . $data[$var];
                }
                continue;
            }
            if ($var == '#stock_item#') {
                $qty = $this->_canShowQuantity($object, $mode, $label);
                if ($qty && $qty != 'out') {
                    $data[$var] = (int)$qty;
                } else {
                    $data[$var] = '';
                }
                continue;
            }

            if ($var == '#special_price#') {
                $price = Mage::helper('tax')->getPrice($object, $object->getFinalPrice(), true);
                $data[$var] = $price;
                $data[$var] = $data[$var] / $label[$mode . '_round'];
                $roundMethod = $label[$mode . '_round_method'];
                $data[$var] = $roundMethod($data[$var]);
                $data[$var] = $data[$var] * $label[$mode . '_round'];
                $data[$var] = Mage::helper('core')->currency($data[$var], true);
                continue;
            }

            if ($var == '#price#') {
                $price = Mage::helper('tax')->getPrice($object, $object->getPrice(), true);
                $data[$var] = $price;
                $data[$var] = $data[$var] / $label[$mode . '_round'];
                $roundMethod = $label[$mode . '_round_method'];
                $data[$var] = $roundMethod($data[$var]);
                $data[$var] = $data[$var] * $label[$mode . '_round'];
                $data[$var] = Mage::helper('core')->currency($data[$var], true);
                continue;
            }

            if ($var == '#final_price#') {
                $price = Mage::helper('tax')->getPrice($object, $object->getFinalPrice(), true);
                $data[$var] = $price;
                $data[$var] = $data[$var] / $label[$mode . '_round'];
                $roundMethod = $label[$mode . '_round_method'];
                $data[$var] = $roundMethod($data[$var]);
                $data[$var] = $data[$var] * $label[$mode . '_round'];
                $data[$var] = Mage::helper('core')->currency($data[$var], true);
                continue;
            }

            if ($var == '#product_name#') {
                $data[$var] = $object->getName();
                continue;
            }
            if ($var == '#product_sku#') {
                $data[$var] = $object->getSku();
                continue;
            }

            $data[$var] = $object->getData(substr($var, 1, -1));
        }

        return str_replace(array_keys($data), $data, $pattern);
    }

    public function labelDataToHtml($product, $mode, $data)
    {
        // remove some string variables for bundle and grouped products
        if ($product->getTypeInstance() instanceof Mage_Catalog_Model_Product_Type_Grouped ||
            $product->getTypeInstance() instanceof Mage_Bundle_Model_Product_Type) {
            $data[$mode . '_image_text'] = str_replace(
                array('#special_price#','#special_date#', '#final_price#'),
                '',
                $data[$mode . '_image_text']
            );
        }
        // start label rendering
        $labelHtml = '';
        if ($mode == 'category') {
            $labelHtml .= $this->getCategoryProductUrl($product, $mode, $data);
        } else {
            if ($data['product_custom_url']) {
                $labelHtml .= $this->getCategoryProductUrl($product, $mode, $data);
            }
        }

        if ($data['rules_id'] == '2') {
            $out = $this->_canShowQuantity($product, $mode, $data);
            if (!$out) {
                return $labelHtml;
            }
            if ($out == 'out') {
                if ($data[$mode . "_out_stock"] == '1' && !empty($data[$mode . "_out_stock_image"])) {
                    $labelImg = $data[$mode . "_out_stock_image"];
                    $labelHtml     .= '<span style="'
                        . $this->_getTableSize(Mage::getBaseDir('media') . "/prolabel/" . $labelImg);
                } else {
                    $labelImg = $data[$mode . "_out_stock_image"];
                    $labelHtml     .= '<span style="'
                        . $this->_getTableSize(Mage::getBaseDir('media') . "/prolabel/" . $labelImg);
                }
            } else {
                $labelImg = $data[$mode . "_image"];
                $labelHtml .= '<span style="'
                    . $this->_getTableSize(Mage::getBaseDir('media') . "/prolabel/" . $labelImg);
            }
        } else {
            $labelImg = $data[$mode . "_image"];
            $labelHtml .= '<span style="'
                . $this->_getTableSize(Mage::getBaseDir('media') . "/prolabel/" . $labelImg);
        }


        if (!$this->_hasLabelPosition($data[$mode . "_position"])) {

            $labelHtml .= $this->_getTableMargins(
                $data[$mode . "_position"],
                Mage::getBaseDir('media') . '/prolabel/' . $labelImg
            );
        }
        $imgPath = Mage::getBaseDir('media') . '/prolabel/' . $labelImg;
        $onClick = '';
        if ($mode == "category") {
            $separator = "'";
            $onClick = 'onclick="document.location='.$separator . $product->getProductUrl(). $separator .'"';
        }
        $background = '';
        if ($labelImg) {
            $background = sprintf(
                'background: url(%s) no-repeat 0 0;',
                Mage::getBaseUrl('media'). 'prolabel/' . $labelImg
            );
        }
        $labelHtml .= $data[$mode . '_position_style'].'"
                class = "prolabel '
                . $data[$mode . '_position'] . '">
            <span class="prolabels-image" ' . $onClick . ' style="cursor:pointer;'
                . $background
                . $this->_getTableSize(Mage::getBaseDir('media') . "/prolabel/" . $labelImg).'">' .
            $this->_getProductUrl($product, $imgPath, $mode, $data) .
            '</span>
            </span>';
        if ($mode == 'category') {
            $labelHtml .= '</a>';
        } elseif ($data['product_custom_url']) {
            $labelHtml .= '</a>';
        }
        return $labelHtml;
    }

    protected function _subtractingDate($toDate)
    {
     $blocks = array (
             array('year',  (3600 * 24 * 365)),
             array('month', (3600 * 24 * 30)),
             array('week',  (3600 * 24 * 7)),
             array('day',   (3600 * 24)),
             array('hour',  (3600)),
             array('min',   (60)),
             array('sec',   (1))
         );

         $argtime = strtotime($toDate);
         $nowtime = Mage::app()->getLocale()->date()->getTimestamp();

         $diff    = $argtime - $nowtime;

         $res = array ();

         for ($i = 0; $i < count($blocks); $i++) {
             $title = $blocks[$i][0];
             $calc  = $blocks[$i][1];
             $units = floor($diff / $calc);
             if ($units > 0) {
                 $res[$title] = $units;
             }
         }

         if (isset($res['year']) && $res['year'] > 0) {
             if (isset($res['month']) && $res['month'] > 0) {
                 $format      = "%s %s %s %s";
                 $year_label  = $res['year'] > 1 ? 'years' : 'year';
                 $month_label = $res['month'] > 1 ? 'months' : 'month';
                 return sprintf($format, $res['year'], $year_label, ($res['month']-$res['year']*12), $month_label);
             } else {
                 $format     = "%s %s";
                 $year_label = $res['year'] > 1 ? 'years' : 'year';
                 return sprintf($format, $res['year'], $year_label);
             }
         }

         if (isset($res['month']) && $res['month'] > 0) {
             if (isset($res['week']) && $res['week'] > 0) {
                 $format      = "%s %s %s %s";
                 $month_label = $res['month'] > 1 ? 'months' : 'month';
                 $week_label   = $res['week'] > 1 ? 'weeks' : 'week';
                 return sprintf($format, $res['month'], $month_label, ($res['week']-$res['month']*4), $week_label);
             } else {
                $format      = "%s %s";
                 $month_label = $res['month'] > 1 ? 'months' : 'month';
                 return sprintf($format, $res['month'], $month_label);
             }
         }

         if (isset($res['week']) && $res['week'] > 0) {
             if (isset($res['day']) && $res['day'] > 0) {
                 $format      = "%s %s %s %s";
                 $week_label = $res['month'] > 1 ? 'weeks' : 'week';
                 $day_label   = $res['week'] > 1 ? 'days' : 'day';
                 return sprintf($format, $res['week'], $week_label, ($res['day']-$res['week']*7), $day_label);
             } else {
                $format      = "%s %s";
                 $week_label = $res['week'] > 1 ? 'weeks' : 'week';
                 return sprintf($format, $res['week'], $week_label);
             }
         }

         if (isset($res['day']) && $res['day'] > 0) {
             if (isset($res['hour']) && $res['hour'] > 0) {
                 $format      = "%s %s %s %s";
                 $hour_label = $res['hour'] > 1 ? 'hours' : 'hour';
                 $day_label   = $res['day'] > 1 ? 'days' : 'day';
                 return sprintf($format, $res['day'], $day_label, ($res['hour']-$res['day']*24), $hour_label);
             } else {
                $format      = "%s %s";
                 $day_label = $res['day'] > 1 ? 'days' : 'day';
                 return sprintf($format, $res['day'], $day_label);
             }
         }

         if (isset($res['hour']) && $res['hour'] > 0) {
             if (isset($res['min']) && $res['min'] > 0) {
                 $format      = "%s %s %s %s";
                 $hour_label = $res['hour'] > 1 ? 'hours' : 'hour';
                 $min_label   = $res['min'] > 1 ? 'minuts' : 'minut';
                 return sprintf($format, $res['hour'], $hour_label, ($res['min']-$res['hour']*60), $min_label);
             } else {
                $format      = "%s %s";
                 $hour_label = $res['hour'] > 1 ? 'hours' : 'hour';
                 return sprintf($format, $res['hour'], $hour_label);
             }
         }

         if (isset($res['min']) && $res['min'] > 0) {
             if (isset($res['sec']) && $res['sec'] > 0) {
                 $format      = "%s %s %s %s";
                 $min_label = $res['min'] > 1 ? 'minuts' : 'minut';
                 $sec_label   = $res['min'] > 1 ? 'seconds' : 'second';
                 return sprintf($format, $res['min'], $min_label, ($res['sec']-$res['min']*60), $sec_label);
             } else {
                $format      = "%s %s";
                 $hour_label = $res['hour'] > 1 ? 'hours' : 'hour';
                 return sprintf($format, $res['hour'], $hour_label);
             }
         }

         if (isset ($res['sec']) && $res['sec'] > 0) {
             if ($res['sec'] == 1) {
                 return "One second ago";
             } else {
                 return sprintf("%s seconds", $res['sec']);
             }
         }
    }

    public function getCategoryProductUrl($product, $mode, $data)
    {
        if ($data[$mode . '_custom_url']) {
            $url = $data[$mode . '_custom_url'];
            if (array_key_exists('system_label_name', $data)) {
                $productName = $data['system_label_name'];
            } else {
                $productName = $data['label_name'];
            }
        } else {
            $url = $product->getProductUrl();
            $productName = $product->getName();
        }
        $style = "style='width:auto;height:auto;'";
        $text = '';
        return "<a href='{$url}' alt='{$productName}' {$style}>$text";
    }

    protected function _getProductUrl(Mage_Catalog_Model_Product $product, $imgPath, $mode, $data)
    {
        $text = $this->_getText($product, $mode, $data);

        if ('' !== $text) {
            $fontstyle = $data[$mode . '_font_style'];
            $text = "<span class='productlabeltext' style='{$fontstyle}'>{$text}</span>";
        } else {
            $text = '&nbsp;';
        }

        return $text;
    }

    protected function _getImage($path)
    {
        if (!is_file($path)) {
            return null;
        }

        try {
            if (!isset($this->_images[$path])) {
                $this->_images[$path] = new Varien_Image($path);
            }
            return $this->_images[$path];
        } catch (Exception $e) {
            return null;
        }
    }

    public function _isOnSale($product, $mode, $data)
    {
        if ($product->getTypeInstance() instanceof Mage_Catalog_Model_Product_Type_Grouped) {
            $simpleProductIds = $product->getTypeInstance()->getAssociatedProductIds();
            $groupedSale = false;

            foreach ($simpleProductIds as $simpleProductId) {
                $simpleProduct = Mage::getModel('catalog/product')->load($simpleProductId);
                $price = $simpleProduct->getData('price');
                $calculatedPrice = $this->calculateProductSpecialPrice($simpleProduct);
                if ($price > $calculatedPrice) {
                    return true;
                }
            }

            return false;
        }

        if ($product->getTypeInstance() instanceof Mage_Bundle_Model_Product_Type) {
            if ($product->getData('special_price')) {
                $bundlePricePersent = (100 - $product->getData('special_price')) / $data[$mode . '_round'];
                if ((int)$bundlePricePersent > 0) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            $calculatedPrice = $this->calculateProductSpecialPrice($product);
            if ($product->getPrice() > $calculatedPrice) {
                return true;
            }
        }

        return false;
    }

    public function calculateProductSpecialPrice($product)
    {
        $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $role = Mage::getSingleton('customer/group')->load($roleId);

        $specialPrice = Mage_Catalog_Model_Product_Type_Price::calculatePrice(
            $product->getData('price'),
            $product->getData('special_price'),
            $product->getData('special_from_date'),
            $product->getData('special_to_date'),
            false,
            Mage::app()->getStore(),
            $role,
            $product->getId()
        );

        return $specialPrice;
    }

    public function checkNewDate($product)
    {
        $from = $product->getData('news_from_date');
        $to   = $product->getData('news_to_date');

        if (empty($from) && empty($to)) {
            return false;
        }

        return Mage::app()->getLocale()->isStoreDateInInterval(
            Mage::app()->getStore(),
            $product->getData('news_from_date'),
            $product->getData('news_to_date')
        );
    }

    public function _canShowQuantity($product, $mode, $data)
    {
        if (!$product->getData('stock_item')->is_in_stock) {
            return 'out';
        }
        $quantity = 0;
        if ($product->isConfigurable()) {
            $model = new Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Type_Configurable();
            $simpleProductIds = $model->getChildrenIds($product->getId());
            foreach (current($simpleProductIds) as $productId) {
                $simpleProduct = Mage::getModel('catalog/product')->load($productId);
                $productQty = $simpleProduct->getData('stock_item')->qty;
                $quantity = $quantity + (int)$productQty;
            }
        } elseif ($product->getTypeInstance() instanceof Mage_Bundle_Model_Product_Type) {
            if ($mode == 'category') {
                $childIds = $product->getTypeInstance()->getChildrenIds($product->getId());
                $simpleQty = 0;
                $sum = array();
                foreach ($childIds as $childId) {
                    foreach ($childId as $simpleId) {
                        $simpleProduct = Mage::getModel('catalog/product')->load($simpleId);
                        $simpleQty += $simpleProduct->getData('stock_item')->qty;
                    }
                    $sum[] = $simpleQty;
                    $simpleQty = 0;
                }
                $quantity = min($sum);
            } else {
                $groupSum = array();
                foreach ($product->getTypeInstance()->getOptions() as $option) {
                    if (!$option->getData('required')) {
                        continue;
                    }
                    foreach ($option->getSelections() as $simpleProduct) {

                        $sum += $simpleProduct->getData('stock_item')->qty;
                    }
                    $groupSum[] = $sum;
                    $sum = 0;
                }

                $quantity = min($groupSum);
            }
        } else {
            if ($mode == 'category') {
                $quantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
            } else {
                $quantity = $product->getData('stock_item')->qty;
            }

        }
        if (array_key_exists('system_id', $data)) {
            if ($quantity > 0 && $quantity < (int)$data[$mode . '_min_stock']) {
                return $quantity;
            }
        } else {
            if ($quantity > 0) {
                return $quantity;
            }
        }

        return false;
    }

    protected function _isRecommended($product, $mode)
    {
        if (!Mage::getStoreConfig("prolabels/{$mode}recommended/display")) {
            return false;
        }

        if ((int)$product->getData(Mage::getStoreConfig("prolabels/{$mode}recommended/attributeid")) == 1){
            return true;
        }

        return false;
    }

    protected function _getTableMargins($position, $imagePath)
    {
        if (null === ($image = $this->_getImage($imagePath))) {
            return '';
        }
        switch ($position) {
            case 'top-center':
                $width = - $image->getOriginalWidth() / 2;
                return "margin-left:{$width}px;";
            case 'middle-left':
                $height = - $image->getOriginalHeight() / 2;
                return "margin-top:{$height}px;";
            case 'middle-right':
                $height = - $image->getOriginalHeight() / 2;
                return "margin-top:{$height}px;";
            case 'bottom-center':
                $width = - $image->getOriginalWidth() / 2;
                return "margin-right:{$width}px;";
            case 'middle-center':
                $width = - $image->getOriginalWidth() / 2;
                $height = - $image->getOriginalHeight() / 2;
                return "margin-right:{$width}px; margin-top:{$height}px;";
            default:
                return '';
        }
    }

    protected function _getTableSize($imagePath)
    {
        if (null === ($image = $this->_getImage($imagePath))) {
            return '';
        }
        return "width:{$image->getOriginalWidth()}px; height:{$image->getOriginalHeight()}px;";
    }

    protected function _loadProduct(Mage_Catalog_Model_Product $product)
    {
        $product->load($product->getId());
    }
}
