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
            Argento.Mover.move('.nav-container', '.header-content');
        },
        unmatch: function () {
            Argento.Mover.restore('.nav-container');
        }
    });
});
