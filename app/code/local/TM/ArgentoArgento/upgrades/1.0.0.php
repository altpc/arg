<?php

class TM_ArgentoArgento_Upgrade_1_0_0 extends TM_Core_Model_Module_Upgrade
{
    /**
     * Create new and coming_soon products, if they are not exists
     *
     * @todo move to parent class and provide ability to skip this step
     *       or assign products to add the attribute to
     */
    public function up()
    {
        // add new and coming_soon products if no one found
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

            // coming soon products
            $comingSoonProducts = Mage::getResourceModel('highlight/catalog_product_collection');
            $comingSoonProducts
                ->setStoreId($storeId)
                ->setVisibility($visibility)
                ->addStoreFilter($storeId)
                ->addCategoryFilter($rootCategory)
                ->setPageSize(1)
                ->setCurPage(1);

            $attributeCode = 'coming_soon';
            if (!$comingSoonProducts->getAttribute($attributeCode)) { // Mage 1.6.0.0 fix
                continue;
            }
            $comingSoonProducts->addAttributeToFilter("{$attributeCode}", array('Yes' => true));

            if (!$comingSoonProducts->count()) {
                foreach ($visibleProducts as $product) {
                    // attribute should be saved in global scope
                    if (!in_array(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, $this->getStoreIds())) {
                        $product->addAttributeUpdate('coming_soon', 0, Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
                    }

                    $product->setStoreId($storeId);
                    $product->setComingSoon(1);
                    $product->save();
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
            'productAttribute' => $this->_getProductAttribute()
        );
    }

    private function _getConfiguration()
    {
        return array( // @todo split into each module, if custom config is not needed
            'design' => array(
                'package/name' => 'argento',
                'theme' => array(
                    'template' => 'argento',
                    'skin'     => 'argento',
                    'layout'   => 'argento'
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

            'tm_ajaxsearch/general' => array(
                'enabled'              => 1,
                'show_category_filter' => 1,
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
     * header_callout
     * footer_links
     * footer_contacts
     * footer_social
     */
    private function _getCmsBlocks()
    {
        return array(
            'header_links' => array(
                'title'      => 'header_links',
                'identifier' => 'header_links',
                'status'     => 1,
                'content'    => <<<HTML
<ul class="links header-links">
  <li class="first"><a href="{{store url=""}}">support</a></li>
  <li><a href="{{store url=""}}">faq</a></li>
  <li class="last"><a href="{{store url=""}}">knowledge base</a></li>
</ul>
HTML
            ),
            'header_callout' => array(
                'title' => 'header_callout',
                'identifier' => 'header_callout',
                'status' => 1,
                'content' => <<<HTML
<img width="160" height="60" class="header-callout hidden-phone hidden-tablet" src="{{skin url="images/media/callout_customer_support.gif"}}" alt="Toll-Free Customer Support 24/7" style="margin: 5px 0 0 50px; max-width: 180px; max-height: 80px;"/>
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
            'footer_toolbar' => array(
                'title' => 'footer_toolbar',
                'identifier' => 'footer_toolbar',
                'status' => 1,
                'content' => <<<HTML
<div class="footer-toolbar-container">
    <div class="footer-toolbar">
        {{block type="newsletter/subscribe" template="newsletter/subscribe.phtml"}}
        <div class="footer-social">
            <span class="label">Join our community</span>
            <ul class="icons">
                <li class="facebook"><a href="facebook.com">Facebook</a></li>
                <li class="twitter"><a href="twitter.com">Twitter</a></li>
                <li class="youtube"><a href="youtube.com">YouTube</a></li>
                <li class="rss"><a href="rss.com">Rss</a></li>
            </ul>
        </div>
    </div>
</div>
HTML
            ),
            'footer_cms' => array(
                'title' => 'footer_cms',
                'identifier' => 'footer_cms',
                'status' => 1,
                'content' => <<<HTML
<div class="footer-cms-container">
    <div class="footer-cms">
        <div class="row">
            <div class="col-md-9">
                <ul class="footer-links row">
                    <li class="col-md-3 col-xs-6">
                        <div class="h6">About us</div>
                        <ul>
                            <li><a href="{{store direct_url="about"}}">About Us</a></li>
                            <li><a href="{{store direct_url="our-company"}}">Our company</a></li>
                            <li><a href="{{store direct_url="carriers"}}">Carriers</a></li>
                            <li><a href="{{store direct_url="shipping"}}">Shipping</a></li>
                        </ul>
                    </li>
                    <li class="col-md-3 col-xs-6">
                        <div class="h6">Customer center</div>
                        <ul>
                            <li><a href="{{store direct_url="customer/account"}}">My Account</a></li>
                            <li><a href="{{store direct_url="sales/order/history"}}">Order Status</a></li>
                            <li><a href="{{store direct_url="wishlist"}}">Wishlist</a></li>
                            <li><a href="{{store direct_url="exchanges"}}">Returns and Exchanges</a></li>
                        </ul>
                    </li>
                    <li class="col-md-3 col-xs-6">
                        <div class="h6">Info</div>
                        <ul>
                            <li><a href="{{store direct_url="privacy"}}">Privacy policy</a></li>
                            <li><a href="{{store direct_url="delivery"}}">Delivery information</a></li>
                            <li><a href="{{store direct_url="returns"}}">Returns policy</a></li>
                        </ul>
                    </li>
                    <li class="col-md-3 col-xs-6">
                        <div class="h6">Contacts</div>
                        <ul>
                            <li><a href="{{store direct_url="contacts"}}">Contact Us</a></li>
                            <li><a href="{{store direct_url="location"}}">Store location</a></li>
                        </ul>
                    </li>
                </ul>
            </div>

            <div class="col-md-3 footer-contacts">
                <div class="h6">Visit Argento  Store</div>
                <address>
                221B Baker Street<br/>
                West Windsor, NJ  08550<br/>
                <strong>1.800.555.1903</strong><br/>
                </address>
                <a href="{{store url="map"}}">get directions</a><br/>
                <img width="200" height="60" style="margin-top: 10px;" src="{{skin url="images/security_sign.gif"}}" srcset="{{skin url="images/security_sign@2x.gif"}} 2x" alt="Security Seal"/>
            </div>
        </div>
    </div>
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
<div class="row">
    <div class="col-md-8">
        <div class="easyslider-wrapper">
            {{widget type="easyslide/insert" slider_id="argento_default"}}
        </div>
    </div>
    <div class="col-md-4">
        {{widget type="easybanner/widget_placeholder" placeholder_name="argento-home-top" banner_css_class="col-xs-4 col-md-12" additional_css_class="row"}}
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        {{widget type="easycatalogimg/widget_list" category_count="4" column_count="4" show_image="1" resize_image="1" image_width="180" image_height="180" subcategory_count="5" template="tm/easycatalogimg/list.phtml"}}
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        {{widget type="easybanner/widget_placeholder" placeholder_name="argento-home-content"}}
    </div>
</div>
<div class="row promo-home-content">
    <div class="col-md-8">
        <div class="tab-container">
            {{widget type="highlight/product_new" title="New arrivals" products_count="6" column_count="3" template="tm/highlight/product/grid.phtml" class_name="highlight-new" show_page_link="1" page_title="See all new products &raquo;"}}
            {{widget type="highlight/product_special" title="Sale" products_count="6" column_count="3" template="tm/highlight/product/grid.phtml" class_name="highlight-special" show_page_link="1" page_title="See all on sale products &raquo;"}}
            {{widget type="highlight/product_attribute_yesno" attribute_code="coming_soon" title="Coming soon" products_count="6" column_count="3" template="tm/highlight/product/grid.phtml" class_name="highlight-attrbiute-coming_soon"}}
        </div>
        <script type="text/javascript">
            new TabBuilder();
        </script>
    </div>
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12 col-sm-6">
                {{widget type="highlight/product_bestseller" title="Bestsellers" products_count="3" column_count="3" template="tm/highlight/product/sidebar/list-link.phtml" class_name="highlight-bestsellers" show_page_link="1" page_title="See all bestsellers &raquo;"}}
            </div>
            <div class="col-md-12 col-sm-6">
                {{widget type="highlight/product_popular" title="Popular Products" products_count="3" column_count="3" template="tm/highlight/product/sidebar/list-link.phtml" class_name="highlight-popular" show_page_link="1" page_title="See all popular products &raquo;"}}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="block brands-home">
            <div class="block-title">
                <span>Featured Brands</span>
            </div>
            <div class="block-content">
                <a href="#" id="left">Left</a>
                <a href="#" id="right">Right</a>
                <div id="slider-brands-container" class="slider-wrapper">
                    <ul class="list-brands slider-brands" id="slider-brands">
                        <li><a href="#"><img src="{{skin url="images/catalog/brands/sony.jpg"}}" alt="" width="128" height="73"/></a></li>
                        <li><a href="#"><img src="{{skin url="images/catalog/brands/apple.jpg"}}" alt="" width="70" height="73"/></a></li>
                        <li><a href="#"><img src="{{skin url="images/catalog/brands/garmin.jpg"}}" alt="" width="154" height="74"/></a></li>
                        <li><a href="#"><img src="{{skin url="images/catalog/brands/htc.jpg"}}" alt="" width="124" height="74"/></a></li>
                        <li><a href="#"><img src="{{skin url="images/catalog/brands/intel.jpg"}}" alt="" width="103" height="74"/></a></li>
                        <li><a href="#"><img src="{{skin url="images/catalog/brands/motorola.jpg"}}" alt="" width="204" height="76"/></a></li>
                        <li><a href="#"><img src="{{skin url="images/catalog/brands/sony.jpg"}}" alt="" width="128" height="73"/></a></li>
                        <li><a href="#"><img src="{{skin url="images/catalog/brands/apple.jpg"}}" alt="" width="70" height="73"/></a></li>
                        <li><a href="#"><img src="{{skin url="images/catalog/brands/garmin.jpg"}}" alt="" width="154" height="74"/></a></li>
                        <li><a href="#"><img src="{{skin url="images/catalog/brands/htc.jpg"}}" alt="" width="124" height="74"/></a></li>
                        <li><a href="#"><img src="{{skin url="images/catalog/brands/intel.jpg"}}" alt="" width="103" height="74"/></a></li>
                        <li><a href="#"><img src="{{skin url="images/catalog/brands/motorola.jpg"}}" alt="" width="204" height="76"/></a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    new Slider("slider-brands-container", "left", "right", {shift: 'auto'});
                </script>
            </div>
        </div>
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
                'identifier'    => 'argento_default',
                'title'         => 'Argento Default',
                'width'         => 635,
                'height'        => 325,
                'duration'      => 0.5,
                'frequency'     => 4.0,
                'autoglide'     => 1,
                'controls_type' => 'number',
                'status'        => 1,
                'slides'        => array(
                    array(
                        'url'   => 'argento/argento/argento_default_slider1.png',
                        'image' => 'Slide 1',
                        'description' => 'Sony VAIO T Series Laptop'
                    ),
                    array(
                        'url'   => 'argento/argento/argento_default_slider2.png',
                        'image' => 'Slide 2',
                        'description' => 'Sony VAIO T Series Laptop'
                    ),
                    array(
                        'url'   => 'argento/argento/argento_default_slider3.png',
                        'image' => 'Slide 3',
                        'description' => 'Sony VAIO T Series Laptop'
                    ),
                    array(
                        'url'   => 'argento/argento/argento_default_slider4.png',
                        'image' => 'Slide 4',
                        'description' => 'Sony VAIO T Series Laptop'
                    )
                )
            )
        );
    }

    private function _getEasybanner()
    {
        return array(
            array(
                'name'         => 'argento-home-top',
                'parent_block' => 'non-existing-block',
                'limit'        => 3,
                'banners'      => array(
                    array(
                        'identifier' => 'argento-home-top1',
                        'title'      => 'Ups home delivery',
                        'url'        => 'ups-delivery',
                        'image'      => 'argento/argento/argento_argento_ups_delivery.png',
                        'width'          => 311,
                        'height'         => 110,
                        'resize_image'   => 1,
                        'retina_support' => 0
                    ),
                    array(
                        'identifier' => 'argento-home-top2',
                        'title'      => 'Galaxy S3',
                        'url'        => 'galaxy-s3',
                        'image'      => 'argento/argento/argento_argento_galaxy_s3.png',
                        'width'          => 311,
                        'height'         => 110,
                        'resize_image'   => 1,
                        'retina_support' => 0
                    ),
                    array(
                        'identifier' => 'argento-home-top3',
                        'title'      => 'Roku 2 XS',
                        'url'        => 'roku-2-xs',
                        'image'      => 'argento/argento/argento_argento_roku2_xs.png',
                        'width'          => 311,
                        'height'         => 110,
                        'resize_image'   => 1,
                        'retina_support' => 0
                    )
                )
            ),
            array(
                'name'         => 'argento-home-content',
                'parent_block' => 'non-existing-block',
                'banners'      => array(
                    array(
                        'identifier' => 'argento-home-content1',
                        'title'      => 'HP Envy 17',
                        'url'        => 'hp-envy-17',
                        'image'      => 'argento/argento/argento_argento_hp_envy_17.png',
                        'width'          => 960,
                        'height'         => 109,
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

    private function _getProductAttribute()
    {
        return array(
            array(
                'attribute_code' => 'featured',
                'frontend_label' => array('Featured'),
                'default_value'  => 0
            ),
            array(
                'attribute_code' => 'coming_soon',
                'frontend_label' => array('Coming Soon'),
                'default_value'  => 0
            )
        );
    }
}
