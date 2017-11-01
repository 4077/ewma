var ewma__common = function (context) {
    context = context.length ? context : document;

    $("[route]", context)
        .rebind("click.std_common_route", function (e) {
            e.stopPropagation();

            if (ewma.cancelFollow) {
                ewma.cancelFollow = false;

                return false;
            }

            href($(this).attr("route"));

            return false;
        });

    $("[request]", context)
        .rebind("click.std_common_path", function (e) {
            e.stopPropagation();

            if (ewma.cancelFollow) {
                ewma.cancelFollow = false;

                return false;
            }

            request($(this).attr("path"));
        });

    $("[hover]", context).each(function () {
        var hoverClass = $(this).attr("hover") || "hover";

        $(this).bind("mouseenter", function (e) {
            //$("[hover_delay]").removeClass(hoverClass);
            var hoverable = $(this);

            //setTimeout(function () {
                hoverable.addClass(hoverClass);
            //});

        }).bind("mouseleave", function () {
            var hoverable = $(this);

            var delay = $(this).attr("hover_delay");

            //if (delay) {
            //    setTimeout(function () {
            //        hoverable.removeClass(hoverClass);
            //    });
            //} else {
                hoverable.removeClass(hoverClass);
            //}
        });
    });

    $("[hover_group]", context).each(function () {
        var hoverClass = $(this).attr("hover") || "hover";
        var group = $(this).attr("hover_group");

        var broadcaster = $(this);

        $("[hover_group='" + group + "'], [hover_listen='" + group + "']").not(this)
            .each(function () {
                var listener = $(this);

                $(broadcaster).bind("mouseenter", function () {
                    $(listener).addClass(hoverClass);
                }).bind("mouseleave", function () {
                    $(listener).removeClass(hoverClass);
                });
            });
    });

    $("[hover_broadcast]").each(function () {
        var hoverClass = $(this).attr("hover") || "hover";
        var group = $(this).attr("hover_broadcast");

        var broadcaster = $(this);

        $("[hover_group='" + group + "'], [hover_listen='" + group + "']").not(this).each(function () {
            var listener = $(this);

            $(broadcaster).bind("mouseenter", function () {
                $(listener).addClass(hoverClass);
            }).bind("mouseleave", function () {
                $(listener).removeClass(hoverClass);
            });
        });
    });

    $("[fit]").each(function () {
        var source = $(this);
        var target = source.closest(source.attr("fit"));

        var height = target.height() - source.css("padding-top").replace("px", "") - source.css("padding-bottom").replace("px", "");

        source.height(height);
    });
};
