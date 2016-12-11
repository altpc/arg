/*
 *
 *  Ajax Autocomplete for Prototype, version 1.0.4
 *  (c) 2010 Tomas Kirda
 *
 *  Ajax Autocomplete for Prototype is freely distributable under the terms of an MIT-style license.
 *  For details, see the web site: http://www.devbridge.com/projects/autocomplete/
 *
 */

var AjaxsearchAutocomplete = function(el, options){
  this.el = $(el);
  this.elico = $(el+'ajaxico');
  this.id = this.el.identify();
  this.el.setAttribute('autocomplete','off');
  this.form = this.el.up('form');
  this.categorySelect = this.form.down('.ajaxsearch-category-select');
  this.submitButton = this.form.down('button.btn-search') || this.form.down('button.button');
  this.currentCategory = '';
  this.suggestions = [];
  this.data = [];
  this.badQueries = [];
  this.selectedIndex = -1;
  this.currentValue = this.el.value;
  this.intervalId = 0;
  this.cachedResponse = [];
  this.instanceId = null;
  this.onChangeInterval = null;
  this.ignoreValueChange = false;
  this.serviceUrl = options.serviceUrl;
  this.options = {
    autoSubmit:false,
    minChars:1,
    enableloader: 0,
    maxHeight:300,
    deferRequestBy:500,
    width:0,
    searchtext:'',
    folded: false,
    baseUrl:'',
    secureUrl:'',
    container:null,
    loaderOffset: {
        left: 0,
        top: 0
    },
    fullWidthMode: true // indicate that suggestions width should include the category combobox width too
  };
  if(options){ Object.extend(this.options, options); }
  if(AjaxsearchAutocomplete.isDomLoaded){
    this.initialize();
  }else{
    Event.observe(document, 'dom:loaded', this.initialize.bind(this), false);
  }
};

AjaxsearchAutocomplete.instances = [];
AjaxsearchAutocomplete.isDomLoaded = false;

AjaxsearchAutocomplete.getInstance = function(id){
  var instances = AjaxsearchAutocomplete.instances;
  var i = instances.length;
  while(i--){ if(instances[i].id === id){ return instances[i]; }}
};

AjaxsearchAutocomplete.highlight = function(value, re){
  return value.replace(re, function(match){ return '<strong>' + match + '<\/strong>'; });
};

