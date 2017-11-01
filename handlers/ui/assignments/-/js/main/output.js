// head {
var __nodeId__ = "ewma_handlers_ui_assignments__main_output";
var __nodeNs__ = "ewma_handlers_ui_assignments";
// }

(function (__nodeNs__, __nodeId__) {
    $.widget(__nodeNs__ + "." + __nodeId__, {
        options: {},

        _create: function () {
            this.bind();
        },

        _setOption: function (key, value) {
            $.Widget.prototype._setOption.apply(this, arguments);
        },

        bind: function () {
            var widget = this;

            $(".bar", widget.element).rebind("click", function () {
                $(this).find(".cp").toggle();
            });
        }
    });
})(__nodeNs__, __nodeId__);
