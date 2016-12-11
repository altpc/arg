var Argento = {};

document.observe('dom:loaded', function() {
    if ('ontouchstart' in document.documentElement) {
        $(document.body).addClassName('touchable touch');
    } else {
        $(document.body).addClassName('untouchable notouch');
    }
});

var MobileNavigation = Class.create();
MobileNavigation.prototype = {
    status: true,
    config: {
        container: '#nav',
        items    : 'li.parent',
        duration : 0.2
    },

    initialize: function(options) {
        Object.extend(this.config, options || {});
        this.container = $$(this.config.container)[0];
        if (!this.container) {
//            this.config.container = '.nav-container .navpro';
//            this.container = $$(this.config.container)[0];
            return;
        }

        var self = this;
        this.container.select(this.config.items).each(function(el) {
            el.insert({
                bottom: '<a href="#" class="toggle"></a>'
            });
        });
        this.container.select('.toggle').each(function(el) {
            el.observe('click', function(e) {
                e.stop();
                var dropdown = el.previous('ul') || el.previous('.nav-dropdown');
                self.toggle(dropdown);
            });
        });
    },

    toggleAll: function(status) {
        var self    = this;
        status      = status || !this.status;
        this.status = status;

        this.container.select('ul').each(function(el) {
            if (status) {
                self.show(el);
            } else {
                self.hide(el);
            }
        });
    },

    toggle: function(el) {
        if (el.hasClassName('shown')) {
            this.hide(el);
        } else {
            this.show(el);
        }
    },

    show: function(el) {
        el.previous('.toggle') && el.previous('.toggle').addClassName('active');
        el.next('.toggle') && el.next('.toggle').addClassName('active');
        el.addClassName('shown');
    },

    hide: function(el) {
        el.previous('.toggle') && el.previous('.toggle').removeClassName('active');
        el.next('.toggle') && el.next('.toggle').removeClassName('active');
        el.removeClassName('shown');
    },

    isVisible: function(el) {
        return el.visible();
    }
};

var Redirector = Class.create();
Redirector.prototype = {
    config: {
        url: window.location.href,
        query: {}
    },

    initialize: function(options) {
        Object.extend(this.config, options || {});

        var queryIndex = this.config.url.indexOf('?');
        if (queryIndex !== -1) {
            var queryArr = this.config.url.substr(queryIndex + 1).split('&'),
                query = {};
            queryArr.each(function(item) {
                var keyValue = item.split('=');
                query[keyValue[0]] = keyValue[1];
            });
            this.config.query = query;
            this.config.url = this.config.url.substr(0, queryIndex);
        }
    },

    redirect: function(params, reset) {
        reset = reset || false;

        if (!reset) {
            params = Object.extend(this.config.query, params);
        }
        var query = '';
        for (var key in params) {
            query += key + '=' + params[key] + '&';
        }
        if (query) {
            query = '?' + query.substr(0, query.length - 1);
        }

        window.location = this.config.url + query;
    }
};

var BlockToggler = Class.create();
BlockToggler.prototype = {
    initialize: function(options) {
        this.config = Object.extend({
            block   : '.block',
            header  : ' > .block-title',
            content : ' > .block-content',
            duration: 0.2,
            state   : 'collapsed',
            headerToggler: 1,
            useEffect: true,
            maxWidth: 0
        }, options || {});

        this.updateLayout();
        this.addObservers();

        var self = this;
        if ('collapsed' === this.config.state) {
            $$(this.config.block).each(function(el) {
                self.collapse(el);
            });
        } else {
            $$(this.config.block).each(function(el) {
                self.expand(el);
            });
        }
    },

    updateLayout: function() {
        $$(this.config.block + this.config.header).each(function(el) {
            el.addClassName('block-toggler');
            el.setStyle({
                position: 'relative'
            });
            el.insert({
                bottom: '<a href="#" class="toggle"></a>'
            });
        });
    },

    addObservers: function() {
        var self = this;
        $$(this.config.block + this.config.header + ' .toggle').each(function(el) {
            el.observe('click', function(e) {
                e.stop();
                self.toggle(el.up(self.config.block));
            })
        });
        if (this.config.headerToggler) {
            $$(this.config.block + this.config.header).each(function(el) {
                el.observe('click', function(e) {
                    e.stop();
                    self.toggle(el.up(self.config.block));
                })
            });
        }
    },

    toggle: function(el) {
        if (document.viewport.getWidth() > this.config.maxWidth) {
            return;
        }
        if (el.hasClassName('collapsed')) {
            this.expand(el);
        } else {
            this.collapse(el);
        }
    },

    collapse: function(el) {
        el.addClassName('collapsed');
        // el.up(self.config.header).removeClassName('active');
        if (this.config.useEffect) {
            new Effect.BlindUp(el.down(this.config.content), {
                duration: this.config.duration
            });
        }
    },

    expand: function(el) {
        el.removeClassName('collapsed');
        // el.up(self.config.header).addClassName('active');
        if (this.config.useEffect) {
            new Effect.BlindDown(el.down(this.config.content), {
                duration: this.config.duration
            });
        }
    }
};