AjaxsearchAutocomplete.prototype = {

  killerFn: null,

  initialize: function() {
    var me = this;
    this.killerFn = function(e) {
      if (!$(Event.element(e)).up('.autocomplete')) {
        me.killSuggestions();
        me.disableKillerFn();
      }
    } .bindAsEventListener(this);

    if (!this.options.width) { this.options.width = this.el.getWidth(); }

    var div = new Element('div', { style: 'position:absolute;display:none;' }),
        style = 'display:none;';
    if (!isNaN(this.options.width)) {
      style += 'width:' + this.options.width + 'px;';
    }
    div.update('<div class="autocomplete-w1"><div class="autocomplete-w2"><div class="autocomplete" id="Autocomplete_' + this.id + '" style="' + style + '"></div></div></div>');

    this.options.container = $(this.options.container);
    if (this.options.container) {
      this.options.container.appendChild(div);
    } else {
      document.body.appendChild(div);
    }

    this.mainContainerId = div.identify();
    this.container = $('Autocomplete_' + this.id);
    this.fixPosition();

    Event.observe(this.el, window.opera ? 'keypress':'keydown', this.onKeyPress.bind(this));
    Event.observe(this.el, 'keyup', this.onKeyUp.bind(this));
    Event.observe(this.el, 'paste', this.onPaste.bind(this));
    Event.observe(this.el, 'cut', this.onPaste.bind(this));
    Event.observe(this.el, 'blur', this.enableKillerFn.bind(this));
    Event.observe(this.el, 'focus', this.fixPosition.bind(this));
    Event.observe(this.el, 'click', this.fixText.bind(this));
    Event.observe(this.el, 'contextmenu', this.fixText.bind(this));
    Event.observe(this.el, 'blur', this.fixText.bind(this));
    if (this.submitButton) {
        Event.observe(this.submitButton, 'click', this.onButtonClick.bind(this));
    }

    if (this.categorySelect) {
        Event.observe(this.categorySelect, 'change', this.onCategoryChange.bind(this));

        if (-1 === this.el.getStyle("width").indexOf('%')) {
            var elWidth = (this.el.getStyle("width") ?
                  parseInt(this.el.getStyle("width"), 10) : this.el.getWidth()),
              selectWidth = (this.categorySelect.getStyle("width") ?
                  parseInt(this.categorySelect.getStyle("width"), 10) : this.categorySelect.getWidth());

            this.el.setStyle({
                width: elWidth - selectWidth + 'px'
            });
        }
    }

    this.container.setStyle({ maxHeight: this.options.maxHeight + 'px' });
    this.instanceId = AjaxsearchAutocomplete.instances.push(this) - 1;

    if (this.options.folded) {
      var close = this.form.down('.close');
      if (close) {
          close.observe('click', function(event) {
              Event.stop(event);
              this.deactivateSearchField();
          }.bind(this));
      }
    }
  },

  fixPosition: function() {
    var offset = this.options.container ? this.positionedOffset(this.el) : this.el.cumulativeOffset(),
        left = offset.left;
    if (this.categorySelect && this.options.fullWidthMode) {
        left -= (this.categorySelect.getWidth() + 4);
    }
    $(this.mainContainerId).setStyle({
        top : (offset.top + this.el.getHeight()) + 'px',
        left: left + 'px'
    });
    if (isNaN(this.options.width)) {
        var elWidth = this.el.getStyle("width");
        if (parseInt(elWidth) && (-1 === elWidth.indexOf('%'))) {
            elWidth = parseInt(elWidth, 10);
        } else {
            elWidth = this.el.getWidth();
        }
        if (this.categorySelect && this.options.fullWidthMode) {
          var selectWidth = (this.categorySelect.getStyle("width") ?
                parseInt(this.categorySelect.getStyle("width"), 10) : this.categorySelect.getWidth());
          elWidth += selectWidth;
        }
        this.container.setStyle({
            width: elWidth + 'px'
        });
    }
  },

  fixText: function() {
    if (document.activeElement) { // modern browsers. Prevent filling the dummy text on double click
        var isFocused = (this.el.id === document.activeElement.id);
        if (isFocused && (this.el.value === this.options.searchtext)) {
            this.el.value = '';
        } else if (!isFocused && this.el.value.length === 0) {
            this.el.value = this.options.searchtext;
        }
        return;
    }

    if(this.el.value == this.options.searchtext){
        this.el.value='';
    } else if(this.el.value.length === 0) {
        this.el.value = this.options.searchtext;
    } else {
        return;
    }
  },

  enableKillerFn: function() {
    Event.observe(document.body, 'click', this.killerFn);
  },

  disableKillerFn: function() {
    Event.stopObserving(document.body, 'click', this.killerFn);
  },

  killSuggestions: function() {
    this.stopKillSuggestions();
    this.intervalId = window.setInterval(function() { this.hide(); this.stopKillSuggestions(); } .bind(this), 300);
  },

  stopKillSuggestions: function() {
    window.clearInterval(this.intervalId);
  },

  onButtonClick: function(e) {
    if (this.options.folded && !this.activateSearchField(e)) {
        return false;
    }
    if (this.currentValue !== '' && this.currentValue !== this.options.searchtext) {
        this.hide();
        this.form.submit();
    }
  },

  activateSearchField: function(event) {
    var field = this.el;
    if (this.form.hasClassName('shown')) {
        return true;
    }

    this.form.addClassName('shown');
    this.el.focus();

    if (field.value && field.value != this.options.searchtext) {
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
    } else if (field.value == this.options.searchtext || field.value === '') {
        field.setValue('');
    }
    return true;
  },

  deactivateSearchField: function() {
      this.form.removeClassName('shown');
      this.hide();
  },

  onKeyPress: function(e) {
    // submit the form is enter was pressed
    if (e.keyCode === Event.KEY_RETURN) {
        if (this.el.getValue().length && this.selectedIndex === -1) {
            this.hide();
            this.form.submit();
            return;
        } else if (this.selectedIndex === -1) {
            Event.stop(e);
            return;
        }
    }

    if (!this.enabled) { return; }
    // return will exit the function
    // and event will not fire
    switch (e.keyCode) {
      case Event.KEY_ESC:
        this.el.value = this.currentValue;
        this.hide();
        break;
      case Event.KEY_TAB:
      case Event.KEY_RETURN:
        if (this.selectedIndex === -1) {
          this.hide();
          return;
        }
        var n = 0, i = 0, self = this;
        this.suggestions.each(function(value) {
            if (value.html) {
                n++;
            } else {
                i++;
                if (i >= self.selectedIndex) {
                    throw $break;
                }
            }
        });
        this.select(this.selectedIndex + n);
        if (e.keyCode === Event.KEY_TAB) { return; }
        break;
      case Event.KEY_UP:
        this.moveUp();
        break;
      case Event.KEY_DOWN:
        this.moveDown();
        break;
      default:
        return;
    }
    Event.stop(e);
  },

  onKeyUp: function(e) {
    switch (e.keyCode) {
      case Event.KEY_UP:
      case Event.KEY_DOWN:
        return;
    }
    clearInterval(this.onChangeInterval);
    if (this.currentValue !== this.el.value) {
      if (this.options.deferRequestBy > 0) {
        // Defer lookup in case when value changes very quickly:
        this.onChangeInterval = setInterval((function() {
          this.onValueChange();
        }).bind(this), this.options.deferRequestBy);
      } else {
        this.onValueChange();
      }
    }
  },
  onPaste: function(e) {
    this.onKeyUp.bind(this).delay(0.1, e);
  },

  onValueChange: function() {
    clearInterval(this.onChangeInterval);
    this.currentValue = this.el.value;
    this.selectedIndex = -1;
    if (this.ignoreValueChange) {
      this.ignoreValueChange = false;
      return;
    }
    if (this.currentValue === ''
        || this.currentValue.length < this.options.minChars
        || this.currentValue === this.options.searchtext) {

      this.hide();
    } else {
      this.getSuggestions();
    }
  },

  onCategoryChange: function() {
    clearInterval(this.onChangeInterval);
    this.currentCategory = this.categorySelect.getValue();
    this.selectedIndex = -1;
    if (this.ignoreValueChange) {
        this.ignoreValueChange = false;
        return;
    }
    if (this.currentValue === ''
        || this.currentValue.length < this.options.minChars
        || this.currentValue === this.options.searchtext) {

        this.hide();
    } else {
        this.getSuggestions();
    }
  },

  getSuggestions: function() {
    var cr = this.cachedResponse[this.currentValue + '_' + this.currentCategory];
    if (cr && Object.isArray(cr.suggestions)) {
        this.suggestions = cr.suggestions;
        this.data = cr.data;
        this.suggest();
    } else if (!this.isBadQuery(this.currentValue)) {
        this.showloader();
        var currentUrl = window.location.href;
        var isBaseUrl = (0 === currentUrl.indexOf(this.options.baseUrl));
        var isRequestBaseUrl = (0 === this.serviceUrl.indexOf(this.options.baseUrl));
        if (isBaseUrl && !isRequestBaseUrl) {
            this.serviceUrl = this.serviceUrl.replace(this.options.secureUrl, this.options.baseUrl);
        } else if (!isBaseUrl && isRequestBaseUrl) {
            this.serviceUrl = this.serviceUrl.replace(this.options.baseUrl, this.options.secureUrl);
        }

        var params = {
            q: this.currentValue
        };
        if (this.currentCategory) {
            params.category = this.currentCategory;
        }
        new Ajax.Request(this.serviceUrl, {
            parameters: params,
            onComplete: this.processResponse.bind(this),
            method    : 'get'
        });
    }
  },

  isBadQuery: function(q) {
    var i = this.badQueries.length;
    while (i--) {
      if (q.indexOf(this.badQueries[i]) === 0) { return true; }
    }
    return false;
  },

  hide: function() {
    this.enabled = false;
    this.selectedIndex = -1;
    $(this.mainContainerId).hide();
    this.container.hide();
  },

  suggest: function() {
    this.hideloader();
    if (this.suggestions.length === 0) {
      this.hide();
      return;
    }
    var content = [];
    var re = new XRegExp(
        XRegExp.split(
            this.currentValue, XRegExp("\\P{L}+")
        ).join('|'), 'gi'
    );

    var previousIsHtml = false, i = 0, n = 0;
    this.suggestions.each(function(value) {

        if (value.html) {
            n++;
            content.push(value.html);
            previousIsHtml = true;
            return;
        }

        var image = value.image ? '<img class="ajaxsearchimage" src="'  + value.image + '" srcset="' + value.srcset + '" alt="' + value.name + '">' : '';
        var description = value.description ?  '<br /><span class="ajaxsearchdescription">' + value.description + '</span>' : '';

        var p = '<p>';
        if (previousIsHtml) {
            previousIsHtml = false;
            p = '<p class="ajaxsearch-small">';
        }
        content.push(
            (this.selectedIndex === i ? '<div class="selected ajaxsearchtitle"' : '<div class="ajaxsearchtitle"'),
            ' title="', value.name,
                '" onclick="AjaxsearchAutocomplete.instances[', this.instanceId, '].select(', i + n, ');" onmouseover="AjaxsearchAutocomplete.instances[', this.instanceId, '].activate(', i, ');">',
            image,
            p,
                re ? AjaxsearchAutocomplete.highlight(value.name, re) : value.name,
                description,
            '</p>',
            '</div>'
        );
        i++;
    } .bind(this));
    this.enabled = true;
    this.fixPosition();
    $(this.mainContainerId).show();
    this.container.update(content.join('')).show();
  },

  processResponse: function(xhr) {
    var response;
    try {
      response = xhr.responseText.evalJSON();
      if (!Object.isArray(response.data)) { response.data = []; }
    } catch (err) { return; }
    this.cachedResponse[response.q + '_' + response.category] = response;
    if (response.suggestions.length === 0) { this.badQueries.push(response.q); }
    if (response.q === this.currentValue) {
      this.suggestions = response.suggestions;
      this.data = response.data;
      this.suggest();
    }
  },

  activate: function(index) {
    var divs = $(this.container).select('div');
    var activeItem;
    // Clear previous selection:
    if (this.selectedIndex !== -1
        && divs.length > this.selectedIndex
        && divs[this.selectedIndex]) {

      divs[this.selectedIndex].className = '';
    }
    this.selectedIndex = index;
    if (this.selectedIndex !== -1 && divs.length > this.selectedIndex) {
      activeItem = divs[this.selectedIndex];
      activeItem.className = 'selected';
    }
    return activeItem;
  },

  deactivate: function(div, index) {
    div.className = '';
    if (this.selectedIndex === index) { this.selectedIndex = -1; }
  },

  select: function(i) {
    var selectedValue = this.suggestions[i].name;
    if (selectedValue) {
      this.el.value = selectedValue;
      if (this.options.autoSubmit && this.el.form) {
        this.el.form.submit();
      }
      this.ignoreValueChange = true;
      this.hide();
      this.onSelect(i);
    }
  },

  moveUp: function() {

    if (this.selectedIndex === -1) { return; }
    if (this.selectedIndex === 0) {
      $(this.container).select('div')[0].className = '';
      this.selectedIndex = -1;
      this.el.value = this.currentValue;
      return;
    }
    this.adjustScroll(this.selectedIndex - 1);
  },

  moveDown: function() {
    if (this.selectedIndex === (this.suggestions.length - 1)) { return; }
    this.adjustScroll(this.selectedIndex + 1);
  },

  showloader: function() {
    if (this.options.enableloader == 1) {
        var elOffset = this.positionedOffset(this.el),
            iconSize = {
                width: 20,
                height: 20
            };

        this.elico.setStyle({
            display: 'block',
            left: Math.round(elOffset.left
                + this.el.getWidth()
                - iconSize.width - 2
                + this.options.loaderOffset.left) + 'px',
            top: Math.round(elOffset.top
                + this.el.getHeight() / 2
                - iconSize.height / 2
                + this.options.loaderOffset.top) + 'px'
        });
    }
  },

  hideloader: function() {
    if (this.options.enableloader == 1) {
        this.elico.setStyle({display: 'none'});
    }
  },

  adjustScroll: function(i) {
    var container = this.container;
    var activeItem = this.activate(i);
    if (!activeItem) {
        return;
    }
    var offsetTop = activeItem.offsetTop;
    var upperBound = container.scrollTop;
    var lowerBound = upperBound + this.options.maxHeight - 25;
    if (offsetTop < upperBound) {
      container.scrollTop = offsetTop;
    } else if (offsetTop > lowerBound) {
      container.scrollTop = offsetTop - this.options.maxHeight + 25;
    }
    var value = this.currentValue;
    if (activeItem.title) {
        value = activeItem.title;
    }
    this.el.value = value;
  },

  onSelect: function(i) {
    (this.options.onSelect || Prototype.emptyFunction)(this.suggestions[i], this.data[i]);
  },
  /* Use own implementation of positionedOffset because of bug in original */
  positionedOffset: function(element) {
    var valueT = 0, valueL = 0;
    do {
      valueT += element.offsetTop  || 0;
      valueL += element.offsetLeft || 0;
      element = element.offsetParent;
      if (element) {
        if (element.nodeName.toUpperCase() === 'BODY') break;
        var p = Element.getStyle(element, 'position');
        if (p !== 'static') break;
      }
    } while (element);

    return new Element.Offset(valueL, valueT);
  }

};

document.observe('dom:loaded', function() {

  AjaxsearchAutocomplete.isDomLoaded = true;

  window.tmAjaxSearch = {};

  var searchFormSelector =
    '#search_mini_form, '
    + '#search_mini_form-left, '
    + '#search_mini_form-right';

  $$(searchFormSelector).each(function(form){
    var searchInputSelector = '.input-text';
    var searchContainerSelector = '.mini-search';
    if (form.hasAttribute('data-config')) {
      var options = JSON.parse(form.readAttribute('data-config'));
      options.container = form.down(searchContainerSelector);
      options.onSelect = function (value, data) {setLocation(value.url)};
      window.tmAjaxSearch[form.id] =
        new AjaxsearchAutocomplete(form.down(searchInputSelector).id, options);
    }
  });

});
