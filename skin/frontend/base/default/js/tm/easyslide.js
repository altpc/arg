document.observe('dom:loaded', function() {
    // Prototype slider - start
    var sliders = $$('.easyslideslider-id');
    if (sliders.length>0) {
        // initialize slider resizing on small (mobile) screens
        window.mobileSliders = [];
        document.observe("easyslide:init", function(event) {
            mobileSliders.push(new EasysliderMobile(event.memo.slider));
        });
        /* Don't forget about screen orientation change */
        if ('addEventListener' in window) {
            var supportsOrientationChange = "onorientationchange" in window,
                orientationEvent = supportsOrientationChange ? "orientationchange" : "resize";
            window.addEventListener(orientationEvent, function() {
                clearTimeout(window.sliderResizeTimer);
                window.sliderResizeTimer = setTimeout(function() {
                    mobileSliders.each(function(mobileSlider) {
                        mobileSlider.updateSize();
                    });
                }, 500);
            }, false);
        }
        window.easySliders = [];
        // initialize easysliders
        sliders.each(function(el){
            var config = JSON.parse(el.readAttribute('data-config'));
            window.easySliders.push(new Easyslider(el.id, config));
            // make all slides visible
            el.select('.section').each(function(section){
                section.setStyle({display: ''});
            });
        });
    }

    // Nivo slider - start
    sliders = $$('.nivoSlider');
    if ((typeof jQuery != 'undefined')) {
        sliders.each(function(el){
            var config = JSON.parse(el.readAttribute('data-config'));
            jQuery(el).nivoSlider(config);
        });
    } else if (sliders.length > 0) {
        console.warn('Easyslide can not find jQuery for NivoSlider rendering.');
    }

    // initialize click on slides
    $$('.easyslide-link').each(function(el) {
        el.observe('click', function(e) {
            if (this.hasClassName('target-self')) {
                return true;
            }

            e.stop();
            var options = '';
            if (this.hasClassName('target-popup')) {
                options = 'width=600,height=400';
            }
            window.open(this.href, this.up().id, options);
        });
    });
});
