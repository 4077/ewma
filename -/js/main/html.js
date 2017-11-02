// head {
var __nodeId__ = "ewma__main_html";
var __nodeNs__ = "ewma";
// }

(function (__nodeNs__, __nodeId__) {
    $.widget(__nodeNs__ + "." + __nodeId__, {
        options: {},

        _create: function () {

        },

        addContainer: function (name, content) {
            var $widget = this.element;

            var $container = this._getContainer(name);

            if ($container.length) {

            } else {
                $(content).appendTo($widget);
            }
        },

        replaceContainer: function (name, content) {
            var $widget = this.element;

            var $container = this._getContainer(name);

            if ($container.length) {
                $container.replaceWith(content);
            } else {
                $(content).appendTo($widget);
            }
        },

        removeContainer: function (name) {
            var $container = this._getContainer(name);

            if ($container.length) {
                $container.remove();
            }
        },

        _getContainer: function (name) {
            var $widget = this.element;

            if (name) {
                return $(".ewma__html_container[instance='" + name + "']", $widget);
            }
        }
    });
})(__nodeNs__, __nodeId__);
