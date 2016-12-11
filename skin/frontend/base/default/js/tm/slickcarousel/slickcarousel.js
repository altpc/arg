document.observe("dom:loaded", function() {
    if (typeof jQuery === 'undefined') {
        console.warn('Slick Carousel can not find jQuery');
        return;
    }
    // init slick
    jQuery('div[data-slick]').slick();
    // init slick wrapper
    jQuery.each(jQuery('div[data-slick-wrapper]'), function() {
        var wrapper = jQuery(this);
        var config = wrapper.data('slick-wrapper');
        if (config.el) {
            wrapper.find(config.el).slick(config);
        }
    });
});
