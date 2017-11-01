$.widget(__nodeNs__ + "." + __nodeId__, {
    options: {},

    _create: function () {
        this.bind();
    },

    _destroy: function () {

    },

    _setOption: function (key, value) {
        $.Widget.prototype._setOption.apply(this, arguments);
    },

    bind: function () {
        var widget = this;


    }
});
