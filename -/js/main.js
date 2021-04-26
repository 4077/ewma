function request(path, data, handler, quiet) {
    ewma.request(path, data, handler, quiet);
};

function multirequest(path, data) {
    ewma.multirequest.add(path, data);
}

var ewma = {
    cancelFollow: false,

    log: {
        requests: 0,
        events:   {
            bind:    0,
            trigger: 0
        }
    },

    appData: {
        url: false,

        tab: Math.random().toString(36).substring(2),

        css: {
            loaded: []
        },

        js: {
            loaded: []
        },

        nodes: []
    },

    responseHandler: function (response) {

        if (response) {
            // ewma.appData.css.checkVersion(response.css.version);
            // ewma.appData.js.checkVersion(response.js.version);

            ewma.processResponse(response);
        }
    },

    requests: 0,

    request: function (path, data, handler, quiet) {
        data = data || {};
        handler = handler || ewma.responseHandler;
        quiet = quiet || false;

        ewma.requests++;

        if (!quiet) {
            ewma.ui.waiting.show(true);
        }

        if (ewma.log.requests) {
            p({log: 'request', path: path, data: data, handler: handler, quiet: quiet});
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
                ewma.requests--;

                if (ewma.log.requests) {
                    p({log: 'response', response: response});
                }

                handler(response);

                ewma.ui.XHRError.remove();
                ewma.ui.waiting.remove();

                setTimeout(function () {
                    ewma.trigger("after_request");
                });
            },
            error:   function (response) {
                ewma.requests--;

                ewma.ui.waiting.remove();

                if (isset(response.responseJSON) && isset(response.responseJSON.error)) { // todo
                    ewma.ui.XHRError.show(response.responseJSON.error);
                    ewma.ui.waiting.show(true);
                } else {
                    p('EMPTY RESPONSE');
                }
            }
        });
    },

    multirequest: {
        sendTimeout:         0,
        stack:               [],
        doublesControlStack: [],

        add: function (path, data, handler) {
            handler = handler || false;

            if (ewma.log.requests) {
                p({log: 'multirequest add', path: path, data: data});
            }

            var multirequest = this;

            var callJson = JSON.stringify([path, data]);

            if (!this.doublesControlStack[callJson]) {
                this.stack.push([path, data]);
                this.doublesControlStack[callJson] = true;

                clearTimeout(multirequest.sendTimeout);
                this.sendTimeout = setTimeout(function () {
                    ewma.request('\\ewma~multirequest:handle', {calls: multirequest.stack}, handler, true);

                    multirequest.stack = [];
                    multirequest.doublesControlStack = [];
                }, 200);
            }
        }
    },

    processResponse: function (data) {
        if (data) {
            var i, url;

            for (i in data.css.hrefs) {
                if (!in_array(data.css.hrefs[i], ewma.appData.css.loaded)) {
                    $("head").append('<link rel="stylesheet" type="text/css" href="' + data.css.hrefs[i] + '"/>');

                    ewma.appData.css.loaded.push(data.css.hrefs[i]);
                }
            }

            for (i in data.js.hrefs) {
                if (!in_array(data.js.hrefs[i], ewma.appData.js.loaded)) {
                    $("head").append($('<script type="text/javascript" src="' + data.js.hrefs[i] + '"></script>'));

                    ewma.appData.js.loaded.push(data.js.hrefs[i]);
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
        ewma.ui.console.show();

        $("#ewma__ui_console").prepend($("<div>")
            .addClass("message")
            .html(data));
    },

    delay: function (fn, timeout) {
        return setTimeout(fn || function () {

        }, timeout || 0);
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
            if (eventName !== 'before_request' && eventName !== 'after_request') {
                p('trigger:   ' + eventName);
            }
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
    },

    history: {

        push: function (route, title) {
            window.history.pushState(null, title || null, route);

            ewma.trigger('ewma/history/push', {
                route: route,
                title: title
            });
        },

        replace: function (route, title) {
            window.history.replaceState(null, title || null, route);

            ewma.trigger('ewma/history/replace', {
                route: route,
                title: title
            });
        },

        states: 0,

        addState: function () {
            this.states++;

            this.push(document.location.href);
        },

        popState: function () {
            this.states--;

            if (this.states < 0) {
                this.states = 0;
            }

            p(this.states);

            return this.states;
        },

        popstateAllowed: function () {
            return this.states <= 0;
        }
    },

    ui: {

        waiting: {

            indicationInterval: null,

            lockTimeout: null,

            startIndicationTimeout: null,

            indicationStarted: false,

            show: function (error) {
                if (!$("#ewma__ui_waiting").length) {
                    $("body").prepend($("<div/>").attr("id", "ewma__ui_waiting"));
                }

                clearTimeout(ewma.ui.waiting.lockTimeout);
                ewma.ui.waiting.lockTimeout = setTimeout(function () {
                    $("#ewma__ui_waiting").addClass("lock");
                }, 400);

                if (!ewma.ui.waiting.indicationStarted) {
                    ewma.ui.waiting.indicationStarted = true;

                    clearTimeout(ewma.ui.waiting.startIndicationTimeout);
                    ewma.ui.waiting.startIndicationTimeout = setTimeout(function () {
                        ewma.ui.waiting.startIndication();
                    }, 500);
                }

                if (error) {
                    $("#ewma__waiting_overlay")
                        .addClass("error")
                        .click(function () {
                            $(this).remove();

                            ewma.ui.XHRError.remove();
                        });
                }
            },

            remove: function () {
                clearTimeout(ewma.ui.waiting.lockTimeout);

                var $ui = $("#ewma__ui_waiting");

                if (ewma.requests <= 0) {
                    $ui.removeClass("lock");

                    ewma.delay(function () {
                        $ui.remove();

                        ewma.ui.waiting.stopIndication();
                    });

                    ewma.requests = 0;
                }
            },

            startIndication: function () {
                if (!$("#ewma__ui_waiting__indicator").length) {
                    $("body").prepend(
                        $("<div/>").attr("id", "ewma__ui_waiting__indicator").append($("<div/>").attr("id", "ewma__ui_waiting__indicator_bar"))
                    );
                }

                var $bar = $("#ewma__ui_waiting__indicator_bar");

                var tick = 0;

                clearInterval(ewma.ui.waiting.indicationInterval)
                ewma.ui.waiting.indicationInterval = setInterval(function () {
                    tick++;

                    var v1 = 33 / (tick + 33);
                    var v2 = 100 - v1 * 100;

                    $("#ewma__ui_waiting__indicator_bar").width(v2 + "%");

                    // p(tick + ' ' + v2);

                }, 10);
            },

            stopIndication: function () {
                $("#ewma__ui_waiting__indicator").remove();

                clearInterval(ewma.ui.waiting.indicationInterval);
                clearTimeout(ewma.ui.waiting.startIndicationTimeout);

                ewma.ui.waiting.indicationStarted = false;
            }
        },

        XHRError: {

            show: function (error) {
                var ideUrl = "phpstorm://open/?file=" + error.file + "&line=" + error.line;

                $("body").prepend($("<div/>")
                    .attr("id", "ewma__ui_xhr_error")
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

            remove: function () {
                $("#ewma__ui_xhr_error").remove();
            }
        },

        console: {

            show: function () {
                if (!$("#ewma__ui_console").length) {
                    $("body").prepend($("<div/>")
                        .attr("id", "ewma__ui_console")
                        .dblclick(function () {
                            $("#ewma__ui_console").remove();
                        }));
                }
            }
        },
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

    __create: function () {

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

        if (requests) {
            var request = requests[requestName];

            if (request) {
                var requestPath;
                var requestData = JSON.parse(JSON.stringify(this.options['.payload'] || {}));

                if (typeof request === 'string') {
                    requestPath = request;
                }

                if (request instanceof Array) {
                    requestPath = request[0];

                    $.extend(requestData, request[1] || {});
                }

                $.extend(requestData, data);

                handler = handler || ewma.responseHandler;

                if (multi) {
                    ewma.multirequest.add(requestPath, requestData, handler);
                } else {
                    quiet = quiet || false;

                    ewma.request(requestPath, requestData, handler, quiet);
                }
            } else {
                // ewma.log('not set request "' + requestName + '"');
            }
        } else {
            // ewma.log('not set requests');
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
    // ewma.appData.css.version = ewmaAppData.css.version;
    ewma.appData.css.loaded = ewmaAppData.css.hrefs;
    // ewma.appData.js.version = ewmaAppData.js.version;
    ewma.appData.js.loaded = ewmaAppData.js.hrefs;

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
