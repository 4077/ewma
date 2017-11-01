function request(path, data, handler, background) {
    ewma.request(path, data, handler, background);
};

var ewma = {
    cancelFollow: false,

    appData: {
        host: false,

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
                    var hrefBeforeChange = ewma.appData.host + this.loaded[i] + ".css?" + this.versionBeforeChange;
                    var href = ewma.appData.host + this.loaded[i] + ".css?" + this.version;

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
                    var srcBeforeChange = ewma.appData.host + this.loaded[i] + ".js?" + this.versionBeforeChange;
                    var src = ewma.appData.host + this.loaded[i] + ".js?" + this.version;

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
                $("body").prepend($("<div/>").attr("id", "ewma_response_waiting_overlay").css({
                    'cursor':     'wait',
                    'position':   'fixed',
                    'width':      '100%',
                    'margin':     '0 auto',
                    'min-height': '100%',
                    'height':     'auto !important',
                    'height':     '100%',
                    'z-index':    '33554432'
                }));
            }, 400);
        }

        ewma.trigger("before_request");

        $.ajax({
            type:    "POST",
            url:     ewma.appData.host,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data:    {
                call: JSON.stringify([path, data])
            },
            success: function (response) {
                handler(response);

                clearTimeout(ewma.responseWaitingTimeout);
                $("#ewma_response_waiting_overlay").remove();

                ewma.trigger("after_request");
            }
        });
    },

    processResponse: function (data) {
        if (data) {
            var i, url;

            for (i in data.css.urls) {
                if (!in_array(data.css.urls[i], ewma.appData.css.loaded)) {
                    url = data.css.urls[i];
                    $("head").append('<link rel="stylesheet" type="text/css" href="' + url + '"/>');
                    ewma.appData.css.loaded.push(data.css.paths[i]);
                }
            }

            for (i in data.css.paths) {
                if (!in_array(data.css.paths[i], ewma.appData.css.loaded)) {
                    url = ewma.appData.host + data.css.paths[i] + ".css?" + ewma.appData.css.version;
                    $("head").append('<link rel="stylesheet" type="text/css" href="' + url + '"/>');
                    ewma.appData.css.loaded.push(data.css.paths[i]);
                }
            }

            for (i in data.js.urls) {
                if (!in_array(data.js.urls[i], ewma.appData.js.loaded)) {
                    url = data.js.urls[i];
                    $("head").append($('<script type="text/javascript" src="' + url + '"></script>')); // todo потестить без $()
                    ewma.appData.js.loaded.push(data.js.paths[i]);
                }
            }

            for (i in data.js.paths) {
                if (!in_array(data.js.paths[i], ewma.appData.js.loaded)) {
                    url = ewma.appData.host + data.js.paths[i] + ".js?" + ewma.appData.js.version;
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

        //for (i in instructions['json']) {
        //    var json = instructions['json'][i];
        //
        //    if (typeof jsonHandler == 'function') {
        //        jsonHandler(json);
        //    }
        //
        //    if (typeof jsonHandler == 'string') {
        //        call_user_func_array(jsonHandler, json); // todo test
        //        //eval(jsonHandler + "(json);");
        //    }
        //}

        for (i in instructions['js']) {
            var instruction = instructions['js'][i];

            if (instruction.type == 'call') {
                //if (ewma.nodes[instruction.data.method] != undefined) {
                //    //p(ewma.nodes[instruction.data.method]);
                //    call_user_func_array(ewma.nodes[instruction.data.method], instruction.data.args);
                //} else {
                call_user_func_array(instruction.data.method, instruction.data.args);
                //}
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
            console.log(eval(instructions['console'][i]));
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

    ewma.appData.host = ewmaAppData.host;
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
