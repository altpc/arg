var ProLabelsTooltip = Class.create();
ProLabelsTooltip.prototype = {
    initialize: function() {
        this.prepareMarkup();
        this.addObservers();
    },

    prepareMarkup: function() {
        this.addStylesToHead();
        var self = this,
            tooltips = $$('ul.prolabels-content-labels li span.tooltip-label');
        tooltips.each(function(tooltip){
            labelWidth = 60;
            tooltipMargin = '-' + labelWidth + 'px';
            tooltip.setStyle(self.styles);
            tooltip.setStyle({marginLeft: tooltipMargin});
        });
    },

    getConfig: function() {
        var config = false;
        $$('.prolabels-content-wrapper').each(function(element){
            var attrName = 'data-tooltip-config';
            if (element.hasAttribute(attrName)) {
                config = JSON.parse(element.getAttribute(attrName));
            }
            throw $break;
        });
        return config;
    },

    addObservers: function() {
        var self = this;
        ["quickshopping:previewloaded", "ajaxlayerednavigation:ready",
         "AjaxPro:onSuccess:after"].map(
            function(eventName){
                document.observe(eventName, self.prepareMarkup.bind(self));
            }
        );
    },

    addStylesToHead: function() {
        if ( (typeof this.styles === 'undefined') || !this.styles) {
            this.styles = this.getConfig();
            if (!this.styles) {
                return;
            }
        } else {
            return;
        }
        var tooltipMargin = '',
            labelWidth = '',
            aHoverCss = '.tt-wrapper li a:hover span.tooltip-label{background-color:'+this.styles.background+';}',
            spanAfterCss = '.tt-wrapper li a span.tooltip-label:after{border-top: 9px solid '+this.styles.background+';}',
            tooltipStyle = aHoverCss + spanAfterCss;
        var style = document.createElement('style');
        style.setAttribute('type', 'text/css');
        if (style.styleSheet) {
            style.styleSheet.cssText = tooltipStyle;
        } else {
            style.appendChild(document.createTextNode(tooltipStyle));
        }
        document.getElementsByTagName('head')[0].appendChild(style);
    }
};

document.observe("dom:loaded", function(){
    window.prolabelsTooltip = new ProLabelsTooltip();
});
