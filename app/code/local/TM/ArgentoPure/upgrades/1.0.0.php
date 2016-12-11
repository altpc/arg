<?php

class TM_ArgentoPure_Upgrade_1_0_0 extends TM_Core_Model_Module_Upgrade
{
    /**
     * Create new and recommended products, if they are not exists
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
                ->setPageSize(12)
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

            $recommendedProducts = Mage::getResourceModel('highlight/catalog_product_collection');
            $recommendedProducts
                ->setStoreId($storeId)
                ->setVisibility($visibility)
                ->addStoreFilter($storeId)
                ->addCategoryFilter($rootCategory)
                ->setPageSize(1)
                ->setCurPage(1);

            $attributeCode = 'recommended';
            if (!$recommendedProducts->getAttribute($attributeCode)) { // Mage 1.6.0.0 fix
                continue;
            }
            $recommendedProducts->addAttributeToFilter("{$attributeCode}", array('Yes' => true));

            if (!$recommendedProducts->count()) {
                foreach ($visibleProducts as $product) {
                    // attribute should be saved in global scope
                    if (!in_array(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, $this->getStoreIds())) {
                        $product->addAttributeUpdate('recommended', 0, Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
                    }

                    $product->setStoreId($storeId);
                    $product->setRecommended(1);
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
                    'template' => 'pure',
                    'skin'     => 'pure',
                    'layout'   => 'pure'
                )
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

            'lightboxpro' => array(
                'general/enabled' => 1,
                'size' => array(
                    'main'      => '512x512',
                    'thumbnail' => '112x112',
                    'maxWindow' => '800x600'
                )
            ),

            'easyslide/general/load'    => 1,
            'askit/general/enabled'     => 1,
            'prolabels/general' => array(
                'enabled' => 1,
                'mobile'  => 0
            ),
            'navigationpro/top/enabled' => 1,
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
            'header_callout' => array(
                'title' => 'header_callout',
                'identifier' => 'header_callout',
                'status' => 1,
                'content' => <<<HTML
<img class="header-callout hidden-phone hidden-tablet" src="{{skin url="images/media/header_callout.gif"}}" alt="Toll-Free Customer Support 24/7" style="margin: 0; position: absolute; left: 300px; top: 2px;"/>
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
            'footer' => array(
                'title' => 'footer',
                'identifier' => 'footer',
                'status' => 1,
                'content' => <<<HTML
<div class="col3-set footer-set">
    <div class="col-1 col-customer-info">
        <div class="block block-footer-links">
        <div class="block-title"><span>Customer Information</span></div>
        <div class="block-content">
            <ul class="footer-links">
              <li>
                <ul>
                  <li><a href="{{store direct_url="block"}}">Blog</a></li>
                  <li><a href="{{store direct_url="location"}}">Store locator</a></li>
                  <li><a href="{{store direct_url="media"}}">Media</a></li>
                  <li><a href="{{store direct_url="help-center"}}">Help Center</a></li>
                </ul>
              </li>
              <li>
                <ul>
                  <li><a href="{{store direct_url="customer/account"}}">My Account</a></li>
                  <li><a href="{{store direct_url="sales/order/history"}}">Order Status</a></li>
                  <li><a href="{{store direct_url="wishlist"}}">Wishlist</a></li>
                  <li><a href="{{store direct_url="exchanges"}}">Returns and Exchanges</a></li>
                </ul>
              </li>
              <li class="last">
                <ul>
                  <li><a href="{{store direct_url="our-company"}}">Our Company</a></li>
                  <li><a href="{{store direct_url="about"}}">About us</a></li>
                  <li><a href="{{store direct_url="careers"}}">Careers</a></li>
                  <li><a href="{{store direct_url="shipping"}}">Shipping</a></li>
                </ul>
              </li>
            </ul>
        </div>
        </div>
        <img width="200" height="60" style="margin-top: 10px;" src="{{skin url="images/security_sign.gif"}}" srcset="{{skin url="images/security_sign@2x.gif"}} 2x" alt="Security Seal"/>
    </div>
    <div class="col-2 col-about">
        <div class="block footer-about">
            <div class="block-title"><span>About us</span></div>
            <div class="block-content">
                <p>Argento is more than just another template created for Magento. It was created right from the ground based on the best ecommerce stores practices.</p>
            </div>
        </div>
        <div class="block footer-social">
            <div class="block-title"><span>Join our community</span></div>
            <div class="block-content">
                <ul class="icons">
                    <li class="facebook"><a href="facebook.com">Facebook</a></li>
                    <li class="twitter"><a href="twitter.com">Twitter</a></li>
                    <li class="youtube"><a href="youtube.com">YouTube</a></li>
                    <li class="rss"><a href="rss.com">Rss</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-3 col-contact">
        {{block type="newsletter/subscribe" name="newsletter.footer" template="newsletter/subscribe.phtml"}}
        <div class="block footer-call-us">
            <div class="block-title"><span>Call us</span></div>
            <div class="block-content">
                <p class="footer-phone">1.800.555.1903</p>
                <p>We're available 24/7. Please note the more accurate the information you can provide us with the quicker we can respond to your query.</p>
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
<div class="homeslider">
    {{widget type="easyslide/insert" slider_id="argento_pure"}}
</div>
<div class="hidden-phone-off">
    {{widget type="easycatalogimg/widget_list" category_count="4" column_count="4" show_image="1" resize_image="1" image_width="180" image_height="180" subcategory_count="1" template="tm/easycatalogimg/list.phtml"}}
</div>

<div class="hidden-tablet hidden-phone">
{{widget type="easybanner/widget_placeholder" placeholder_name="argento-home-content"}}
</div>

<div class="tab-container">
    {{widget type="highlight/product_new" title="New arrivals" products_count="12" column_count="4" template="tm/highlight/product/grid.phtml" class_name="highlight-new" show_page_link="1" page_title="All new products &raquo;"}}
    {{widget type="highlight/product_bestseller" title="Bestsellers" products_count="12" column_count="4" template="tm/highlight/product/grid.phtml" class_name="highlight-bestsellers" show_page_link="1" page_title="All bestsellers &raquo;"}}
    {{widget type="highlight/product_attribute_yesno" attribute_code="recommended" title="Recommended products" products_count="12" column_count="4" template="tm/highlight/product/grid.phtml" class_name="highlight-attribute-recommended"}}
    {{widget type="highlight/product_special" title="On sale" products_count="12" column_count="4" template="tm/highlight/product/grid.phtml" class_name="highlight-special" show_page_link="1" page_title="All on sale products &raquo;"}}
    <div class="block-about block-highlight">
        <div class="block-title"><span>About Us</span></div>
        <div class="block-content" style="padding: 7px 7px;">
            <div class="col3-set">
                <div class="col-1">
                    <p style="line-height:1.2em;"><small>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi luctus. Duis lobortis. Nulla nec velit. Mauris pulvinar erat non massa. Suspendisse tortor turpis, porta nec, tempus vitae, iaculis semper, pede.</small></p>
                    <p style="color:#888; font:1.2em/1.4em georgia, serif;">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi luctus. Duis lobortis. Nulla nec velit. Mauris pulvinar erat non massa. Suspendisse tortor turpis, porta nec, tempus vitae, iaculis semper, pede. Cras vel libero id lectus rhoncus porta.</p>
                </div>
                <div class="col-2">
                    <p><strong style="color:#de036f;">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi luctus. Duis lobortis. Nulla nec velit.</strong></p>
                    <p>Vivamus tortor nisl, lobortis in, faucibus et, tempus at, dui. Nunc risus. Proin scelerisque augue. Nam ullamcorper. Phasellus id massa. Pellentesque nisl. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nunc augue. Aenean sed justo non leo vehicula laoreet. Praesent ipsum libero, auctor ac, tempus nec, tempor nec, justo. </p>
                    <p>Maecenas ullamcorper, odio vel tempus egestas, dui orci faucibus orci, sit amet aliquet lectus dolor et quam. Pellentesque consequat luctus purus. Nunc et risus. Etiam a nibh. Phasellus dignissim metus eget nisi. Vestibulum sapien dolor, aliquet nec, porta ac, malesuada a, libero. Praesent feugiat purus eget est. Nulla facilisi. Vestibulum tincidunt sapien eu velit. Mauris purus. Maecenas eget mauris eu orci accumsan feugiat. Pellentesque eget velit. Nunc tincidunt.</p>
                </div>
                <div class="col-3">
                    <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi luctus. Duis lobortis. Nulla nec velit. Mauris pulvinar erat non massa. Suspendisse tortor turpis, porta nec, tempus vitae, iaculis semper, pede. Cras vel libero id lectus rhoncus porta. Suspendisse convallis felis ac enim. Vivamus tortor nisl, lobortis in, faucibus et, tempus at, dui. Nunc risus. Proin scelerisque augue. Nam ullamcorper </p>
                    <p><strong style="color:#de036f;">Maecenas ullamcorper, odio vel tempus egestas, dui orci faucibus orci, sit amet aliquet lectus dolor et quam. Pellentesque consequat luctus purus.</strong></p>
                    <p>Nunc et risus. Etiam a nibh. Phasellus dignissim metus eget nisi.</p>
                    <div class="divider"></div>
                    <p>To all of you, from all of us at Magento Demo Store - Thank you and Happy eCommerce!</p>
                    <p style="line-height:1.2em;"><strong style="font:italic 2em Georgia, serif;">John Doe</strong><br/><small>Some important guy</small></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    new TabBuilder();
    new IScroll($$('.tab-container .tabs-wrapper')[0], {
        click: true,
        tap  : true,
        bindToWrapper: true,
        scrollX: true,
        scrollY: false
    });
</script>

<div class="block brands-home hidden-phone">
    <div class="block-title">
        <span>Featured Brands</span>
    </div>
    <div class="block-content">
        <a href="#" id="left">Left</a>
        <a href="#" id="right">Right</a>
        <div id="slider-brands-container" class="slider-wrapper">
            <ul class="list-brands slider-brands" id="slider-brands">
                <li><a href="#"><img src="{{skin url="images/catalog/brands/gucci.jpg"}}" alt="" width="150" height="80"/></a></li>
                <li><a href="#"><img src="{{skin url="images/catalog/brands/lv.jpg"}}" alt="" width="100" height="80"/></a></li>
                <li><a href="#"><img src="{{skin url="images/catalog/brands/ck.jpg"}}" alt="" width="130" height="80"/></a></li>
                <li><a href="#"><img src="{{skin url="images/catalog/brands/chanel.jpg"}}" alt="" width="170" height="80"/></a></li>
                <li><a href="#"><img src="{{skin url="images/catalog/brands/guess.jpg"}}" alt="" width="130" height="80"/></a></li>
                <li><a href="#"><img src="{{skin url="images/catalog/brands/versace.jpg"}}" alt="" width="145" height="80"/></a></li>
                <li><a href="#"><img src="{{skin url="images/catalog/brands/gucci.jpg"}}" alt="" width="150" height="80"/></a></li>
                <li><a href="#"><img src="{{skin url="images/catalog/brands/lv.jpg"}}" alt="" width="100" height="80"/></a></li>
                <li><a href="#"><img src="{{skin url="images/catalog/brands/ck.jpg"}}" alt="" width="130" height="80"/></a></li>
                <li><a href="#"><img src="{{skin url="images/catalog/brands/chanel.jpg"}}" alt="" width="170" height="80"/></a></li>
                <li><a href="#"><img src="{{skin url="images/catalog/brands/guess.jpg"}}" alt="" width="130" height="80"/></a></li>
                <li><a href="#"><img src="{{skin url="images/catalog/brands/versace.jpg"}}" alt="" width="145" height="80"/></a></li>
            </ul>
        </div>
        <script type="text/javascript">
            new Slider("slider-brands-container", "left", "right", {shift: 'auto'});
        </script>
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
                'identifier'    => 'argento_pure',
                'title'         => 'Argento Pure',
                'width'         => 958,
                'height'        => 349,
                'duration'      => 0.5,
                'frequency'     => 4.0,
                'autoglide'     => 1,
                'controls_type' => 'number',
                'status'        => 1,
                'slides'        => array(
                    array(
                        'url'   => 'argento/pure/argento_pure_slider1.jpg',
                        'image' => 'Slide 1',
                        'description' => 'Semi-Annual Sale: Extra 10% off Select fashion brands'
                    ),
                    array(
                        'url'   => 'argento/pure/argento_pure_slider2.jpg',
                        'image' => 'Slide 2',
                        'description' => 'Semi-Annual Sale: Extra 10% off Select fashion brands'
                    ),
                    array(
                        'url'   => 'argento/pure/argento_pure_slider3.jpg',
                        'image' => 'Slide 3',
                        'description' => 'Semi-Annual Sale: Extra 10% off Select fashion brands'
                    ),
                    array(
                        'url'   => 'argento/pure/argento_pure_slider4.jpg',
                        'image' => 'Slide 4',
                        'description' => 'Semi-Annual Sale: Extra 10% off Select fashion brands'
                    ),
                    array(
                        'url'   => 'argento/pure/argento_pure_slider5.jpg',
                        'image' => 'Slide 5',
                        'description' => 'Semi-Annual Sale: Extra 10% off Select fashion brands'
                    )
                )
            )
        );
    }

    private function _getEasybanner()
    {
        return array(
            array(
                'name'         => 'argento-home-content',
                'parent_block' => 'non-existing-block',
                'banners'      => array(
                    array(
                        'identifier' => 'argento_pure-home-content1',
                        'title'      => 'HP Envy 17',
                        'url'        => 'discounts',
                        'image'      => 'argento/pure/argento_pure_callout_home_content1.gif',
                        'width'          => 960,
                        'height'         => 147,
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
                'attribute_code' => 'recommended',
                'frontend_label' => array('Recommended'),
                'default_value'  => 0
            )
        );
    }
}
