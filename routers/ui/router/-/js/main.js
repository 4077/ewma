// head {
var __nodeId__ = "ewma_routers_ui_router__main";
var __nodeNs__ = "ewma_routers_ui_router";
// }

(function (__nodeNs__, __nodeId__) {
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

            ewma.bind("reload." + __nodeId__ + "." + widget.options.routerId, function () {
                ewma.multirequest.add(widget.options.paths.reload, {
                    router_id: widget.options.routerId
                });
            });
        }
    });
})(__nodeNs__, __nodeId__);