/* Floatbar navigation for mobile devices */
var BottomNavbar = function() {
    var _navbar = new Element('div', {
            'class': 'floatbar floatbar-bottom navbar-bottom'
        }),
        _headroom;

    document.observe('dom:loaded', function() {
        document.body.insert({
            bottom: _navbar
        });
        _headroom = new Headroom(_navbar, {
            tolerance: 10,
            offset   : 0,
            classes  : {
                pinned  : "floatbar-shown",
                unpinned: "floatbar-bottom-hidden"
            }
        });
        _headroom.init();
        document.body.fire('bottomnavbar:init');
    });

    function _updateFloatbarLayout() {
        var titles = _navbar.select('.section'),
            width  = 100 / titles.length + '%';

        titles.each(function(title) {
            title.setStyle({
                width: width
            });
        });
    }

    function _prepareAnimatedPopup(selector) {
        var popup = $$(selector).first();
        if (!popup) {
            return;
        }
        // prepare popup markup
        popup.writeAttribute('data-selector', selector);
        popup.addClassName('floatbar-popup floatbar-popup-initial');
        popup.insert('<a href="javascript:void(0)" class="close floatbar-popup-close-icon">x</a>');

        // add event listeners
        popup.select('.close').each(function(el) {
            el.observe('click', function(e) {
                var popup = el.up('.floatbar-popup').readAttribute('data-selector');
                _hidePopup($$(popup).first());
            }.bind(this));
        }.bind(this));

        var content = popup.down('.block-content');
        if (!content) {
            return;
        }

        // make content scrollable inside visible region
        content.wrap('div', {'class': 'iscroll'});
        _updatePopupHeight(popup);

        // activate iscroll for touch devices
        if (document.body.hasClassName('touch')) {
            var iscroll = popup.down('.iscroll');
            new IScroll(iscroll, {
                click: true,
                tap  : true,
                bindToWrapper: true
            });
            // fix highligh effect for tapped links
            iscroll.select('a').each(function(el) {
                el.observe('tap', function(e) {
                    this.addClassName('highlight');
                    setTimeout(this.removeClassName.bind(this, 'highlight'), 250);
                });
            });
        }
    }

    function _showPopup(popup) {
        $$('.floatbar-popup').invoke('removeClassName', 'shown');
        $$('.floatbar-popup').invoke('addClassName', 'collapsed');

        popup.removeClassName('collapsed');
        popup.removeClassName('floatbar-popup-initial');
        popup.addClassName('shown');
        _headroom.offset = 9999; // disable headroom autohide
    }

    function _hidePopup(popup) {
        popup.addClassName('collapsed');
        popup.removeClassName('shown');
        _headroom.offset = 0;  // revert headroom autohide
    }

    function _updatePopupHeight(popup) {
        var iscroll = popup.down('.iscroll');
        if (!iscroll) {
            return;
        }

        iscroll.setStyle({
            height: 'auto'
        });

        var blockHeight = popup.getHeight(),
            titleHeight = popup.down('.block-title') ?
                popup.down('.block-title').getHeight() : 0;

        iscroll.setStyle({
            height: blockHeight - titleHeight + 'px'
        });
    }
    function _updatePopupsLayout() {
        $$('.floatbar-popup').each(function(popup) {
            _updatePopupHeight(popup);
        });
    }
    if ('addEventListener' in window) {
        window.addEventListener('resize', _updatePopupsLayout.bind(this));
    } else {
        window.attachEvent('onresize', _updatePopupsLayout.bind(this));
    }

    return {
        add: function(titleSelector, blockSelector) {
            var popup = $$(blockSelector).first();
            if (!popup) {
                return;
            }

            var title = $$(titleSelector).first();
            if (!title) {
                title = titleSelector;
            } else {
                title = title.innerHTML;
            }

            var titleBlock = new Element('div', {
                'class'     : 'section',
                'data-block': blockSelector
            });
            _navbar.insert({
                bottom: titleBlock
            });
            titleBlock.insert(title);
            titleBlock.observe('click', this.toggle.bind(this, titleBlock.readAttribute('data-block')));

            _updateFloatbarLayout();
        },
        toggle: function(blockSelector) {
            var block = $$(blockSelector).first(),
                delayFlag = false;
            if (!block) {
                return;
            }
            if (!block.hasClassName('floatbar-popup')) {
                _prepareAnimatedPopup(blockSelector);
                delayFlag = true;
            }

            if (!block.hasClassName('shown')) {
                if (delayFlag) {
                    _showPopup.delay(0.1, block);
                } else {
                    _showPopup(block);
                }
            } else {
                _hidePopup(block);
            }
        }
    };
}();

