// head {
var __nodeId__ = "ewma_dev_ui_modulesTree__main";
var __nodeNs__ = "ewma_dev_ui_modulesTree";
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

            $(".indent.clickable", widget.element).rebind("click", function (e) {
                request(widget.options.paths.toggleSubnodes, {
                    module_path: $(this).closest(".nodes").attr("module_path")
                });

                e.stopPropagation();
            });

            $(".name", widget.element).rebind("click", function (e) {
                request(widget.options.paths.selectModule, {
                    module_path: $(this).closest(".nodes").attr("module_path")
                });

                e.stopPropagation();
            });

            $(".action_button", widget.element).rebind("click", function (e) {
                request(data.paths.input + ':' + $(this).attr("action"), {
                    module_path: $(this).closest(".nodes").attr("module_path"),
                    node_type:   $(this).attr("type")
                });

                e.stopPropagation();
            });
        }
    });
})(__nodeNs__, __nodeId__);
