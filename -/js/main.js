function request(path, data, handler, quiet) {
    ewma.request(path, data, handler, quiet);
};

function multirequest(path, data) {
    ewma.multirequest.add(path, data);
}

var ewma = {
    cancelFollow: false,

    log: {
        events: {
            bind:    0,
            trigger: 0
        }
    },

    appData: {
        url: false,

        tab: Math.random().toString(36).substring(2),

        css: {
            version:             0,
            versionBeforeChange: 0,
            loaded:              [],

            checkVersion: function (version) {
                if (version !== this.version) {
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
                if (version !== this.version) {
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
                call: JSON.stringify([path, data]),
                tab:  ewma.appData.tab
            },
            success: function (response) {
                handler(response);

                ewma.removeXHRError();
                ewma.removeWaitingLayer();

                ewma.trigger("after_request");
            },
            error:   function (response) {
                if (response.responseJSON.error) {
                    ewma.showXHRError(response.responseJSON.error);

                    ewma.removeWaitingLayer();
                    ewma.showWaitingLayer(true);
                }
            }
        });
    },

    multirequest: {
        sendTimeout:         0,
        stack:               [],
        doublesControlStack: [],

        add: function (path, data) {
            var multirequest = this;

            var callJson = JSON.stringify([path, data]);

            if (!this.doublesControlStack[callJson]) {
                this.stack.push([path, data]);
                this.doublesControlStack[callJson] = true;

                clearTimeout(multirequest.sendTimeout);
                this.sendTimeout = setTimeout(function () {
                    ewma.request('\\ewma~multirequest:handle', {calls: multirequest.stack}, false, true);

                    multirequest.stack = [];
                    multirequest.doublesControlStack = [];
                }, 200);
            }
        }
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

            if (data.reload !== undefined) {
                location.reload();
            }
        }
    },

    processInstructions: function (instructions) {
        var i;

        for (i in instructions['js']) {
            var instruction = instructions['js'][i];

            if (instruction.type === 'call') {
                call_user_func_array(instruction.data.method, instruction.data.args);
            }

            if (instruction.type === 'raw') {
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

    eventContainers: [],

    commonEventsContainer: $('<div class="common_events_container"></div>'),

    bind: function (eventName, callback, $container) {
        if (this.log.events.bind) {
            p('bind:   ' + eventName);
        }

        $container = $container || this.commonEventsContainer;

        if (!$container.data("isEventListener")) {
            ewma.eventContainers.push($container);

            $container.data("isEventListener", true);
        }

        $container.bind(eventName, function (e, data) {
            callback(data, e);
        });
    },

    rebind: function (eventName, callback, $container) {
        if (this.log.events.bind) {
            p('unbind:   ' + eventName);
        }

        $container = $container || this.commonEventsContainer;

        $container.unbind(eventName);

        ewma.bind(eventName, callback, $container);
    },

    trigger: function (eventName, args) {
        if (this.log.events.trigger) {
            p('trigger:   ' + eventName);
        }

        $.each(ewma.eventContainers, function (n, $eventContainer) {
            $eventContainer.trigger(eventName, args)
        });
    },

    getWidget: function (selector, nodeId) {
        execute_function_by_name(selector, nodeId);
    },

    w: function (path) {
        var parts = path.split("|");

        return execute_function_by_name(parts[1], $(parts[0]), "instance");
    },

    proc: function (xpid) {
        return {
            xpid: xpid,

            loopInterval: 0,

            loop: function (handler, interval) {
                interval = interval || 1000;

                var proc = this;

                clearInterval(proc.loopInterval);
                proc.loopInterval = setInterval(function () {
                    $.getJSON('/proc/' + proc.xpid + '.json', {}, function (data) {
                        if (data.terminated) {
                            proc.terminateHandler(data.output, data.errors);

                            clearInterval(proc.loopInterval);
                        }

                        if (data.progress || data.output || data.errors) {
                            handler(data.progress, data.output, data.errors);
                        }
                    });
                }, interval);
            },

            terminateHandler: function () {

            },

            terminate: function (handler) {
                this.terminateHandler = handler;
            },

            pause: function () {
                ewma.request('\ewma~process/xhr:pause:' + this.xpid);
            },

            resume: function () {
                ewma.request('\ewma~process/xhr:resume:' + this.xpid);
            },

            break: function () {
                ewma.request('\ewma~process/xhr:break:' + this.xpid);
            }
        };
    }
};

$.widget("ewma.node", {
    _create: function () {
        var w = this;

        var events = this.options['.e'];

        if (events) {
            for (var eventName in events) {
                if (events.hasOwnProperty(eventName)) {
                    var eventHandlerPath = events[eventName];

                    if (eventHandlerPath.lastIndexOf('r.', 0) === 0) {
                        (function () {
                            var requestName = eventHandlerPath.substring(2);

                            // p('event request: ' + requestName);

                            w.e(eventName, function (data) {
                                w.r(requestName, data);
                            });
                        })();
                    } else if (eventHandlerPath.lastIndexOf('mr.', 0) === 0) {
                        (function () {
                            var requestName = eventHandlerPath.substring(3);

                            // p('event multirequest: ' + requestName);

                            w.e(eventName, function (data) {
                                w.r(requestName, data, true);
                            });
                        })();
                    } else {
                        (function () {
                            var eventHandlerPath = events[eventName];

                            // p('event handler: ' + eventHandlerPath);

                            w.e(eventName, function (data) {
                                w[eventHandlerPath](data);
                            });
                        })();
                    }
                }
            }
        }

        this.__create();
    },

    w: function (widgetName) {
        return ewma.w(this.options['.w'][widgetName]);
    },

    e: function (event, handler) {
        if (event.substr(0, 1) === '+') {
            ewma.bind(event.substr(1) + '.' + this.widgetName, handler);
            // ewma.bind(event.substr(1) + '.' + this.widgetName + '.' + this.uuid, handler); // reload problem
        } else {
            ewma.rebind(event + '.' + this.widgetName, handler);
            // ewma.rebind(event + '.' + this.widgetName + '.' + this.uuid, handler);
        }
    },

    r: function (requestName, data, multi, handler, quiet) {
        var requests = this.options['.r'];
        var request = requests[requestName];

        if (request) {
            var requestPath;
            var requestData = this.options['.payload'] || {};

            if (typeof request === 'string') {
                requestPath = request;
            }

            if (request instanceof Array) {
                requestPath = request[0];

                $.extend(requestData, request[1] || {});
            }

            $.extend(requestData, data);

            if (multi) {
                ewma.multirequest.add(requestPath, requestData);
            } else {
                handler = handler || ewma.responseHandler;
                quiet = quiet || false;

                ewma.request(requestPath, requestData, handler, quiet);
            }
        }
    },

    mr: function (requestName, data, handler) {
        this.r(requestName, data, true, handler);
    }
});

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

    ewma.commonEventsContainer.appendTo("body");

    /*ewma.bind('ewma/css/update', function (data) {
        var path = data.path;

        $('link[rel="stylesheet"]').each(function () {
            var pos = this.href.indexOf(path);

            if (pos !== -1) {
                var tail = this.href.substr(pos + path.length);

                this.href = ewma.appData.url + path + tail + Date.now();
            }
        });
    });*/
});

$.fn.getWidget = function (widgetName) {
    var data = $(this).data();

    widgetName = widgetName || Object.keys(data)[0];

    return data[widgetName];
};
