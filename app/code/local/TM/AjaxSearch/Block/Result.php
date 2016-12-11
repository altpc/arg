<?php

class TM_AjaxSearch_Block_Result extends TM_AjaxSearch_Block_CatalogSearch_Result
{
    const ENABLE_SUGGEST_CONFIG     = 'tm_ajaxsearch/general/enablesuggest';
    const ENABLE_IMAGE_CONFIG       = 'tm_ajaxsearch/general/enableimage';
    const IMAGE_HEIGHT_CONFIG       = 'tm_ajaxsearch/general/imageheight';
    const IMAGE_WIDTH_CONFIG        = 'tm_ajaxsearch/general/imagewidth';

    const ENABLE_DESCRIPTION_CONFIG = 'tm_ajaxsearch/general/enabledescription';
    const DESCRIPTION_LENGTH_CONFIG = 'tm_ajaxsearch/general/descriptionchars';

    const RESULT_SIZE_CONFIG        = 'tm_ajaxsearch/general/productstoshow';

    const ENABLE_TAGS    = 'tm_ajaxsearch/general/enabletags';
    const ENABLE_CATALOG = 'tm_ajaxsearch/general/enablecatalog';
    const ENABLE_CMS     = 'tm_ajaxsearch/general/enablecms';

    const SORT_ORDER_SUGGEST = 'tm_ajaxsearch/general/suggest_order';
    const SORT_ORDER_PRODUCT = 'tm_ajaxsearch/general/product_order';
    const SORT_ORDER_CATALOG = 'tm_ajaxsearch/general/catalog_order';
    const SORT_ORDER_CMS     = 'tm_ajaxsearch/general/cms_order';
    const SORT_ORDER_TAGS    = 'tm_ajaxsearch/general/tags_order';

    /**
     *
     * @var string
     */
    protected $_rawQuery = null;

    protected $_suggestions = array();

    private function _trim($text, $len, $delim = '...')
    {
        if (function_exists("mb_strstr")) {
            $strlen = 'mb_strlen';
            $strpos = 'mb_strpos';
            $substr = 'mb_substr';
        } else {
            $strlen = 'strlen';
            $strpos = 'strpos';
            $substr = 'substr';
        }

        if ($strlen($text) > $len) {
            $whitespaceposition = $strpos($text, " ", $len) - 1;
            if ($whitespaceposition > 0) {
                $text = $substr($text, 0, ($whitespaceposition + 1));
            }
            return $text . $delim;
        }
        return $text;
    }

    /**
     *
     * @return string
     */
    public function getRawQuery()
    {
        $param = Mage_CatalogSearch_Helper_Data::QUERY_VAR_NAME;
        if (null === $this->_rawQuery) {
            $this->_rawQuery = $this->getRequest()->getParam($param, '');
        }

        return $this->_rawQuery;
    }

    protected function _getSearchedQueryCollection()
    {
        $collection = Mage::getResourceModel('catalogsearch/query_collection')
            ->setStoreId($this->getStoreId())
            ->setQueryFilter($this->getQueryText());

//        Zend_Debug::dump((string)$collection->getSelect());
        return $collection;
    }

    protected function _prepareSearchedQuery()
    {
        $_html = '';
        $collection = $this->_getSearchedQueryCollection();

        $query = $this->getQueryText();
        $data = array();
        foreach ($collection as $item) {
            $_data = array(
                'title' => $item->getQueryText(),
                'num_of_results' => $item->getNumResults()
            );

            if ($item->getQueryText() == $query) {
                array_unshift($data, $_data);
            } else {
                $data[] = $_data;
            }
        }

        $helper = Mage::helper('catalogsearch/data');
        foreach ($data as $item) {
            $_url = $helper->getResultUrl($item['title']);
            $_html .= " <a href='{$_url}'>{$item['title']}({$item['num_of_results']})</a>" ;
        }
        if (count($data) > 0) {
            $sortOrder = Mage::getStoreConfig(self::SORT_ORDER_SUGGEST);
            $this->_suggestions[$sortOrder][] = array('html' => '<p class="headercategorysearch">'
                . $this->__("Suggestions") . '<br/>' . $_html . '</p>');
        }
    }

