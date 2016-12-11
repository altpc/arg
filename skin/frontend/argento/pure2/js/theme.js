document.observe('dom:loaded', function() {
    var mobileMapping = {
        'toggle-search': {
            on: function() {
                $('search_mini_form').addClassName('shown');
            },
            off: function() {
                $('search_mini_form').removeClassName('shown');
            }
        }
    };
    for (var i in mobileMapping) {
        Argento.togglers.add(i, mobileMapping[i]);
    }

    enquire.register('(max-width: 768px)', {
        match: function () {
            Argento.Mover.move('#search_mini_form', '.header-content');
            Argento.Mover.move('.header-cart-wrapper', '.header-content');
        },
        unmatch: function () {
            Argento.Mover.restore('#search_mini_form');
            Argento.Mover.restore('.header-cart-wrapper');
        }
    });
});
