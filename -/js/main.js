function request(path, data, handler, background) {
    ewma.request(path, data, handler, background);
};

var ewma = {
    cancelFollow: false,

    appData: {
        url: false,

        css: {
            version:             0,
            versionBeforeChange: 0,
            loaded:              [],

            checkVersion: function (version) {
                if (version != this.version) {
                    this.versionBeforeChange = this.version;
                    this.version = version;
                    this.reloadAll();
                }
            },

            reloadAll: function () {
                for (var i in this.loaded) {
                    var hrefBeforeChange = ewma.appData.url + this.loaded[i] + ".css?" + this.versionBeforeChange;
                    var href = ewma.appData.url + this.loaded[i] + ".css?" + this.version;

                    $("head").find("link[href='" + hrefBeforeChange + "']").attr("href", href);
                }
            }
        },

        js: {
            version:             0,
            versionBeforeChange: 0,
            loaded:              [],

            checkVersion: function (version) {
                if (version != this.version) {
                    this.versionBeforeChange = this.version;
                    this.version = version;
                    this.reloadAll();
                }
            },

            reloadAll: function () {
                for (var i in this.loaded) {
                    var srcBeforeChange = ewma.appData.url + this.loaded[i] + ".js?" + this.versionBeforeChange;
                    var src = ewma.appData.url + this.loaded[i] + ".js?" + this.version;

                    $("head").find("script[src='" + srcBeforeChange + "']").attr("src", src);
                }
            }
        },

        nodes: []
    },

    responseWaitingTimeout: 0,

    responseHandler: function (response) {
        clearTimeout(ewma.responseWaitingTimeout);

        if (response) {
            ewma.appData.css.checkVersion(response.css.version);
            ewma.appData.js.checkVersion(response.js.version);

            ewma.processResponse(response);
        }
    },

    request: function (path, data, handler, quiet) {
        data = data || {};
        handler = handler || ewma.responseHandler;
        quiet = quiet || false;

        clearTimeout(ewma.responseWaitingTimeout);

        if (!quiet) {
            ewma.responseWaitingTimeout = setTimeout(function () {
                ewma.showWaitingLayer();
            }, 400);
        }

        ewma.trigger("before_request");

        $.ajax({
            type:    "POST",
            url:     ewma.appData.url,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data:    {
                call: JSON.stringify([path, data])
            },
            success: function (response) {
                handler(response);

                ewma.removeXHRError();
                ewma.removeWaitingLayer();

                ewma.trigger("after_request");
            },
            error:   function (jqXHR) {
                if (jqXHR.responseJSON.error) {
                    ewma.showXHRError(jqXHR.responseJSON.error);

                    ewma.removeWaitingLayer();
                    ewma.showWaitingLayer(true);
                }
            }
        });
    },

    showXHRError: function (error) {
        var ideUrl = "phpstorm://open/?file=" + error.file + "&line=" + error.line;

        $("body").prepend($("<div/>")
            .attr("id", "ewma__xhr_error")
            .append($("<div>").html(error.message))
            .append($("<div>").addClass("line").append(
                $("<a>")
                    .html(error.file + ':' + error.line)
                    .attr("href", ideUrl)
            ))
            .click(function () {
                window.location = ideUrl;
            }));
    },

    showWaitingLayer: function (error) {
        $("body").prepend($("<div/>").attr("id", "ewma__waiting_overlay"));

        if (error) {
            $("#ewma__waiting_overlay")
                .addClass("error")
                .click(function () {
                    $(this).remove();
                    ewma.removeXHRError();
                });
        }
    },

    removeWaitingLayer: function () {
        clearTimeout(ewma.responseWaitingTimeout);

        $("#ewma__waiting_overlay").remove();
    },

    removeXHRError: function () {
        $("#ewma__xhr_error").remove();
    },

    processResponse: function (data) {
        if (data) {
            var i, url;

            for (i in data.css.paths) {
                if (!in_array(data.css.paths[i], ewma.appData.css.loaded)) {
                    url = ewma.appData.url + data.css.paths[i] + ".css?" + ewma.appData.css.version;
                    $("head").append('<link rel="stylesheet" type="text/css" href="' + url + '"/>');
                    ewma.appData.css.loaded.push(data.css.paths[i]);
                }
            }

            for (i in data.js.paths) {
                if (!in_array(data.js.paths[i], ewma.appData.js.loaded)) {
                    url = ewma.appData.url + data.js.paths[i] + ".js?" + ewma.appData.js.version;
                    $("head").append($('<script type="text/javascript" src="' + url + '"></script>')); // todo потестить без $()
                    ewma.appData.js.loaded.push(data.js.paths[i]);
                }
            }

            ewma.processInstructions(data.instructions);

            if (data.redirect !== undefined) {
                window.location.replace(data.redirect);
            }

            if (data.href !== undefined) {
                window.location.href = data.href;
            }
        }
    },

    processInstructions: function (instructions) {
        var i;

        for (i in instructions['js']) {
            var instruction = instructions['js'][i];

            if (instruction.type == 'call') {
                call_user_func_array(instruction.data.method, instruction.data.args);
            }

            if (instruction.type == 'raw') {
                eval(instruction.code);
            }
        }

        for (i in instructions['cookies']) {
            var cookie = instructions['cookies'][i];

            var date = new Date();
            date.setTime(cookie.expires * 1000);

            $.cookie(cookie.name, cookie.value, {expires: date, path: cookie.path});
        }

        for (i in instructions['console']) {
            ewma.console(instructions['console'][i]);
        }
    },

    console: function (data) {
        ewma.addConsoleMessage(data);
    },

    addConsoleMessage: function (data) {
        ewma.showConsoleLayer();

        $("#ewma__console_layer").append($("<div>")
            .addClass("message")
            .html(data));
    },

    showConsoleLayer: function () {
        if (!$("#ewma__console_layer").length) {
            $("body").prepend($("<div/>")
                .attr("id", "ewma__console_layer")
                .click(function () {
                    $("#ewma__console_layer").remove();
                }));
        }
    },

    delay: function (fn, timeout) {
        return setTimeout(fn, timeout || 0);
    },

    nodes: {},

    eventsContainer: $("<div></div>"),

    bind: function (eventName, callback) {
        this.eventsContainer.bind(eventName, callback);
    },

    trigger: function (eventName, args) {
        this.eventsContainer.trigger(eventName, args); // todo test args
    },

    callWidget: function (selector, nodeId) {

    }
};

$(document).ready(function () {
    $.ajaxSetup({
        cache: true
    });

    ewma.appData.url = ewmaAppData.url;
    ewma.appData.css.version = ewmaAppData.css.version;
    ewma.appData.css.loaded = ewmaAppData.css.paths;
    ewma.appData.js.version = ewmaAppData.js.version;
    ewma.appData.js.loaded = ewmaAppData.js.paths;

    ewma.processInstructions(ewmaAppData.instructions);
});

$.fn.getWidget = function (widgetName) {
    var data = $(this).data();

    widgetName = widgetName || Object.keys(data)[0];

    return data[widgetName];
};
