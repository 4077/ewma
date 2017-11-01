$.fn.rebind = function (e, callback) {
    $(this).unbind(e).bind(e, callback);

    return this;
};

//function e(object) {
//    if (object) {
//        return eval('(' + object + ')');
//    }
//}

function reload() {
    setTimeout(function () {
        location.reload()
    }, 0);
}

function href(url) {
    setTimeout(function () {
        location.href = url;
    }, 0);
}

//

function p(input) {
    console.log(input);
}



// php:

/**
 * https://github.com/kvz/phpjs/blob/master/functions/funchand/call_user_func_array.js
 *
 * @param cb
 * @param parameters
 * @returns {*}
 */
function call_user_func_array(cb, parameters) {

    //eval('var fn = eval(cb)');
    //
    //return fn.apply(fn, parameters);

    //var func;
    //
    //if (typeof cb == 'string') {
    //    if (typeof this[cb] == 'function') {
    //        func = this[cb];
    //    } else {
    //        func = eval(cb);//(new Function(null, 'return ' + cb))();
    //    }
    //} else if (cb instanceof Array) {
    //    func = eval(cb[0] + "['" + cb[1] + "']");
    //}
    //
    //if (typeof func != 'function') {
    //    throw new Exception(func + ' is not a valid function');
    //}

    var parts = cb.split('.');
    var method = parts.pop();
    var obj = parts.join('.') || 'window';

    eval('obj = ' + obj + ';');

    return obj[method].apply(obj, parameters);

    //func.apply(func, parameters);
}

//function in_array(needle, haystack) {
//    for (var key in haystack) {
//        if (haystack[key] === needle) {
//            return true;
//        }
//    }
//
//    return false;
//}

function in_array(needle, array, strict) {
    var len = array.length;

    for (var i = 0; i < len; i++) {
        if (strict) {
            if (array[i] === needle) {
                return true;
            }
        } else {
            if (array[i] == needle) {
                return true;
            }
        }
    }

    return false;
}

function array_unique(arr) {
    var tmp_arr = new Array();
    for (var i = 0; i < arr.length; i++) {
        if (!in_array(arr[i], tmp_arr)) {
            tmp_arr.push(arr[i]);
        }
    }
    return tmp_arr;
}

function isset() {
    //  discuss at: http://phpjs.org/functions/isset/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: FremyCompany
    // improved by: Onno Marsman
    // improved by: Rafał Kukawski
    //   example 1: isset( undefined, true);
    //   returns 1: false
    //   example 2: isset( 'Kevin van Zonneveld' );
    //   returns 2: true

    var a = arguments,
        l = a.length,
        i = 0,
        undef;

    if (l === 0) {
        throw new Error('Empty isset');
    }

    while (i !== l) {
        if (a[i] === undef || a[i] === null) {
            return false;
        }
        i++;
    }
    return true;
}

function str_replace(search, replace, subject) {
    return subject.split(search).join(replace);
}

