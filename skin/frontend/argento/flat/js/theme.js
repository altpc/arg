/* Search form scripts */
function activateSearchField(field, form, emtyText, event) {
    if (form.hasClassName('shown')) {
        return true;
    }

    form.addClassName('shown');
    field.addClassName('shown');
    field.focus();

    if (field.value && field.value != emtyText) {
        Event.stop(event);
        // http://stackoverflow.com/questions/13071106/set-caret-to-end-of-textbox-on-focus/13071184#13071184
        setTimeout(function() {
            if (field.createTextRange) {
                var r = field.createTextRange();
                r.collapse(true);
                r.moveEnd("character", field.value.length);
                r.moveStart("character", field.value.length);
                r.select();
            } else {
                field.selectionStart = field.selectionEnd = field.value.length;
            }
        }.bind(this), 13);
        return false;
    } else if (field.value == emtyText || field.value === '') {
        field.setValue('');
    }
    return true;
}
function deactivateSearchField(field, form) {
    field.removeClassName('shown');
    form.removeClassName('shown');
}

document.observe('dom:loaded', function() {
    if (typeof AjaxsearchAutocomplete == 'undefined') {
        Varien.searchForm.prototype.submit = Varien.searchForm.prototype.submit.wrap(function(o, event) {
            if (!activateSearchField(this.field, this.form, this.emptyText, event)) {
                return false;
            }
            o(event);
        });

        var formId = 'search_mini_form';
        var config = {};
        if ($(formId).hasAttribute('data-config')) {
            var config = JSON.parse($(formId).getAttribute('data-config'));
        }
        window.searchForm = new Varien.searchForm(formId, 'search', config.searchtext);
        searchForm.initAutocomplete(config.suggestUrl, 'search_autocomplete');

        var close = $$('.form-search .search-close').first();
        if (close) {
            close.observe('click', function() {
                deactivateSearchField(searchForm.field, searchForm.field.form);
            });
        }

    }
});
/* End of search form scripts */

document.observe('dom:loaded', function() {
    var mobileMapping = {
        'toggle-search': {
            on: function() {
                $$('.btn-search').each(function(el){
                    el.simulate('click');
                    throw $break;
                });
            },
            off: function() {
                $$('.search-close').each(function(el){
                    el.simulate('click');
                    throw $break;
                });
            }
        }
    };
    for (var i in mobileMapping) {
        Argento.togglers.add(i, mobileMapping[i]);
    }

    $$('.product-collateral > div').each(function(el, i) {
        el.addClassName('jumbotron');
        if (i > 0) {
            el.insert({
                top: '<div class="stub"></div>'
            });
        }
    });

    // language switcher
    $$('#select-language').each(function(el){
        new Chosen(el, {
            disable_search_threshold: 10,
            search_contains: true,
            width: 'auto'
        });
    });
});
