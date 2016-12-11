/*   _     _            ____
    / \   (_) __ ___  _|  _ \ _ __ ___
   / _ \  | |/ _` \ \/ / |_) | '__/ _ \
  / ___ \ | | (_| |>  <|  __/| | | (_) |
 /_/   \_\/ |\__,_/_/\_\_|   |_|  \___/
        |__/
*/
document.observe("dom:loaded", function (){

    /* catalog/product/compare.js*/
    AjaxPro.observe('onFailure:catalog:product_compare', function() {
        if (!AjaxPro.opacity) {
            return;
        }
        if (SmartHeader && jQuery) {
            jQuery('.sticky-container #mini-compare').last().remove();
        }
    });

    AjaxPro.observe('onSuccess:catalog:product_compare', function() {
        if (SmartHeader && jQuery) {
            jQuery('.sticky-container #mini-compare').last().remove();
        }
    });

    beforeSmartHeaderFix = function () {
        if (SmartHeader && AjaxPro.config.get('isMobile') == '1') {
            // console.log('before ');
            // console.log(jQuery(".mini-compare").length);
            // jQuery("#mini-compare").remove();
            // SmartHeader.moveElementsToRegularPosition();
        }
    };
    afterSmartHeaderFix = function() {
        if (SmartHeader && AjaxPro.config.get('isMobile') == '1') {

            var length = jQuery('#mini-compare ol li').length;

            var countEl = jQuery("a[href='#header-compare'] .count");
            if (length > 0 && !countEl.length) {
                jQuery("a[href='#header-compare'] span").last()
                    .before('<span class="count">1</span>');
            }

            if (length == 0) {
                jQuery("a[href='#header-compare'] .count").remove();
            } else {
                jQuery("a[href='#header-compare'] .count").html(length);
            }
            SmartHeader.init();

            jQuery('#mini-compare').removeClass('dropdown');

            jQuery('.skip-active').removeClass('skip-active');

            jQuery(function($) {

                //Skip Links
                var skipContents = $('.skip-content');
                var skipLinks = $('.skip-link');

                skipLinks.off('click').on('click', function (e) {
                    e.preventDefault();

                    var self = $(this);
                    var target = self.attr('href');

                    //Get target element
                    var elem = $(target);

                    //Check if stub is open
                    var isSkipContentOpen = elem.hasClass('skip-active') ? 1 : 0;

                    //Hide all stubs
                    skipLinks.removeClass('skip-active');
                    skipContents.removeClass('skip-active');

                    //Toggle stubs
                    if (isSkipContentOpen) {
                        self.removeClass('skip-active');
                    } else {
                        self.addClass('skip-active');
                        elem.addClass('skip-active');
                    }
                });

            });
        }
    };

    AjaxPro.observe('onComplete:catalog:product_compare:before', beforeSmartHeaderFix);
    AjaxPro.observe('onComplete:catalog:product_compare:after', afterSmartHeaderFix);

    // AjaxPro.observe('onFailure:catalog:product_compare:before', beforeSmartHeaderFix);
    // AjaxPro.observe('onFailure:catalog:product_compare:after', afterSmartHeaderFix);

    console.log('ajaxpro ultimo compare.js patch was running');

    /* checkout/cart.js*/
    AjaxPro.observe('onFailure:checkout', function() {
        if (!AjaxPro.opacity) {
            return;
        }
        if (SmartHeader && jQuery) {
            jQuery('.sticky-container #mini-cart').last().remove();
        }
    });

    AjaxPro.observe('onSuccess:checkout', function() {
        if (SmartHeader && jQuery) {
            jQuery('.sticky-container #mini-cart').last().remove();
        }
    });

    // if (AjaxPro.config.get('isMobile') == '1') {

        beforeSmartHeaderFix = function () {
            if (SmartHeader && AjaxPro.config.get('isMobile') == '1') {
                jQuery("#mini-cart").remove();
                // SmartHeader.moveElementsToRegularPosition();
            }
        };
        afterSmartHeaderFix = function() {
            if (SmartHeader && AjaxPro.config.get('isMobile') == '1') {
                jQuery('#mini-cart-wrapper-mobile').prepend(jQuery('#mini-cart'));

                jQuery('#mini-cart').removeClass('dropdown');

                jQuery('.skip-active').removeClass('skip-active');

                //Skip Links
                var skipContents = jQuery('.skip-content');
                var skipLinks = jQuery('.skip-link');

                skipLinks.off('click').on('click', function (e) {
                    e.preventDefault();

                    var self = jQuery(this);
                    var target = self.attr('href');

                    //Get target element
                    var elem = jQuery(target);

                    //Check if stub is open
                    var isSkipContentOpen = elem.hasClass('skip-active') ? 1 : 0;

                    //Hide all stubs
                    skipLinks.removeClass('skip-active');
                    skipContents.removeClass('skip-active');

                    //Toggle stubs
                    if (isSkipContentOpen) {
                        self.removeClass('skip-active');
                    } else {
                        self.addClass('skip-active');
                        elem.addClass('skip-active');
                    }
                });
            }
        };

        AjaxPro.observe('onComplete:checkout:before', beforeSmartHeaderFix);
        AjaxPro.observe('onComplete:checkout:after', afterSmartHeaderFix);

        // AjaxPro.observe('onFailure:checkout:before', beforeSmartHeaderFix);
        // AjaxPro.observe('onFailure:checkout:after', afterSmartHeaderFix);
        console.log('ajaxpro ultimo cart.js patch running');
    // }
});