function substr(str, start, len) {
    //  discuss at: http://phpjs.org/functions/substr/
    //     version: 909.322
    // original by: Martijn Wieringa
    // bugfixed by: T.Wild
    // improved by: Onno Marsman
    // improved by: Brett Zamir (http://brett-zamir.me)
    //  revised by: Theriault

    var i = 0,
        allBMP = true,
        es = 0,
        el = 0,
        se = 0,
        ret = '';
    str += '';
    var end = str.length;

    // BEGIN REDUNDANT
    this.php_js = this.php_js || {};
    this.php_js.ini = this.php_js.ini || {};
    // END REDUNDANT
    switch ((this.php_js.ini['unicode.semantics'] && this.php_js.ini['unicode.semantics'].local_value.toLowerCase())) {
        case 'on':
            // Full-blown Unicode including non-Basic-Multilingual-Plane characters
            // strlen()
            for (i = 0; i < str.length; i++) {
                if (/[\uD800-\uDBFF]/.test(str.charAt(i)) && /[\uDC00-\uDFFF]/.test(str.charAt(i + 1))) {
                    allBMP = false;
                    break;
                }
            }

            if (!allBMP) {
                if (start < 0) {
                    for (i = end - 1, es = (start += end); i >= es; i--) {
                        if (/[\uDC00-\uDFFF]/.test(str.charAt(i)) && /[\uD800-\uDBFF]/.test(str.charAt(i - 1))) {
                            start--;
                            es--;
                        }
                    }
                } else {
                    var surrogatePairs = /[\uD800-\uDBFF][\uDC00-\uDFFF]/g;
                    while ((surrogatePairs.exec(str)) != null) {
                        var li = surrogatePairs.lastIndex;
                        if (li - 2 < start) {
                            start++;
                        } else {
                            break;
                        }
                    }
                }

                if (start >= end || start < 0) {
                    return false;
                }
                if (len < 0) {
                    for (i = end - 1, el = (end += len); i >= el; i--) {
                        if (/[\uDC00-\uDFFF]/.test(str.charAt(i)) && /[\uD800-\uDBFF]/.test(str.charAt(i - 1))) {
                            end--;
                            el--;
                        }
                    }
                    if (start > end) {
                        return false;
                    }
                    return str.slice(start, end);
                } else {
                    se = start + len;
                    for (i = start; i < se; i++) {
                        ret += str.charAt(i);
                        if (/[\uD800-\uDBFF]/.test(str.charAt(i)) && /[\uDC00-\uDFFF]/.test(str.charAt(i + 1))) {
                            se++; // Go one further, since one of the "characters" is part of a surrogate pair
                        }
                    }
                    return ret;
                }
                break;
            }
        // Fall-through
        case 'off':
        // assumes there are no non-BMP characters;
        //    if there may be such characters, then it is best to turn it on (critical in true XHTML/XML)
        default:
            if (start < 0) {
                start += end;
            }
            end = typeof len === 'undefined' ? end : (len < 0 ? len + end : len + start);
            // PHP returns false if start does not fall within the string.
            // PHP returns false if the calculated end comes before the calculated start.
            // PHP returns an empty string if start and end are the same.
            // Otherwise, PHP returns the portion of the string from start to end.
            return start >= str.length || start < 0 || start > end ? !1 : str.slice(start, end);
    }
    return undefined; // Please Netbeans
}


function explode(delimiter, string) {
    var emptyArray = {0: ''};

    if (arguments.length != 2 || typeof arguments[0] == 'undefined' || typeof arguments[1] == 'undefined') {
        return null;
    }

    if (delimiter === '' || delimiter === false || delimiter === null) {
        return false;
    }

    if (typeof delimiter == 'function' || typeof delimiter == 'object' || typeof string == 'function' || typeof string == 'object') {
        return emptyArray;
    }

    if (delimiter === true) {
        delimiter = '1';
    }

    return string.toString().split(delimiter.toString());
}

function constrains(value, min, max) {
    if (value < min) {
        value = min;
    }
    if (value > max) {
        value = max;
    }

    return value;
}

function ending(number, one, two, five) {
    var ending;

    number = number % 100;

    if ((number > 4 && number < 21) || number == 0) {
        ending = five;
    }
    else {
        var last_digit = substr(number, -1);

        if (last_digit > 1 && last_digit < 5) {
            ending = two;
        }
        else {
            if (last_digit == 1) {
                ending = one;
            } else {
                ending = five;
            }
        }
    }

    return ending;
}