    ///////////////////////////////////////////////////////////////////////////

    protected function _prepareQueryPopularity($size = 0)
    {
        if ($size <= 0) {
            return false;
        }
        $queryText = $this->getQueryText();
        $model = Mage::getModel('catalogsearch/query')->loadByQuery($queryText);

        $model->setQueryText($queryText);
        if ($model->getId()) {
            $model->setPopularity($model->getPopularity() + 1);
        } else {
            $model->setNumResults($size);
            $model->setPopularity(1);
        }

        $model->save();

        return intval($model->getPopularity()) > 1;
    }

    public function getAvailableOrders()
    {
        $category = $this->getCategory();
        if (!$category instanceof Mage_Catalog_Model_Category) {
            $category = Mage::getSingleton('catalog/layer')->getCurrentCategory();
        }

        /* @var $category Mage_Catalog_Model_Category */
        $availableOrders = $category->getAvailableSortByOptions();
        unset($availableOrders['position']);
        $availableOrders = array_merge(array(
            'relevance' => $this->__('Relevance')
        ), $availableOrders);

        return $availableOrders;
    }

    public function getToolbarBlock()
    {
        $toolbar = $this->getLayout()
            ->createBlock('catalog/product_list_toolbar', microtime());

        $toolbar
            ->setData('_current_grid_order', false)
            ->setData('_current_grid_direction', false)
            ->setAvailableOrders($this->getAvailableOrders())
            ->setDefaultDirection('desc')
            ->setDefaultOrder('relevance')
        ;

        return $toolbar;
    }

    protected function _prepareProducts()
    {
        $isEnabledImage = (bool) Mage::getStoreConfig(self::ENABLE_IMAGE_CONFIG);
        $imageHeight    = (int) Mage::getStoreConfig(self::IMAGE_HEIGHT_CONFIG);
        $imageWidth     = (int) Mage::getStoreConfig(self::IMAGE_WIDTH_CONFIG);

        $isEnabledDescription = (bool) Mage::getStoreConfig(self::ENABLE_DESCRIPTION_CONFIG);
        $lengthDescription    = (int) Mage::getStoreConfig(self::DESCRIPTION_LENGTH_CONFIG);

        $collection = $this->_getAlternativeProductCollection();

        // $this->_prepareQueryPopularity($collection->getSize());

        $toolbar = $this->getToolbarBlock();

        $toolbar->setCollection($collection);

        $size = (int) Mage::getStoreConfig(self::RESULT_SIZE_CONFIG);
        $collection->setPageSize($size);
        // $collection->getSelect()->limit($size);
        $sortOrder = Mage::getStoreConfig(self::SORT_ORDER_PRODUCT);

        if (0 < count($collection)) {
            $this->_suggestions[$sortOrder][] = array('html' =>
                '<p class="headercategorysearch">' . $this->__("Products") . '</p>'
            );
        }
        if ($isEnabledImage) {
            $helper = Mage::helper('catalog/image');
        }

        foreach ($collection as $_row) {

            $_product = Mage::getModel('catalog/product')
                ->setStoreId($this->getStoreId())
                ->load($_row->getId());

            $_image = $_srcset = $_description = '';

            if ($isEnabledImage) {
                $_image  = (string) $helper->init($_product, 'thumbnail')->resize($imageWidth, $imageHeight);
                $_srcset = (string) $helper->init($_product, 'thumbnail')->resize($imageWidth * 2, $imageHeight * 2);
                $_srcset .= ' 2x';
            }
            if ($isEnabledDescription) {
                $_description = strip_tags($this->_trim(
                    $_product->getShortDescription(),
                    $lengthDescription
                ));
            }

            // $store = Mage::app()->getStore();
            // $path = Mage::getResourceModel('core/url_rewrite')
            //     ->getRequestPathByIdPath('product/' . $_product->getId(), $store);
            // // $url = $store->getBaseUrl($store::URL_TYPE_WEB) . $path;
            // $url = rtrim(Mage::getUrl($path, array('_store' => $store->getStoreId())), '/');
            $url = $_product->getProductUrl();
            $this->_suggestions[$sortOrder][] = array(
                'name'        => $_product->getName(),
                'url'         => $url,
                'image'       => $_image,
                'srcset'      => $_srcset,
                'description' => $_description
            );
        }

    }

