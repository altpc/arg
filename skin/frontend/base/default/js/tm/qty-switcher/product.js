TMProductQtySwitcher = function () {
    var _config;

    function _getElement() {
        return $('qty');
    }

    function _inc() {
        var el = _getElement(),
            step = _config.increment,
            min = _config.min,
            max = _config.max,
            value = el.value;

        if (isNaN(value)) {
            value = min;
        } else {
            value = parseFloat(value);
        }

        if (max >= value + step) {
            el.value = value + step;
        }
        return true;
    }

    function _dec() {
        var el = _getElement(),
            step = _config.increment,
            min = _config.min,
            max = _config.max,
            value = el.value;

        if (isNaN(value)) {
            value = min;
        } else {
            value = parseFloat(value);
        }

        if (min <= value - step) {
            value = value - step;
            if (value > max) {
                value = max;
            }
            el.value = value;
        }
        return true;
    }

    function _init() {
        var el = _getElement();
        if (!el) {
            return;
        }
        var span2 = "<span><span>%s</span></span>";
        span2 = "%s";
        var decElement = new Element('a', {class:'qty qty-switcher-dec'})
                .update(span2.replace('%s', '‹')),
            incElement = new Element('a', {class:'qty qty-switcher-inc'})
                .update(span2.replace('%s', '›'));
        incElement.observe('click', _inc);
        decElement.observe('click', _dec);

        el.insert({
            before: decElement,
            after: incElement
        });

        el.observe('keydown', function(e){
            if (38 === e.keyCode) {
                _inc();
            }
            if (40 === e.keyCode) {
                _dec();
            }
        });

        el.writeAttribute('autocomplete', 'off');
    }

    return {
        init: function(config) {
            if (config.max < config.min) {
                config.max = 10000;
            }
            _config = config;
            _init();
        }
    }
}();