function date(format, timestamp) {
    var a, jsdate = new Date(timestamp ? timestamp * 1000 : null);
    var pad = function (n, c) {
        if ((n = n + "").length < c) {
            return new Array(++c - n.length).join("0") + n;
        } else {
            return n;
        }
    };
    var txt_weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday",
        "Thursday", "Friday", "Saturday"];
    var txt_ordin = {1: "st", 2: "nd", 3: "rd", 21: "st", 22: "nd", 23: "rd", 31: "st"};
    var txt_months = ["", "January", "February", "March", "April",
        "May", "June", "July", "August", "September", "October", "November",
        "December"];

    var f = {
        // Day
        d: function () {
            return pad(f.j(), 2);
        },
        D: function () {
            t = f.l();
            return t.substr(0, 3);
        },
        j: function () {
            return jsdate.getDate();
        },
        l: function () {
            return txt_weekdays[f.w()];
        },
        N: function () {
            return f.w() + 1;
        },
        S: function () {
            return txt_ordin[f.j()] ? txt_ordin[f.j()] : 'th';
        },
        w: function () {
            return jsdate.getDay();
        },
        z: function () {
            return (jsdate - new Date(jsdate.getFullYear() + "/1/1")) / 864e5 >> 0;
        },

        // Week
        W: function () {
            var a = f.z(), b = 364 + f.L() - a;
            var nd2, nd = (new Date(jsdate.getFullYear() + "/1/1").getDay() || 7) - 1;

            if (b <= 2 && ((jsdate.getDay() || 7) - 1) <= 2 - b) {
                return 1;
            } else {

                if (a <= 2 && nd >= 4 && a >= (6 - nd)) {
                    nd2 = new Date(jsdate.getFullYear() - 1 + "/12/31");
                    return date("W", Math.round(nd2.getTime() / 1000));
                } else {
                    return (1 + (nd <= 3 ? ((a + nd) / 7) : (a - (7 - nd)) / 7) >> 0);
                }
            }
        },

        // Month
        F: function () {
            return txt_months[f.n()];
        },
        m: function () {
            return pad(f.n(), 2);
        },
        M: function () {
            t = f.F();
            return t.substr(0, 3);
        },
        n: function () {
            return jsdate.getMonth() + 1;
        },
        t: function () {
            var n;
            if ((n = jsdate.getMonth() + 1) == 2) {
                return 28 + f.L();
            } else {
                if (n & 1 && n < 8 || !(n & 1) && n > 7) {
                    return 31;
                } else {
                    return 30;
                }
            }
        },

        // Year
        L: function () {
            var y = f.Y();
            return (!(y & 3) && (y % 1e2 || !(y % 4e2))) ? 1 : 0;
        },
        //o not supported yet
        Y: function () {
            return jsdate.getFullYear();
        },
        y: function () {
            return (jsdate.getFullYear() + "").slice(2);
        },

        // Time
        a: function () {
            return jsdate.getHours() > 11 ? "pm" : "am";
        },
        A: function () {
            return f.a().toUpperCase();
        },
        B: function () {
            // peter paul koch:
            var off = (jsdate.getTimezoneOffset() + 60) * 60;
            var theSeconds = (jsdate.getHours() * 3600) +
                (jsdate.getMinutes() * 60) +
                jsdate.getSeconds() + off;
            var beat = Math.floor(theSeconds / 86.4);
            if (beat > 1000) {
                beat -= 1000;
            }
            if (beat < 0) {
                beat += 1000;
            }
            if ((String(beat)).length == 1) {
                beat = "00" + beat;
            }
            if ((String(beat)).length == 2) {
                beat = "0" + beat;
            }
            return beat;
        },
        g: function () {
            return jsdate.getHours() % 12 || 12;
        },
        G: function () {
            return jsdate.getHours();
        },
        h: function () {
            return pad(f.g(), 2);
        },
        H: function () {
            return pad(jsdate.getHours(), 2);
        },
        i: function () {
            return pad(jsdate.getMinutes(), 2);
        },
        s: function () {
            return pad(jsdate.getSeconds(), 2);
        },
        //u not supported yet

        // Timezone
        //e not supported yet
        //I not supported yet
        O: function () {
            var t = pad(Math.abs(jsdate.getTimezoneOffset() / 60 * 100), 4);
            if (jsdate.getTimezoneOffset() > 0) {
                t = "-" + t;
            } else {
                t = "+" + t;
            }
            return t;
        },
        P: function () {
            var O = f.O();
            return (O.substr(0, 3) + ":" + O.substr(3, 2));
        },
        //T not supported yet
        //Z not supported yet

        // Full Date/Time
        c: function () {
            return f.Y() + "-" + f.m() + "-" + f.d() + "T" + f.h() + ":" + f.i() + ":" + f.s() + f.P();
        },
        //r not supported yet
        U: function () {
            return Math.round(jsdate.getTime() / 1000);
        }
    };

    return format.replace(/[\\]?([a-zA-Z])/g, function (t, s) {
        if (t != s) {
            // escaped
            ret = s;
        } else if (f[s]) {
            // a date function exists
            ret = f[s]();
        } else {
            // nothing special
            ret = s;
        }

        return ret;
    });
}
