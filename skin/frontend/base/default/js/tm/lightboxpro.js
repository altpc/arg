;(function (){

function getDataset(elem){
    if (typeof elem.dataset !== 'undefined') {
        return elem.dataset;
    }
    // fallback for zombie browsers - IE8
    var dataset = {};
    for (var i = 0; i < elem.attributes.length; i++) {
        if (elem.attributes[i].name.substring(0,5) == 'data-') {
            var key = elem.attributes[i].name.substring(5).camelize();
            dataset[key] = elem.attributes[i].value
        }
    }
    return dataset
}

function valueToType(value) {
    if (value == "true") { return true };
    if (value == "false") { return false };
    if (!isNaN(value)) { return parseFloat(value) };
    if (value.substring(0,1) == '{' && value.substring(value.length-1) == '}'){
        var obj = JSON.parse(value.split('\'').join('"'));
        for(var key in obj){
            obj[key] = valueToType(obj[key])
        }
        return obj
    }
    return value
}

function getUniqueId(prefix){
    var id=Math.floor((1+Math.random())*0x10000).toString(16).substring(1);
    return prefix+id;
}

var LightboxPro = Class.create();
LightboxPro.prototype = {
    initialize: function(){
        this.skipTextTranslation = [
            'cssDirection',
            'creditsText',
            'creditsTitle'
        ];
        this.selector = {
            config: '.lightbox-highslide-config',
            html: '.lightbox-html', // selector for Html Widget
            image: '.lightbox-single-image', // selector for Image Widget
            gallery: '.highslide-gallery', // selector for Gallery Widget
            slideshow: '.highslide-gallery .lightbox-main-image',
            thumbs: '.lightbox-image'
        };
        this.onClick = {
            image: 'return hs.expand('
                    + 'this, lightBox.getCustomConfigs(this.idHs)'
                    + ')',
            html:  'return hs.htmlExpand('
                    + 'this, lightBox.getCustomConfigs(this.idHs)'
                    + ')'
            };
        this.customConfigs = {}
    },

    getInstance: function(instanceId){
        if (typeof instanceId === 'undefined') { instanceId = 0 }
        return $$(this.selector.gallery,
                this.selector.html,
                this.selector.image
            )[instanceId]
    },


    loadCofig: function(){
        var elemWithConfig = $$(this.selector.config)[0];
        if (!elemWithConfig) {
            // HS configuration not found
            ['expandCursor', 'outlineType','restoreCursor'].
                map(function(v){hs[v]=null});
            return false;
        }
        //default HighSlide settings
        hs.allowWidthReduction = true;
        hs.showCredits = false;
        // settings from configuration
        var dataset = getDataset(elemWithConfig);
        for (var key in dataset) {
            hs[key] = valueToType(dataset[key]);
        }
        // prepare close button for popup
        if (hs.closeButtonEnabled) {
            hs.registerOverlay({
                html: '<div class="closebutton" onclick="return hs.close(this)" title="Close"></div>',
                position: 'top right',
                useOnHtml: false,
                fade: 2 // fading the semi-transparent overlay looks bad in IE
            });
        }
        // highslide texts (translations)
        for (var key in hs.lang) {
            if (this.skipTextTranslation.indexOf(key) == -1) {
                hs.lang[key] = Translator.translate(hs.lang[key]);
            }
        }
        return true
    },

    initGallery: function(){
        var self = this;
        $$(this.selector.slideshow).each(function(el){
            el.idHs = getUniqueId('gallery-');
            el.thumbnails = el.up(self.selector.gallery)
                .select(self.selector.thumbs)
                .each(function(th){
                    th.idHs = el.idHs;
                });
            var dataset = getDataset(el);
            var slideShow = {},
                customConfigs = {};
            slideShow.slideshowGroup = el.idHs;
            for (var key in dataset) {
                if (key != 'customConfigs') {
                    slideShow[key] = valueToType(dataset[key]);
                } else {
                    customConfigs = valueToType(dataset.customConfigs);
                }
            }
            window.hs.addSlideshow(slideShow);
            customConfigs.slideshowGroup = el.idHs;
            customConfigs.transitions = ['expand', 'crossfade'];
            self.setCustomConfigs(el.idHs, customConfigs);
        });
        return this
    },

    initSingleElementWidgets: function () {
        var self = this;
        $$(this.selector.html, self.selector.image).each(function(el){
            el.elementHs = el.down('a');
            el.elementHs.idHs = getUniqueId('widget-');
            var dataset = getDataset(el.elementHs);
            var customConfigs = {};
            if (dataset.customConfigs) {
                customConfigs = valueToType(dataset.customConfigs);
            }
            customConfigs.slideshowGroup = el.elementHs.idHs;
            self.setCustomConfigs(el.elementHs.idHs, customConfigs);
        });
        return this
    },

    setCustomConfigs: function(key, config){
        this.customConfigs[key] = config
    },

    getCustomConfigs: function(key){
        return this.customConfigs[key]
    },

    registerOnClick: function(){
        var self = this;
        // register clicks for galleries
        $$(self.selector.slideshow).each(function(sshow){
            sshow.setAttribute('onclick', self.onClick.image);
            sshow.thumbnails.each(function(th){
                if ("mainImage" in th) { th.mainImage = false };
                th.stopObserving('click');
                th.removeAttribute('onclick');
                if (th.href == sshow.href) {
                    th.mainImage = sshow;
                }
                if (th.mainImage) {
                    th.observe('click', function(event){
                        event.stop();
                        this.mainImage.click();
                    })
                } else {
                    th.setAttribute('onclick', self.onClick.image)
                }
            });
        });
        // register clicks for single element widgets
        ['image','html'].map(function(v){
            $$(self.selector[v]).each(function(e){
                e.elementHs.setAttribute('onclick', self.onClick[v]);
            });
        });
        return this
    },

    integrateMageConfiguralSwatches: function(){
        // method implements integration LightboxPro and Magento ConfigSwatches
        // !! Theme should work with colorswatches without lightboxpro first
        if ('undefined' === typeof ProductMediaManager) {
            return this
        }
        ProductMediaManager.createZoom = ProductMediaManager.createZoom.wrap(
            function(original, image){
                original(image);
                var img = $j('.main-image img');
                // prevent image size increasing
                if (!img.hasClass('resized') && img.height()) {
                    img.addClass('resized').css({
                        'max-height': img.height()
                    });
                }
                $j('.main-image').attr('href', image.attr('src'));
                var srcset = img.attr('srcset'),
                    newSrc = image.attr('src');
                    img.attr('src', newSrc);
                if (srcset) {
                    if (image.attr('srcset')) {
                        img.attr('srcset', image.attr('srcset'));
                    } else {
                        img.removeAttr('srcset');
                    }
                }
                lightBox.registerOnClick()
            });
        ProductMediaManager.swapImage = ProductMediaManager.swapImage.wrap(
            function(original, targetImage){
                original(targetImage);
                var imageGallery = $j('.highslide-gallery');
                if (!targetImage[0].complete){
                    imageGallery.addClass('loading');
                    imagesLoaded(targetImage, function() {
                        imageGallery.removeClass('loading');
                    });
                }
            });
        return this
    }

}

window.lightBox = new LightboxPro();

})();

document.observe("dom:loaded", function () {
    // LightboxPro initialization
    if (lightBox.loadCofig()) {

        lightBox.initGallery().initSingleElementWidgets().registerOnClick().
            integrateMageConfiguralSwatches();

        // workaround for jumping thumbnails in firefox
        if(Prototype.Browser.Gecko) {
            hs.Expander.prototype.positionOverlay =
                hs.Expander.prototype.positionOverlay.wrap(
                function (callOriginal, overlay) {
                    if (overlay.hsId == 'thumbstrip') {
                        setTimeout(function(callFunction, node){
                            callFunction(node);
                            exp = hs.getExpander(node);
                            exp.slideshow.thumbstrip.selectThumb();
                        }, 13, callOriginal, overlay);
                    } else {
                        callOriginal(overlay);
                    }
                }
            )
        };

    };
});
