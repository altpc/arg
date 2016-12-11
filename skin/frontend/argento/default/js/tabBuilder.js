/**
 * Do not remove or change this notice.
 * TabBuilder - Prototype and Scriptaculous tabs plug-in
 * Copyright (c) 2008 - 2009 Templates Master www.templates-master.com
 *
 * @author Templates Master www.templates-master.com
 * @version 1.1
 */

var TabBuilder = Class.create();
TabBuilder.prototype = {
    config:
    {
        effect: 'none',
        duration: 300,
        tabContainer: '.tab-container',
        tab: '.block-highlight'
    },

    initialize: function(settings)
    {
        Object.extend(this.config, settings);
        $$(this.config.tabContainer).each(function(el) {
            if ($(el).select(this.config.tab).length) {
                this.buildTabs(el)
                    .setActiveTab(el, 0)
                    .addObservers(el);
            }
        }.bind(this));
    },

    buildTabs: function(container)
    {
        var tabs = new Element('ol').addClassName('tabs'),
            tabsContent = new Element('div').addClassName('content');

        container.insert({'bottom': tabs});
        tabs.insert({'after': tabsContent});
        var wrapper = new Element('div', {'class': 'tabs-wrapper'});
        tabs.wrap(wrapper);

        $(container).select(this.config.tab).each(function(el) {
            var tabTitle = $(el).select('.block-title')[0].outerHTML;
            tabs.insert({'bottom': '<li>' + tabTitle + '</li>'});

            el.addClassName('tab');
            tabsContent.insert({'bottom': el});
        });
        tabs.down('li:first-child').addClassName('first');
        tabs.down('li:last-child').addClassName('last');
        return this;
    },

    setActiveTab: function(container, index, hideInactive)
    {
        this._switchTabDisplay(container, index, hideInactive);
        return this;
    },

    addObservers: function(container)
    {
        var that = this;
        $(container).select('.tabs li').each(function(el, index) {
            el.observe('click', function() {
                that.setActiveTab(container, index);
            });
            el.observe('mouseover', function(el) {
                $(this).addClassName('over');
            });
            el.observe('mouseout', function(el) {
                $(this).removeClassName('over');
            });
        });
        $(container).select('.tab .block-title').each(function(el, index) {
            el.observe('click', function() {
                that.setActiveTab(container, index, false);
            });
            el.observe('mouseover', function(el) {
                $(this).addClassName('over');
            });
            el.observe('mouseout', function(el) {
                $(this).removeClassName('over');
            });
        });
        return this;
    },

    _switchTabDisplay: function(container, index, hideInactive)
    {
        if (undefined === hideInactive) {
            hideInactive = true;
        }
        if (hideInactive) {
            $(container).select('.tabs li, .content .tab').invoke('removeClassName', 'active');
        }
        if (!hideInactive && $(container).select('.tabs li')[index].hasClassName('active')) {
            $(container).select('.tabs li')[index].removeClassName('active');
            $(container).select('.content .tab')[index].removeClassName('active');
        } else {
            $(container).select('.tabs li')[index].addClassName('active');
            $(container).select('.content .tab')[index].addClassName('active');
        }
        // $(container).select('.content .tab').invoke('setStyle', {'display': 'none'});
        // $(container).select('.content .tab')[index].setStyle({'display': 'block'});
    }
};