    /////////////////////////////////////////////////////////////////////////

    protected function _getTagCollection()
    {
        $tags = explode(' ', $this->getQueryText());

        $atributes = Mage::getSingleton('catalog/config')->getProductAttributes();
        /* @var $collection TM_AjaxSearch_Model_Mysql4_Tag_Collection */
        $collection = Mage::getResourceModel('ajaxsearch/tag_collection')
            ->addAttributeToSelect($atributes) //add default attributes to collection, You can use '*' for all
            ->addTagsFilter($tags)
//            ->addTagFilter($tag->getId()) //filter by the tag object
            ->addStoreFilter($this->getStoreId()) // filter by current store view since a product may be related to a tag only in some stores
            ->addMinimalPrice() //add the prices to the collection
            ->addUrlRewrite() //add url keys if needed
            ->setActiveFilter(); //some relations between tags and products may be disabled
        Mage::getSingleton('catalog/product_status')
            ->addSaleableFilterToCollection($collection); //select only active products
        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInSiteFilterToCollection($collection); //get only visible products

        return $collection;
    }

    protected function _prepareTags()
    {
        $isEnabledImage = (bool) Mage::getStoreConfig(self::ENABLE_IMAGE_CONFIG);
        $imageHeight    = (int) Mage::getStoreConfig(self::IMAGE_HEIGHT_CONFIG);
        $imageWidth     = (int) Mage::getStoreConfig(self::IMAGE_WIDTH_CONFIG);

        $isEnabledDescription = (bool) Mage::getStoreConfig(self::ENABLE_DESCRIPTION_CONFIG);
        $lengthDescription    = (int) Mage::getStoreConfig(self::DESCRIPTION_LENGTH_CONFIG);

        $sortOrder = Mage::getStoreConfig(self::SORT_ORDER_TAGS);
        $collection = $this->_getTagCollection();
        if (0 < count($collection)) {
            $this->_suggestions[$sortOrder][] = array('html' =>
                '<p class="headercategorysearch">' . $this->__("Products tags suggested")
                . '</p><span class="hr"></span>'
            );
        }

        if ($isEnabledImage) {
            $helper = Mage::helper('catalog/image');
        }
        foreach ($collection as $_row) {
            $_product = Mage::getModel('catalog/product')->load($_row->getId());
            $_image = $_srcset = $_description = '';
            if ($isEnabledImage) {
                $helper = $helper->init($_product, 'thumbnail');
                $_image = $helper->resize($imageWidth, $imageHeight)
                    ->__toString();
                $_srcset = $helper->resize($imageWidth * 2, $imageHeight * 2)
                    ->__toString() . ' 2x';
            }
            if ($isEnabledDescription) {
                $_description = strip_tags($this->_trim(
                    $_product->getShortDescription(),
                    $lengthDescription
                ));
            }

            $this->_suggestions[$sortOrder][] = array(
                'name'        => $_product->getName(),
                'url'         => $_product->getProductUrl(),
                'image'       => $_image,
                'srcset'      => $_srcset,
                'description' => $_description
            );
        }

    }
    ////////////////////////////////////////////////////////////////////////

    protected function _getCategoryCollection()
    {
        return Mage::getResourceModel('ajaxsearch/category_collection')
            ->setStoreIdFilter($this->getStoreId())
            ->setQueryFilter($this->getQueryText());
    }

    protected function _prepareCategory()
    {
        $collection = $this->_getCategoryCollection();
        $sortOrder = Mage::getStoreConfig(self::SORT_ORDER_CATALOG);
        if (0 < count($collection)) {
            $this->_suggestions[$sortOrder][] = array('html' => '<p class="headercategorysearch">'
                . $this->__("Categories")
                . '</p><span class="hr"></span>'
            );
        }
        foreach ($collection as $_row) {
            $category = Mage::getModel("catalog/category")->load($_row['entity_id']);
            $this->_suggestions[$sortOrder][] = array(
                'name' => $_row['name'],
                'url'  => $category->getUrl()
            );
        }
    }

