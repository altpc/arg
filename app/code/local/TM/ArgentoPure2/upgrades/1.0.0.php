<?php

class TM_ArgentoPure2_Upgrade_1_0_0 extends TM_Core_Model_Module_Upgrade
{
    /**
     * Create new products if they are not exists
     */
    public function up()
    {
        // Remove related product from easytabs
        $this->unsetEasytab('easytabs/tab_product_related', $this->getStoreIds());

        // New products
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

            // recommended products
            $recommended = Mage::getResourceModel('highlight/catalog_product_collection');
            $recommended
                ->setStoreId($storeId)
                ->setVisibility($visibility)
                ->addStoreFilter($storeId)
                ->addCategoryFilter($rootCategory)
                ->setPageSize(1)
                ->setCurPage(1);

            $attributeCode = 'recommended';
            if (!$recommended->getAttribute($attributeCode)) { // Mage 1.6.0.0 fix
                continue;
            }
            $recommended->addAttributeToFilter("{$attributeCode}", array('Yes' => true));

            if (!$recommended->count()) {
                foreach ($visibleProducts as $product) {
                    // attribute should be saved in global scope
                    if (!in_array(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, $this->getStoreIds())) {
                        $product->addAttributeUpdate($attributeCode, 0, Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
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
            'easybanner'    => $this->_getEasybanner(),
            'easyslide'     => $this->_getSlider(),
            'productAttribute' => $this->_getProductAttribute()
        );
    }

    private function _getConfiguration()
    {
        return array(
            'design' => array(
                'package/name' => 'argento',
                'theme' => array(
                    'template' => 'pure2',
                    'skin'     => 'pure2',
                    'layout'   => 'pure2',
                    'after_default' => Mage::helper('argento')->isEnterprise() ?
                        'enterprise/default' : ''
                )
            ),
            'catalog/product_image/small_width' => 315,

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

            'testimonials/general/enabled' => 1,

            'tm_ajaxsearch/general' => array(
                'enabled'              => 1,
                'show_category_filter' => 0,
                'searchfieldtext'      => 'SEARCH',
                'width'                => 'auto',
                'enablesuggest'        => 0,
                'enablecatalog'        => 0,
                'enablecms'            => 0,
                'enabletags'           => 0,
                'enabledescription'    => 0,
                'descriptionchars'     => 50,
                'imagewidth'           => 32,
                'imageheight'          => 32,
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
                    'productscount'     => 3,
                    'columns'           => 3,
                    'addtocartcheckbox' => 0,
                    'amazonestyle'      => 1
                ),
                'customer' => array(
                    'enabled'       => 1,
                    'productscount' => 3,
                    'columns'       => 3
                )

            ),

            'richsnippets/general' => array(
                'enabled' => 1
            ),

            'askit/general/enabled' => 1,
            'prolabels/general' => array(
                'enabled' => 1,
                'mobile'  => 0
            ),
            'lightboxpro' => array(
                'general/enabled' => 1,
                'size' => array(
                    'main'      => '512x800',
                    'main_keep_frame' => false,
                    'thumbnail' => '80x120',
                    'thumbnail_keep_frame' => true,
                    'maxWindow' => '800x600',
                    'popup'     => '0x0',
                    'popup_keep_frame' => false
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
            'scroll_up' => array(
                'title'      => 'scroll_up',
                'identifier' => 'scroll_up',
                'status'     => 1,
                'content'    => <<<HTML
<p id="scroll-up" class="hidden-tablet hidden-phone">
    <a href="#"><i class="fa fa-4x fa-chevron-up">&#8203;</i></a>
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
            'footer_cms' => array(
                'title' => 'footer_cms',
                'identifier' => 'footer_cms',
                'status' => 1,
                'content' => <<<HTML
<div class="footer-cms-container">
    <div class="footer-cms">
        <div class="block block-social">
                <ul class="icons">
                    <li class="twitter"><a href="twitter.com">Twitter</a></li>
                    <li class="facebook"><a href="facebook.com">Facebook</a></li>
                    <li class="youtube"><a href="youtube.com">YouTube</a></li>
                    <li class="rss"><a href="rss.com">Rss</a></li>
                </ul>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-5">
                <div class="block block-information">
                    <div class="block-title"><span>Company Information</span></div>
                    <div class="block-content">
                        <ul>
                            <li><a href="{{store url='blog'}}">Blog</a></li>
                            <li><a href="{{store url='sales/guest/form'}}">Order Status</a></li>
                            <li><a href="{{store url='storelocator'}}">Store Locator</a></li>
                            <li><a href="{{store url='wishlist'}}">Wishlist</a></li>
                            <li><a href="{{store url='privacy'}}">Privacy Policy</a></li>
                            <li><a href="{{store url='customer/account'}}">Personal Account</a></li>
                            <li><a href="{{store url='terms'}}">Terms of Use</a></li>
                            <li><a href="{{store url='returns'}}">Returns &amp; Exchanges</a></li>
                            <li><a href="{{store url='company'}}">Our Company</a></li>
                            <li><a href="{{store url='careers'}}">Careers</a></li>
                            <li><a href="{{store url='about'}}">About us</a></li>
                            <li><a href="{{store url='shipping'}}">Shipping</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="block block-about">
                    <div class="block-title"><span>Call Us</span></div>
                    <div class="block-content">
                        <a class="footer-phone" href="tel:1.800.555.1903">1.800.555.1903</a>
                        <p>
        We're available 24/7. Please note the more accurate the information you can provide us with the quicker we can respond to your query.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4">
                {{block type="newsletter/subscribe" name="footer.newsletter" template="newsletter/subscribe.phtml"}}
            </div>
        </div>
    </div>
</div>
HTML
            ),
            'footer_payments' => array(
                'title'      => 'footer_payments',
                'identifier' => 'footer_payments',
                'status'     => 1,
                'content'    => <<<HTML
<img class="payments" src="{{skin url='images/payments.png'}}"
            srcset="{{skin url='images/payments.png'}} 1x, {{skin url='images/payments@2x.png'}} 2x"
            alt="Payment methods"/>
HTML
            ),
            'product_sidebar' => array(
                'title'      => 'product_sidebar',
                'identifier' => 'product_sidebar',
                'status'     => 1,
                'content'    => <<<HTML
<div class="block block-product-sidebar">
    <div class="block-content">
        {{widget type="attributepages/product_option" template="tm/attributepages/product/options.phtml" width="180" height="90" use_image="1" image_type="image" use_link="1" attribute_code="manufacturer" css_class="hidden-label"}}
        {{widget type="cms/widget_block" template="cms/widget/static_block/default.phtml" block_id="services_sidebar"}}
    </div>
</div>
HTML
            ),
            'services_sidebar' => array(
                'title'      => 'services_sidebar',
                'identifier' => 'services_sidebar',
                'status'     => 1,
                'content'    => <<<HTML
<div class="block block-services-sidebar">
    <div class="block-title"><span>Our Services</span></div>
    <div class="block-content">
        <div class="icon-section section-delivery section-left">
            <span class="fa-stack fa-2x icons-primary">
                <i class="fa fa-circle fa-stack-2x">&#8203;</i>
                <i class="fa fa-truck fa-stack-1x fa-inverse">&#8203;</i>
            </span>
            <div class="section-info">
                <h5>Delivery</h5>
                <p>We guarantee to ship your order next day after order has been submitted</p>
            </div>
        </div>
        <div class="icon-section section-customer-service section-left">
            <span class="fa-stack fa-2x icons-primary">
                <i class="fa fa-circle fa-stack-2x">&#8203;</i>
                <i class="fa fa-users fa-stack-1x fa-inverse">&#8203;</i>
            </span>
            <div class="section-info">
                <h5>Customer Service</h5>
                <p>Please contacts us and our customer service team will answer all your questions</p>
            </div>
        </div>
        <div class="icon-section section-returns section-left">
            <span class="fa-stack fa-2x icons-primary">
                <i class="fa fa-circle fa-stack-2x">&#8203;</i>
                <i class="fa fa-reply fa-stack-1x fa-inverse">&#8203;</i>
            </span>
            <div class="section-info">
                <h5>Easy Returns</h5>
                <p>If you are not satisfied with your order - send it back within  30 days after day of purchase!</p>
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
<div class="row jumbotron jumbotron-slider jumbotron-image">
    <div class="container wow fadeIn">
        {{widget type="easyslide/insert" slider_id="argento_pure2"}}
    </div>
</div>

<div class="row jumbotron">
    <div class="container">
        <div class="block block-dotted">
            <div class="block-title"><span>The Essentials</span></div>
            <div class="block-content">
                {{widget type="easycatalogimg/widget_list" background_color="255,255,255" category_count="4" subcategory_count="6" column_count="4" show_image="1" image_width="200" image_height="200" template="tm/easycatalogimg/list.phtml"}}
            </div>
        </div>
    </div>
</div>

<div class="row jumbotron">
    <div class="container">
        <div class="tab-container">
            {{widget type="highlight/product_special" title="Sale" products_count="6" column_count="3" template="tm/highlight/product/grid.phtml" class_name="highlight-special" show_page_link="1" page_title="Shop Sale" img_width="315" img_keep_frame="0"}}
            {{widget type="highlight/product_bestseller" title="Bestsellers" products_count="6" column_count="3" template="tm/highlight/product/grid.phtml" class_name="highlight-bestsellers" show_page_link="1" page_title="Shop Bestsellers" img_width="315" img_keep_frame="0"}}
            {{widget type="highlight/product_popular" title="Popular" products_count="6" column_count="3" template="tm/highlight/product/grid.phtml" class_name="highlight-popular" show_page_link="1" page_title="Shop Popular" img_width="315" img_keep_frame="0"}}
            {{widget type="highlight/product_attribute_yesno" attribute_code="recommended" title="Editor's Choice" products_count="6" column_count="3" template="tm/highlight/product/grid.phtml" class_name="highlight-attribute-recommended" img_width="315" img_keep_frame="0"}}
            {{widget type="highlight/product_new" title="New arrivals" products_count="6" column_count="3" template="tm/highlight/product/grid.phtml" class_name="highlight-new" show_page_link="1" page_title="Shop New" img_width="315" img_keep_frame="0"}}
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
    </div>
</div>

<div class="row jumbotron">
    <div class="container">
        <div class="block block-brands argento-slider wow fadeIn" data-wow-delay="0.2s">
            <div class="block-title"><span>Our Brands</span></div>
            <div class="block-content">
                <a href="#" id="left" class="trigger trigger-left"><i class="fa fa-4x fa-angle-right">&#8203;</i></a>
                <a href="#" id="right" class="trigger trigger-right"><i class="fa fa-4x fa-angle-left">&#8203;</i></a>
                <div id="slider-brands-container" class="slider-wrapper">
                    <ul class="list-slider" id="slider-brands">
                <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/gucci.jpg"}}" alt="" width="150" height="80"/></a></li>
                <li class="item" ><a href="#"><img src="{{skin url="images/catalog/brands/lv.jpg"}}" alt="" width="100" height="80"/></a></li>
                <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/ck.jpg"}}" alt="" width="130" height="80"/></a></li>
                <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/chanel.jpg"}}" alt="" width="170" height="80"/></a></li>
                <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/guess.jpg"}}" alt="" width="130" height="80"/></a></li>
                <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/versace.jpg"}}" alt="" width="145" height="80"/></a></li>
                <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/gucci.jpg"}}" alt="" width="150" height="80"/></a></li>
                <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/lv.jpg"}}" alt="" width="100" height="80"/></a></li>
                <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/ck.jpg"}}" alt="" width="130" height="80"/></a></li>
                <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/chanel.jpg"}}" alt="" width="170" height="80"/></a></li>
                <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/guess.jpg"}}" alt="" width="130" height="80"/></a></li>
                <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/versace.jpg"}}" alt="" width="145" height="80"/></a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    new Slider("slider-brands-container", "left", "right", {shift: 'auto'});
                </script>
            </div>
        </div>
    </div>
</div>
<div class="row jumbotron">
    <div class="container block-homepage-banner">
        {{widget type="easybanner/widget_placeholder" placeholder_name="argento-pure2-home"}}
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

    private function _getEasybanner()
    {
        return array(
            array(
                'name'         => 'argento-pure2-home',
                'parent_block' => 'non-existing-block',
                'limit'        => 1,
                'banners'      => array(
                    array(
                        'identifier' => 'argento-pure2-home1',
                        'title'      => 'Special Offer',
                        'url'        => 'free-shipping',
                        'class_name' => 'home-banner',
                        'image'      => 'argento/pure2/argento_pure2_callout_home1.png',
                        'width'          => 1160,
                        'height'         => 130,
                        'resize_image'   => 0,
                        'retina_support' => 0
                    )
                )
            )
        );
    }

    private function _getSlider()
    {
        return array(
            array(
                'identifier'    => 'argento_pure2',
                'title'         => 'Argento Pure 2.0',
                'width'         => 1263,
                'height'        => 375,
                'duration'      => 0.5,
                'frequency'     => 4.0,
                'autoglide'     => 1,
                'controls_type' => 'number',
                'status'        => 1,
                'slides'        => array(
                    array(
                        'url'   => 'argento/pure2/argento_pure2_slider1.jpg',
                        'image' => '25% off',
                        'description' => '',
                        'desc_pos' => TM_Easyslide_Model_Easyslide_Slides::DESCRIPTION_CENTER,
                        'background' => TM_Easyslide_Model_Easyslide_Slides::BACKGROUND_TRANSPARENT
                    ),
                    array(
                        'url'   => 'argento/pure2/argento_pure2_slider2.jpg',
                        'image' => '25% off green',
                        'description' => '',
                        'desc_pos' => TM_Easyslide_Model_Easyslide_Slides::DESCRIPTION_CENTER,
                        'background' => TM_Easyslide_Model_Easyslide_Slides::BACKGROUND_TRANSPARENT
                    ),
                    array(
                        'url'   => 'argento/pure2/argento_pure2_slider3.jpg',
                        'image' => '25% off orange',
                        'description' => '',
                        'desc_pos' => TM_Easyslide_Model_Easyslide_Slides::DESCRIPTION_CENTER,
                        'background' => TM_Easyslide_Model_Easyslide_Slides::BACKGROUND_TRANSPARENT
                    )
                )
            )
        );
    }

    private function _getProductAttribute()
    {
        return array(
            array(
                'attribute_code' => 'recommended',
                'frontend_label' => array('Recommended'),
                'default_value'  => 0
            )
        );
    }
}
