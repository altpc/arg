<?php

class TM_ArgentoMall_Upgrade_1_0_0 extends TM_Core_Model_Module_Upgrade
{
    /**
     * Create new, featured and recommended products, if they are not exists
     */
    public function up()
    {
        $todayStartOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $todayEndOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $visibility = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();
        foreach ($this->getStoreIds() as $storeId) {
            if ($storeId) {
                $store = Mage::app()->getStore($storeId);
            } else {
                $store = Mage::app()->getDefaultStoreView();
            }
            if (!$store) {
                continue;
            }
            $storeId = $store->getId();
            $rootCategory = Mage::getModel('catalog/category')->load($store->getRootCategoryId());

            if (!$rootCategory) {
                continue;
            }
            /**
             * @var Mage_Catalog_Model_Resource_Product_Collection
             */
            $visibleProducts = Mage::getResourceModel('highlight/catalog_product_collection');
            $visibleProducts
                ->setStoreId($storeId)
                ->setVisibility($visibility)
                ->addStoreFilter($storeId)
                ->addCategoryFilter($rootCategory)
                ->addAttributeToSort('entity_id', 'desc')
                ->setPageSize(10)
                ->setCurPage(1);

            if (!$visibleProducts->count()) {
                continue;
            }

            foreach ($visibleProducts as $product) {
                $product->load($product->getId());
            }

            /**
             * @var Mage_Catalog_Model_Resource_Product_Collection
             */
            $newProducts = Mage::getResourceModel('highlight/catalog_product_collection');
            $newProducts
                ->setStoreId($storeId)
                ->setVisibility($visibility)
                ->addStoreFilter($storeId)
                ->addCategoryFilter($rootCategory)
                ->addAttributeToFilter('news_from_date', array('or'=> array(
                    0 => array('date' => true, 'to' => $todayEndOfDayDate),
                    1 => array('is' => new Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToFilter('news_to_date', array('or'=> array(
                    0 => array('date' => true, 'from' => $todayStartOfDayDate),
                    1 => array('is' => new Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToFilter(
                    array(
                        array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
                        array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
                    )
                )
                ->addAttributeToSort('news_from_date', 'desc')
                ->setPageSize(1)
                ->setCurPage(1);

            if (!$newProducts->count()) {
                foreach ($visibleProducts as $product) {
                    $product->setStoreId($storeId);
                    $product->setNewsFromDate($todayStartOfDayDate);
                    $product->save();
                }
            }

            foreach (array('featured', 'recommended') as $attributeCode) {
                $collection = Mage::getResourceModel('highlight/catalog_product_collection');
                $collection
                    ->setStoreId($storeId)
                    ->setVisibility($visibility)
                    ->addStoreFilter($storeId)
                    ->addCategoryFilter($rootCategory)
                    ->setPageSize(1)
                    ->setCurPage(1);

                if (!$collection->getAttribute($attributeCode)) { // Mage 1.6.0.0 fix
                    continue;
                }
                $collection->addAttributeToFilter("{$attributeCode}", array('Yes' => true));

                if (!$collection->count()) {
                    foreach ($visibleProducts as $product) {
                        // attribute should be saved in global scope
                        if (!in_array(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, $this->getStoreIds())) {
                            $product->addAttributeUpdate($attributeCode, 0, Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
                        }

                        $product->setStoreId($storeId);
                        $product->setData($attributeCode, 1);
                        $product->save();
                    }
                }
            }
        }
    }

    public function getOperations()
    {
        return array(
            'configuration' => $this->_getConfiguration(),
            'cmsblock'      => $this->_getCmsBlocks(),
            'cmspage'       => $this->_getCmsPages(),
            'easyslide'     => $this->_getSlider(),
            'easybanner'    => $this->_getEasybanner(),
            // 'prolabels'     => $this->_getProlabels(),
            'navigationpro' => $this->_getNavigationpro(),
            'productAttribute' => $this->_getProductAttribute()
        );
    }

    private function _getConfiguration()
    {
        return array( // @todo split into each module, if custom config is not needed
            'design' => array(
                'package/name' => 'argento',
                'theme' => array(
                    'template' => 'mall',
                    'skin'     => 'mall',
                    'layout'   => 'mall',
                    'after_default' => Mage::helper('argento')->isEnterprise() ?
                        'enterprise/default' : ''
                )
            ),

            'easyslide/general' => array(
                'load' => 1
            ),

            'ajax_pro' => array(
                'general' => array(
                    'enabled' => 1,
                    'useLoginFormBlock' => 1
                ),
                'effect' => array(
                    'opacity' => 1,
                    'enabled_overlay' => 1,
                    'overlay_opacity' => 0.5
                ),
                'checkoutCart' => array(
                    'enabled'     => 1,
                    'enabledForm' => 1,
                    'messageHandle' => 'tm_ajaxpro_checkout_cart_add_suggestpage'
                ),
                'catalogProductCompare' => array(
                    'enabled'     => 1,
                    'enabledForm' => 1
                ),
                'wishlistIndex' => array(
                    'enabled'     => 1,
                    'enabledForm' => 1
                ),
                'catalogCategoryView' => array(
                    'enabled' => 1,
                    'type' => 'button'
                )
            ),

            'easycatalogimg' => array(
                'general/enabled' => 1,
                'category' => array(
                    'enabled_for_default' => 0,
                    'enabled_for_anchor'  => 0
                )
            ),

            'facebooklb' => array(
                'category_products' => array(
                    'enabled'   => 0,
                    'send'      => 0,
                    'layout'    => 'button_count',
                    'showfaces' => 0,
                    'width'     => 350,
                    'color'     => 'light'
                ),
                'productlike' => array(
                    'enabled'   => 1,
                    'send'      => 1,
                    'layout'    => 'button_count',
                    'showfaces' => 0,
                    'width'     => 350,
                    'color'     => 'light'
                )
            ),

            'tm_ajaxsearch/general' => array(
                'enabled'              => 1,
                'show_category_filter' => 1,
                'width'                => '251',
                'attributes'           => 'name,sku'
            ),

            'tm_easytabs/general' => array(
                'enabled' => 1
            ),

            'soldtogether' => array(
                'general' => array(
                    'enabled' => 1,
                    'random'  => 1
                ),
                'order' => array(
                    'enabled'           => 1,
                    'addtocartcheckbox' => 0,
                    'amazonestyle'      => 1
                ),
                'customer/enabled' => 1
            ),

            'richsnippets/general' => array(
                'enabled' => 1
            ),

            'askit/general/enabled'       => 1,
            'prolabels/general' => array(
                'enabled' => 1,
                'mobile'  => 0
            ),
            'lightboxpro' => array(
                'general/enabled' => 1,
                'size' => array(
                    'main'      => '512x512',
                    'thumbnail' => '112x112',
                    'maxWindow' => '800x600'
                )
            ),
            'navigationpro/top/enabled'   => 1,
            'suggestpage/general/show_after_addtocart' => 1
        );
    }

    /**
     * header_links
     * scroll_up
     * footer_links
     */
    private function _getCmsBlocks()
    {
        return array(
            'header_links' => array(
                'title'      => 'header_links',
                'identifier' => 'header_links',
                'status'     => 1,
                'content'    => <<<HTML
<ul class="header-links">
    <li class="first"><a href="{{store url="contacts"}}">support</a></li>
    <li><a href="{{store url="faq"}}">faq</a></li>
    <li class="last"><a href="{{store url="knowledgebase"}}">knowledge base</a></li>
</ul>
HTML
            ),
            'scroll_up' => array(
                'title'      => 'scroll_up',
                'identifier' => 'scroll_up',
                'status'     => 1,
                'content'    => <<<HTML
<p id="scroll-up" class="hidden-tablet hidden-phone">
    <a href="#">Back to top</a>
</p>

<script type="text/javascript">
document.observe('dom:loaded', function() {
    $('scroll-up').hide();
    Event.observe(window, 'scroll', function() {
        if (document.viewport.getScrollOffsets()[1] > 180) {
            $('scroll-up').show();
        } else {
            $('scroll-up').hide();
        }
    });

    $('scroll-up').down('a').observe('click', function(e) {
        e.stop();
        Effect.ScrollTo(document.body, { duration:'0.2' });
        return false;
    });
});
</script>
HTML
            ),
            'footer_links' => array(
                'title' => 'footer_links',
                'identifier' => 'footer_links',
                'status' => 1,
                'content' => <<<HTML
<div class="box footer-links-cms">
    <div class="head"><span>Informational</span></div>
    <ul class="col2-set">
        <li class="col-1">
            <ul>
                <li><a href="{{store direct_url="about"}}">About Us</a></li>
                <li><a href="{{store direct_url="our-company"}}">Our company</a></li>
                <li><a href="{{store direct_url="press"}}">Press</a></li>
                <li><a href="{{store direct_url="contacts"}}">Contact Us</a></li>
                <li><a href="{{store direct_url="location"}}">Store location</a></li>
            </ul>
        </li>
        <li class="last col-2">
            <ul>
                <li><a href="{{store direct_url="privacy"}}">Privacy policy</a></li>
                <li><a href="{{store direct_url="delivery"}}">Delivery information</a></li>
                <li><a href="{{store direct_url="returns"}}">Returns policy</a></li>
            </ul>
        </li>
    </ul>
</div>
HTML
            )
        );
    }

    /**
     * home
     */
    private function _getCmsPages()
    {
        return array(
            'home' => array(
                'title'             => 'home',
                'identifier'        => 'home',
                'root_template'     => 'one_column',
                'meta_keywords'     => '',
                'meta_description'  => '',
                'content_heading'   => '',
                'is_active'         => 1,
                'content'           => <<<HTML
<div class="callout-home-top col2-set">
    <div class="col-1">
        {{widget type="easyslide/insert" slider_id="argento_mall"}}
    </div>
    <div class="col-2">
        {{block type="newsletter/subscribe" name="homepage.newsletter" template="newsletter/subscribe.phtml"}}
        {{widget type="easybanner/widget_placeholder" placeholder_name="argento-mall-home-top"}}
    </div>
</div>

<div class="col-home-set">
    <div class="col-side sidebar">
        {{block type="navigationpro/navigation" template="tm/navigationpro/sidebar.phtml" name_in_layout="navpro-homepage-left" menu_name="argento_mall_left" enabled="1"}}
    </div>
    <div class="col-main">
        <div class="col3-set">
            <div class="col-1 col-mobile-50">
                {{widget type="highlight/product_special" name="homepage.special" title="Deal of the week" class_name="block block-alt" products_count="1" column_count="1" template="tm/highlight/product/grid.phtml"}}
            </div>
            <div class="col-2 col-mobile-50">
                {{widget type="highlight/product_attribute_yesno" attribute_code="recommended" class_name="editor-choice  block block-alt" name="homepage.editor_choice" title="Editor's choice" products_count="1" column_count="1" template="tm/highlight/product/grid.phtml"}}
            </div>
            <div class="col-3">
                <div class="block block-alt video-of-day">
                  <div class="block-title"><span>Video of the day</span></div>
                  <div class="block-content">
                    <div class="video-container">
                    <object><param name="movie" value="http://www.youtube.com/v/6BQfCoqbubE"><param name="allowFullScreen" value="true"><param name="allowScriptAccess" value="always"><param wmode="transparent"><embed src="http://www.youtube.com/v/6BQfCoqbubE" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" width="250" height="201" wmode="transparent"></object>
                    </div>
                    <p><small>Amazing Canon Rebel XSi commercial that I saw on TV the other day.</small></p>
                  </div>
                </div>
            </div>
        </div>
        {{widget type="highlight/product_featured" name="homepage.featured" class_name="block block-featured-homepage" title="Featured products" products_count="6" column_count="3" template="tm/highlight/product/grid-wide.phtml"}}
        {{widget type="highlight/product_new" name="homepage.new" title="New products" products_count="30" template="tm/highlight/product/slider.phtml"}}
    </div>
</div>
HTML
,
                'layout_update_xml' => <<<HTML
<reference name="head">
    <action method="addItem"><type>skin_js</type><name>js/tabBuilder.js</name></action>
    <action method="addItem"><type>skin_js</type><name>js/slider.js</name></action>
</reference>
HTML
            )
        );
    }

    private function _getSlider()
    {
        return array(
            array(
                'identifier'    => 'argento_mall',
                'title'         => 'Argento Mall',
                'width'         => 700,
                'height'        => 270,
                'duration'      => 0.5,
                'frequency'     => 4.0,
                'autoglide'     => 1,
                'controls_type' => 'number',
                'status'        => 1,
                'slides'        => array(
                    array(
                        'url'   => 'argento/mall/argento_mall_slider1.jpg',
                        'image' => 'Sony VAIO Laptop',
                        'description' => 'Sony VAIO Laptop',
                        'desc_pos' => 4,
                        'background' => 2
                    ),
                    array(
                        'url'   => 'argento/mall/argento_mall_slider2.jpg',
                        'image' => 'Dell Studio 17',
                        'description' => 'Dell Studio 17',
                        'desc_pos' => 4,
                        'background' => 2
                    ),
                    array(
                        'url'   => 'argento/mall/argento_mall_slider3.jpg',
                        'image' => 'HP HDX 16t',
                        'description' => 'HP HDX 16t',
                        'desc_pos' => 4,
                        'background' => 2
                    ),
                    array(
                        'url'   => 'argento/mall/argento_mall_slider4.jpg',
                        'image' => 'Nikon 5000',
                        'description' => 'Nikon 5000',
                        'desc_pos' => 4,
                        'background' => 2
                    ),
                    array(
                        'url'   => 'argento/mall/argento_mall_slider5.jpg',
                        'image' => 'Apple Macbook',
                        'description' => 'Apple Macbook',
                        'desc_pos' => 4,
                        'background' => 2
                    )
                )
            )
        );
    }

    private function _getEasybanner()
    {
        return array(
            array(
                'name'         => 'argento-mall-home-top',
                'parent_block' => 'non-existing-block',
                'limit'        => 1,
                'banners'      => array(
                    array(
                        'identifier' => 'argento-mall-home-top1',
                        'title'      => 'Free Shipping',
                        'url'        => 'free-shipping',
                        'image'      => 'argento/mall/argento_mall_callout_home_top1.gif',
                        'width'          => 210,
                        'height'         => 130,
                        'resize_image'   => 1,
                        'retina_support' => 0
                    )
                )
            )
        );
    }

    private function _getProlabels()
    {
        return array(
            array(
                'type'                    => 'new',
                'label_status'            => 1,
                'system_label_name'       => 'New',
                'l_status'                => 1,
                'product_image_text'     => 'New',
                'product_position'        => 'top-left',
                'product_position_style'  => 'top: 7px; left: 7px;',
                'product_font_style'      => 'color: #fff; text-shadow: 0 1px 0 rgba(0,0,0,0.3); width: 45px; height: 45px;background:#00a7e5; border-radius:50%;',
                'product_image'           => '',
                'product_round_method'    => 'round',
                'category_image_text'     => 'New',
                'category_position'       => 'top-left',
                'category_position_style' => 'top: 5px; left: 5px;',
                'category_font_style'     => 'color: #fff; text-shadow: 0 1px 0 rgba(0,0,0,0.3); width: 45px; height: 45px;background:#00a7e5; border-radius:50%;',
                'category_image'          => '',
                'category_round_method'   => 'round'
            )
        );
    }

    private function _getNavigationpro()
    {
        return array(
            'argento_mall_left' => array(
                'name' => 'argento_mall_left',
                'levels_per_dropdown' => 3,
                'columns'  => array(
                    array(
                        'width' => 185
                    )
                )
            ),
        );
    }

    private function _getProductAttribute()
    {
        return array(
            array(
                'attribute_code' => 'featured',
                'frontend_label' => array('Featured'),
                'default_value'  => 0
            ),
            array(
                'attribute_code' => 'recommended',
                'frontend_label' => array('Recommended'),
                'default_value'  => 0
            )
        );
    }
}