    ////////////////////////////////////////////////////////////////////////

    protected function _getCmsCollection()
    {
        return Mage::getResourceModel('ajaxsearch/cms_collection')
            ->addStoreFilter($this->getStoreId())
            ->setQueryFilter($this->getQueryText());
    }

    protected function _prepareCms()
    {
        $collection = $this->_getCmsCollection();
        $sortOrder = Mage::getStoreConfig(self::SORT_ORDER_CMS);
        if (count($collection)) {
            $this->_suggestions[$sortOrder][] = array('html' => '<p class="headercategorysearch">'
                . $this->__("Info Pages")
                . '</p><span class="hr"></span>'
            );
        }
        $helper = Mage::helper('cms/page');
        $storeId = $this->getStoreId();
        foreach ($collection as $_page) {

            $page = Mage::getModel('cms/page')
                ->setStoreId($storeId)
                ->load($_page->getId());

            if (!$page || !$page->getId()) {
                continue;
            }

            $this->_suggestions[$sortOrder][] = array(
                'name' => $page->getTitle(),
                'url'  => $helper->getPageUrl($page->getId())
            );
        }
    }
    ////////////////////////////////////////////////////////////////////////
    public function getSuggestions()
    {
        $this->_suggestions = array();

        if (Mage::getStoreConfig(self::ENABLE_SUGGEST_CONFIG)) {
            $this->_prepareSearchedQuery();
        }
        $this->_prepareProducts();

        if (Mage::getStoreConfig(self::ENABLE_TAGS)) {
            $this->_prepareTags();
        }
        if (Mage::getStoreConfig(self::ENABLE_CATALOG)) {
            $this->_prepareCategory();
        }
        if (Mage::getStoreConfig(self::ENABLE_CMS)) {
            $this->_prepareCms();
        }

        if (!function_exists('array_flatten_once')) {
            function array_flatten_once($array)
            {
                $return = array();
                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        $return = array_merge($return, $value);
                    } else {
                        $return[$key] = $value;
                    }
                }

                return $return;
            }
        }
        ksort($this->_suggestions);
        $this->_suggestions = array_flatten_once($this->_suggestions);

        $helper = Mage::helper('core');
        $query = $this->getQueryText();
        if (method_exists($helper, 'removeTags')) {
            @$_query = $helper->removeTags($query);
        } else {
            $_query = strip_tags($query);
        }
        $searchURL = Mage::helper('catalogsearch/data')->getResultUrl($query);

        $notFound = count($this->_suggestions) == 0;
        if (!$notFound) {
            array_unshift($this->_suggestions, array('html' =>
                '<p class="headerajaxsearchwindow">' .
                    Mage::getStoreConfig('tm_ajaxsearch/general/headertext') .
                    " <a href='{$searchURL}'>{$_query}</a>" .
                '</p>'
            ));
        }

        $nothingNotFoundText = Mage::getStoreConfig('tm_ajaxsearch/general/notfoundtext');
        if ($notFound && !empty($nothingNotFoundText)) {
            $this->_suggestions[] = array('html' =>
                '<p class="headerajaxsearchwindow">' .
                    $nothingNotFoundText .
                '</p>'
            );
        }

        $this->_suggestions[] = array('html' =>
            '<p class="headerajaxsearchwindow">' .
                Mage::getStoreConfig('tm_ajaxsearch/general/footertext') .
            " <a href='{$searchURL}'>{$_query}</a>" .
            '</p>'
        );

        $categoryId = '';
        $category = $this->getCategory();
        if ($category) {
            $categoryId = $category->getId();
        }
        return array(
            'q'           => $this->getRawQuery(),
            'category'    => $categoryId,
            'suggestions' => $this->_suggestions
        );
    }
}
