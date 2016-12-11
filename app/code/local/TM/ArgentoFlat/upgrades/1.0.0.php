<?php

class TM_ArgentoFlat_Upgrade_1_0_0 extends TM_Core_Model_Module_Upgrade
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
        }
    }

    public function getOperations()
    {
        return array(
            'configuration' => $this->_getConfiguration(),
            'cmsblock'      => $this->_getCmsBlocks(),
            'cmspage'       => $this->_getCmsPages(),
            'easybanner'    => $this->_getEasybanner(),
            'easyslide'     => $this->_getSlider()
        );
    }

    private function _getConfiguration()
    {
        return array(
            'design' => array(
                'package/name' => 'argento',
                'theme' => array(
                    'template' => 'flat',
                    'skin'     => 'flat',
                    'layout'   => 'flat',
                    'after_default' => Mage::helper('argento')->isEnterprise() ?
                        'enterprise/default' : ''
                )
            ),
            'catalog/product_image/small_width' => 200,

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
                'width'                => 'auto',
                'descriptionchars'     => 200,
                'imagewidth'           => 100,
                'imageheight'          => 100,
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

            'askit/general/enabled' => 1,
            'prolabels/general' => array(
                'enabled' => 1,
                'mobile'  => 0
            ),
            'lightboxpro' => array(
                'general/enabled' => 1,
                'size' => array(
                    'main'      => '512x512',
                    'thumbnail' => '112x112',
                    'maxWindow' => '800x600',
                    'popup'     => '0x0'
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
<div class="footer-cms-container jumbotron jumbotron-dark">
    <div class="footer-cms">
        <div class="row">
            <div class="col-md-4">
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
            <div class="col-md-4">
                <div class="block block-social">
                    <div class="block-title"><span>Get Social</span></div>
                    <div class="block-content">
                        <p>
                            Join our on Facebook and get recent news about our new
                            products and offers.
                        </p>
                        <ul class="icons">
                            <li class="twitter"><a href="twitter.com">Twitter</a></li>
                            <li class="facebook"><a href="facebook.com">Facebook</a></li>
                            <li class="youtube"><a href="youtube.com">YouTube</a></li>
                            <li class="rss"><a href="rss.com">Rss</a></li>
                        </ul>
                    </div>
                </div>
                {{block type="newsletter/subscribe" name="footer.newsletter" template="newsletter/subscribe.phtml"}}
            </div>
            <div class="col-md-4">
                <div class="block block-about">
                    <div class="block-title"><span>About us</span></div>
                    <div class="block-content">
                        <address>
                            2311 North Avenue, Pasadena, California<br/>
                            Phone: 1-888-555-7463<br/>
                            Fax: 1-888-555-2742<br/>
                            Email: <a href="mailto:info@naturalherbs.com" title="Email to info@naturalherbs.com">info@naturalherbs.com</a>
                        </address>
                        <br/>
                        <p>
                            Natural Herbs is truly professional company on vitamine
                            and sport nutrition supplements' marketplace. We sell
                            only the highest-grade substances needed for health
                            and bodily growth. Our web-store offers a huge choice
                            of products for better physical wellbeing. Let's engage
                            people to be healthy!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
HTML
            ),
            'footer_payments' => array(
                'title' => 'footer_payments',
                'identifier' => 'footer_payments',
                'status' => 1,
                'content' => <<<HTML
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
    <div class="col-md-12">
        <div class="cover cover-pastel">
            <div class="left triangle"></div>
            <div class="right triangle"></div>
        </div>
        <div class="container wow fadeIn">
            {{widget type="easyslide/insert" slider_id="argento_flat"}}
        </div>
    </div>
</div>
<div class="row jumbotron jumbotron-pastel jumbotron-inverse">
    <div class="col-md-12">
        <div class="container">
            <div class="hero block block-categories">
                <div class="block-title"><span>Shop Our Store for</span><p class="subtitle no-margin">more than 25,000 health products including vitamins, herbs, sport supplements, diet and much more!</p></div>
                <div class="block-content">
                    {{widget type="easycatalogimg/widget_list" background_color="34,147,146" category_count="4" subcategory_count="2" column_count="4" show_image="1" image_width="200" image_height="200" template="tm/easycatalogimg/list.phtml"}}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row jumbotron jumbotron-pastel-alt no-padding">
    <div class="col-md-12">
        <div class="container hero block-homepage-banner">
            {{widget type="easybanner/widget_placeholder" placeholder_name="argento-flat-home"}}
        </div>
    </div>
</div>
<div class="row jumbotron">
    <div class="col-md-12">
        <div class="container hero">
            {{widget type="highlight/product_new" title="New Arrivals" show_page_link="1" page_title="Browse all new products at our store &raquo;" products_count="4" column_count="4" template="tm/highlight/product/grid.phtml" class_name="highlight-content-new" img_width="200" img_height="200"}}
        </div>
    </div>
</div>
<div class="row jumbotron jumbotron-pattern">
    <div class="cover">
        <div class="left triangle"></div>
        <div class="right triangle"></div>
    </div>
    <div class="stub"></div>
    <div class="col-md-12 container-wrapper">
        <div class="container hero">
            {{widget type="highlight/product_special" title="Special Offer" show_page_link="1" page_title="Browse all products on sale at our store &raquo;" products_count="4" column_count="4" template="tm/highlight/product/grid.phtml" class_name="highlight-content-special" img_width="200" img_height="200"}}
        </div>
    </div>
</div>
<div class="row jumbotron">
    <div class="col-md-12">
        <div class="container hero">
            {{widget type="highlight/product_bestseller" title="Our Bestsellers" show_page_link="1" page_title="Browse all bestseller products at our store &raquo;" products_count="4" column_count="4" template="tm/highlight/product/grid.phtml" class_name="highlight-content-bestsellers" img_width="200" img_height="200"}}
        </div>
    </div>
</div>
<div class="row jumbotron">
    <div class="stub"></div>
    <div class="col-md-12 container-wrapper">
        <div class="container hero">
            <div class="hero block block-benefits">
                <div class="block-title wow fadeInDown" data-wow-duration="0.5s"><span>Why choose us</span></div>
                <div class="block-content">
                    <div class="row">
                        <div class="col-md-3 col-xs-6 wow slideInUp">
                            <span class="fa-stack fa-4x"><i class="fa fa-circle fa-stack-2x">&#8203;</i><i class="fa fa-tags fa-stack-1x fa-inverse">&#8203;</i></span>
                            <h3>Low Pricing</h3>
                            <p>Meet all types for your body's needs, that are healthy for you and for your pocket. Click for big savings.</p>
                        </div>
                        <div class="col-md-3 col-xs-6 wow slideInUp">
                            <span class="fa-stack fa-4x"><i class="fa fa-circle fa-stack-2x">&#8203;</i><i class="fa fa-cubes fa-stack-1x fa-inverse">&#8203;</i></span>
                            <h3>Huge Selection</h3>
                            <p>Make your healthy choice using the huge variety of vitamins and sports nutrition. Let your transformation go on.</p>
                        </div>
                        <div class="col-md-3 col-xs-6 wow slideInUp">
                            <span class="fa-stack fa-4x"><i class="fa fa-circle fa-stack-2x">&#8203;</i><i class="fa fa-birthday-cake fa-stack-1x fa-inverse">&#8203;</i></span>
                            <h3>Reward Points</h3>
                            <p>Get reward points by boosting your healthy activity online. Stay with us and gain more.</p>
                        </div>
                        <div class="col-md-3 col-xs-6 wow slideInUp">
                            <span class="fa-stack fa-4x"><i class="fa fa-circle fa-stack-2x">&#8203;</i><i class="fa fa-comments fa-stack-1x fa-inverse">&#8203;</i></span>
                            <h3>Ask Experts</h3>
                            <p>Have a question? Ask an expert and get complete online support. We are open for you.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row jumbotron jumbotron-bright jumbotron-inverse">
    <div class="stub"></div>
    <div class="col-md-12 container-wrapper">
        <div class="container">
            <div class="hero block block-about wow fadeIn"  data-wow-delay="0.2s">
                <div class="block-title"><span>About us</span></div>
                <div class="block-content">
                    <p>
                        Natural Herbs company was found with idea to ensure users more natural healthy care.
                        The company is making name for itself as an advanced store with reliable service. Our
                        online store works with leaders worldwide producing vitamins, herbs and sport nutrition
                        supplements. We provide high-quality products that suit your needs and fit your budget.
                    </p>
                    <p>
                        Natural Herbs is aiming to become your full-service friend. We focus on keeping you motivated
                        improve your health. Build your own body with us! We'll help you to reach your goal.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row jumbotron">
    <div class="stub"></div>
    <div class="cover cover-dark">
        <div class="left triangle"></div>
        <div class="right triangle"></div>
    </div>
    <div class="col-md-12 container-wrapper">
        <div class="container hero">
            <div class="hero block block-brands argento-slider wow fadeIn" data-wow-delay="0.2s">
                <div class="block-title"><span>Popular Brands</span><p class="subtitle">check most trusted brands from more then 50 leading manufactures presented at our store.</p></div>
                <div class="block-content">
                    <a href="#" id="left" class="trigger trigger-left"><i class="fa fa-4x fa-angle-right">&#8203;</i></a>
                    <a href="#" id="right" class="trigger trigger-right"><i class="fa fa-4x fa-angle-left">&#8203;</i></a>
                    <div id="slider-brands-container" class="slider-wrapper">
                        <ul class="list-slider" id="slider-brands">
                            <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/life_extension.gif"}}" alt="Life Extension"/></a></li>
                            <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/gnc.gif"}}" alt="GNC"/></a></li>
                            <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/mega_food.gif"}}" alt="Mega Food" /></a></li>
                            <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/nordic_naturals.gif"}}" alt="Nordic Naturals"/></a></li>
                            <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/life_extension.gif"}}" alt="Life Extension"/></a></li>
                            <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/gnc.gif"}}" alt="GNC"/></a></li>
                            <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/mega_food.gif"}}" alt="Mega Food"/></a></li>
                            <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/nordic_naturals.gif"}}" alt="Nordic Naturals"/></a></li>
                            <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/life_extension.gif"}}" alt="Life Extension"/></a></li>
                            <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/gnc.gif"}}" alt="GNC"/></a></li>
                            <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/mega_food.gif"}}" alt="Mega Food"/></a></li>
                            <li class="item"><a href="#"><img src="{{skin url="images/catalog/brands/nordic_naturals.gif"}}" alt="Nordic Naturals"/></a></li>
                        </ul>
                    </div>
                    <script type="text/javascript">
                        new Slider("slider-brands-container", "left", "right", {shift: 'auto'});
                    </script>
                </div>
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

    private function _getEasybanner()
    {
        return array(
            array(
                'name'         => 'argento-flat-home',
                'parent_block' => 'non-existing-block',
                'limit'        => 1,
                'banners'      => array(
                    array(
                        'identifier' => 'argento-flat-home1',
                        'title'      => 'Special Offer',
                        'url'        => 'free-shipping',
                        'image'      => 'argento/flat/argento_flat_callout_home1.png',
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
                'identifier'    => 'argento_flat',
                'title'         => 'Argento Flat',
                'width'         => 1160,
                'height'        => 447,
                'duration'      => 0.5,
                'frequency'     => 4.0,
                'autoglide'     => 1,
                'controls_type' => 'arrow',
                'status'        => 1,
                'slides'        => array(
                    array(
                        'url'   => 'argento/flat/argento_flat_slider1.png',
                        'image' => '25% off',
                        'description' => '',
                        'desc_pos' => 4,
                        'background' => 2
                    ),
                    array(
                        'url'   => 'argento/flat/argento_flat_slider2.png',
                        'image' => '25% off green',
                        'description' => '',
                        'desc_pos' => 4,
                        'background' => 2
                    ),
                    array(
                        'url'   => 'argento/flat/argento_flat_slider3.png',
                        'image' => '25% off orange',
                        'description' => '',
                        'desc_pos' => 4,
                        'background' => 2
                    )
                )
            )
        );
    }
}