var CollapsedElement = function() {
    var _options = {
        maxHeight: 200,
        height: 100
    };

    return {
        init: function(selector, options) {
            var elements = $$(selector);
            Object.extend(_options, options || {});

            elements.each(function(el) {
                if (el.getHeight() <= _options.maxHeight) {
                    return;
                }

                if (_options.className) {
                    el.addClassName(_options.className);
                } else {
                    el.setStyle({
                        overflow: 'hidden',
                        height: _options.height + 'px'
                    });
                }
                el.insert({
                    after: '<span class="fade-box-toggle">' + Translator.translate('More').stripTags() + '</span>'
                });
            });

            this.addObservers();
        },
        addObservers: function() {
            $$('.fade-box-toggle').each(function(el) {
                el.stopObserving('click');
                el.observe('click', function() {
                    if (_options.className) {
                        this.previous().removeClassName(_options.className);
                    } else {
                        this.previous().setStyle({
                            height: 'auto'
                        });
                    }
                    this.remove();
                });
            });
        }
    };
};

var MobileTogglers = function() {
    var _container = $('mobile-togglers'),
        _rules = {
            'toggle-menu': {
                on: function() {
                    var el = $(_container.down('.toggle-menu').readAttribute('data-menu'));
                    el.addClassName('shown');
                },
                off: function() {
                    var el = $(_container.down('.toggle-menu').readAttribute('data-menu'));
                    el.removeClassName('shown');
                }
            },
            'toggle-quick-links': {
                on: function() {
                    $$('.quick-links')[0].addClassName('shown');
                },
                off: function() {
                    $$('.quick-links')[0].removeClassName('shown');
                }
            },
            // 'toggle-search': {
            //     on: function() {
            //         $$('.btn-search')[0].simulate('click');
            //     },
            //     off: function() {
            //         $$('.search-close')[0].simulate('click');
            //     }
            // },
            'toggle-cart': {
                on: function() {
                    $('header-cart-content').show();
                },
                off: function() {
                    $('header-cart-content').hide();
                }
            }
        };

    function _addObservers(selector, handlers) {
        if (!_container) {
            return;
        }
        _container.select('.' + selector).each(function(el) {
            el.observe('click', function(e) {
                if (el.hasClassName('active')) {
                    el.removeClassName('active');
                    _container.removeClassName('active');
                    handlers.off();
                } else {
                    for (var i in _rules) {
                        if (el.hasClassName(i)) {
                            continue;
                        }
                        _container.select('.' + i).invoke('removeClassName', 'active');
                        _rules[i].off();
                    }

                    el.addClassName('active');
                    _container.addClassName('active');
                    handlers.on();
                }
            });
        }.bind(this));
    }

    for (var i in _rules) {
        _addObservers(i, _rules[i]);
    }

    return {
        add: function(selector, handlers) {
            _rules[selector] = handlers;
            _addObservers(selector, handlers);
        }
    };
};

/**
 * Moves element to destination and restore
 * it's previous position when needed
 */
Argento.Mover = {
    move: function(source, destination) {
        source = $$(source)[0];
        destination = $$(destination)[0];
        if (source && destination) {
            var prev = source.previous(':not([data-argento-mover-moved])');
            if (prev && prev.classNames().toString()) {
                source.writeAttribute(
                    'data-argento-mover-previous',
                    '.' + prev.classNames().toString().replace(' ', '.')
                );
            } else {
                var parent = source.up();
                source.writeAttribute(
                    'data-argento-mover-parent',
                    '.' + parent.classNames().toString().replace(' ', '.')
                );
            }
            source.writeAttribute('data-argento-mover-moved', 1);
            destination.insert(source);
        }
    },
    restore: function(source) {
        source = $$(source).first();
        if (!source) {
            return;
        }

        var destination = source.readAttribute('data-argento-mover-previous');
        if (destination) {
            $$(destination)[0].insert({
                after: source
            });
            return;
        }

        destination = source.readAttribute('data-argento-mover-parent');
        if (destination) {
            $$(destination)[0].insert({
                top: source
            });
        }
    }
};

Argento.StickyKit = {
    stick: function(selector, offset) {
        $$(selector).each(function(el) {
            var sticky = new StickInParent({
                recalc_every: 5,
                offset_top: offset
            });
            sticky.stick(el);
        });
    },
    unstick: function(selector) {
        $$(selector).each(function(el) {
            el.fire('sticky_kit:detach');
        });
    }
};

document.observe('dom:loaded', function() {
    Argento.togglers = MobileTogglers();

    enquire.register('(max-width: 480px)', {
        match: function () {
            Argento.Mover.move('.top-toolbar .quick-links', '.header-content');
        },
        unmatch: function () {
            Argento.Mover.restore('.header-content .quick-links');
        }
    });
});

/**
 * Pretty categories dropdown for Ajax Search
 */
document.observe('dom:loaded', function() {
    var search = $('search_mini_form');
    if (typeof search === 'undefined' || !search) { return; }
    if (!search.hasAttribute('data-chosen-no-result')) { return; }
    Prototype.Browser.IE6 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 6;
    Prototype.Browser.IE7 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 7;
    if (!Prototype.Browser.IE6 && !Prototype.Browser.IE7) {
        new Chosen(search.down('.ajaxsearch-category-select'), {
            allow_single_deselect: true,
            no_results_text: search.readAttribute('data-chosen-no-result')
        });
    }
});
