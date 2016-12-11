var Slider = Class.create();
Slider.prototype = {
    options: {
        shift: 701,
        useCss: false
    },

    initialize: function(container, controlLeft, controlRight, settings){
        Object.extend(this.options, settings);
        this.animating = false;
        this.containerSize = {
            width: $(container).offsetWidth,
            height: $(container).offsetHeight
        },
        this.container = $(container);
        this.content = $(container).down();
        this.controlLeft = $(controlLeft);
        this.controlRight = $(controlRight);

        this.content.setStyle('left:0');
        this.initControls();
    },

    initControls: function(){
        this.controlLeft.href = this.controlRight.href = 'javascript:void(0)';
        Event.observe(this.controlLeft,  'click', this.shiftLeft.bind(this));
        Event.observe(this.controlRight, 'click', this.shiftRight.bind(this));
        this.updateControls(1, 0);
    },

    getShift: function(){
        if ('auto' === this.options.shift) {
            return this.container.getWidth();
        }
        return this.options.shift;
    },

    shiftRight: function(){
        if (this.animating)
            return;

        var left = isNaN(parseInt(this.content.style.left)) ? 0 : parseInt(this.content.style.left),
            shift;

        if ((left + this.getShift()) < 0) {
            shift = this.getShift();
            this.updateControls(1, 1);
        } else {
            shift = Math.abs(left);
            this.updateControls(1, 0);
        }
        this.moveTo(shift);
    },

    shiftLeft: function(){
        if (this.animating)
            return;

        var left = isNaN(parseInt(this.content.style.left)) ? 0 : parseInt(this.content.style.left),
            lastItemLeft = this.content.childElements().last().positionedOffset()[0],
            lastItemWidth = this.content.childElements().last().getWidth(),
            contentWidth = lastItemLeft + lastItemWidth + 8,
            shift;

        if ((contentWidth + left - this.getShift()) > this.container.getWidth()) {
            shift = this.getShift();
            this.updateControls(1, 1);
        } else {
            shift = contentWidth + left - this.container.getWidth();
            this.updateControls(0, 1);
        }
        this.moveTo(-shift);
    },

    moveTo: function(shift){
        if (this.options.useCss) {
            var left = isNaN(parseInt(this.content.style.left)) ? 0 : parseInt(this.content.style.left);
            this.content.setStyle({
                left: left + shift + 'px'
            });
        } else {
            var scope = this;
            this.animating = true;
            new Effect.Move(this.content, {
                x: shift,
                duration: 0.4,
                delay: 0,
                afterFinish: function(){
                    scope.animating = false;
                }
            });
        }
    },

    updateControls: function(left, right){
        if (!left)
            this.controlLeft.addClassName('disabled');
        else
            this.controlLeft.removeClassName('disabled');

        if (!right)
            this.controlRight.addClassName('disabled');
        else
            this.controlRight.removeClassName('disabled');
    }
};
