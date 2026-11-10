// ==ClosureCompiler==
// @compilation_level SIMPLE_OPTIMIZATIONS
// @output_file_name mixpanel-jslib-2.2-snippet.min.js
// ==/ClosureCompiler==

// -----------------------------------------------------------------------------
// VENDORED THIRD-PARTY FILE — Mixpanel JS library loader snippet.
//
// Source: https://github.com/mixpanel/mixpanel-js
//         src/loaders/mixpanel-jslib-snippet.js (snippet version __SV 1.2)
//
// This snippet creates the window.mixpanel stub that queues calls until the
// full library (assets/vendor/mixpanel.min.js) finishes loading. The library
// REQUIRES this stub — loaded on its own it logs '"mixpanel" object not
// initialized' and never defines window.mixpanel.
//
// LOCAL MODIFICATION (1): the upstream default MIXPANEL_LIB_URL pointed at
// //cdn.mxpnl.com. WordPress.org guidelines forbid loading third-party scripts
// from a CDN, so the default is emptied — the library URL is always supplied via
// MIXPANEL_CUSTOM_LIB_URL (set by class-chat-widget.php to our vendored copy).
// A misconfiguration then fails locally instead of quietly reaching out to a
// third-party host.
//
// LOCAL MODIFICATION (2): the injection is guarded on a non-empty src — see the
// comment at that line. Emptying the default alone is NOT silent: an empty src
// resolves to the current document URL, so upstream's unconditional insert
// would re-request the page and parse the HTML as JS.
//
// Do not edit otherwise; re-vendor from upstream to update, and re-apply both
// modifications above.
// -----------------------------------------------------------------------------

/** @define {string} */
var MIXPANEL_LIB_URL = '';

(function(document, mixpanel) {
    // Only stub out if this is the first time running the snippet.
    if (!mixpanel['__SV']) {
        var script, first_script, gen_fn, functions, i, lib_name = "mixpanel";
        window[lib_name] = mixpanel;

        mixpanel['_i'] = [];

        mixpanel['init'] = function (token, config, name) {
            // support multiple mixpanel instances
            var target = mixpanel;
            if (typeof(name) !== 'undefined') {
                target = mixpanel[name] = [];
            } else {
                name = lib_name;
            }

            // Pass in current people object if it exists
            target['people'] = target['people'] || [];
            target['toString'] = function(no_stub) {
                var str = lib_name;
                if (name !== lib_name) {
                    str += "." + name;
                }
                if (!no_stub) {
                    str += " (stub)";
                }
                return str;
            };
            target['people']['toString'] = function() {
                // 1 instead of true for minifying
                return target.toString(1) + ".people (stub)";
            };

            function _set_and_defer(target, fn) {
                var split = fn.split(".");
                if (split.length == 2) {
                    target = target[split[0]];
                    fn = split[1];
                }
                target[fn] = function() {
                    target.push([fn].concat(Array.prototype.slice.call(arguments, 0)));
                };
            }

            // create shallow clone of the public mixpanel interface
            // Note: only supports 1 additional level atm, e.g. mixpanel.people.set, not mixpanel.people.set.do_something_else.
            functions = "disable time_event track track_pageview track_links track_forms track_with_groups add_group set_group remove_group register register_once alias unregister identify name_tag set_config reset opt_in_tracking opt_out_tracking has_opted_in_tracking has_opted_out_tracking clear_opt_in_out_tracking start_batch_senders start_session_recording stop_session_recording people.set people.set_once people.unset people.increment people.append people.union people.track_charge people.clear_charges people.delete_user people.remove".split(' ');
            for (i = 0; i < functions.length; i++) {
                _set_and_defer(target, functions[i]);
            }

            // special case for get_group(): chain method calls like mixpanel.get_group('foo', 'bar').unset('baz')
            var group_functions = "set set_once union unset remove delete".split(' ');
            target['get_group'] = function() {
                var mock_group = {};

                var call1_args = arguments;
                var call1 = ['get_group'].concat(Array.prototype.slice.call(call1_args, 0));

                function _set_and_defer_chained(fn_name) {
                    mock_group[fn_name] = function() {
                        var call2_args = arguments;
                        var call2 = [fn_name].concat(Array.prototype.slice.call(call2_args, 0));
                        target.push([call1, call2]);
                    };
                }
                for (var i = 0; i < group_functions.length; i++) {
                    _set_and_defer_chained(group_functions[i]);
                }
                return mock_group;
            };

            // register mixpanel instance
            mixpanel['_i'].push([token, config, name]);
        };

        // Snippet version, used to fail on new features w/ old snippet
        mixpanel['__SV'] = 1.2;

        script = document.createElement("script");
        script.type = "text/javascript";
        script.async = true;

        if (typeof MIXPANEL_CUSTOM_LIB_URL !== 'undefined') {
            script.src = MIXPANEL_CUSTOM_LIB_URL;
        } else if (document.location.protocol === 'file:' && MIXPANEL_LIB_URL.match(/^\/\//)) {
            script.src = 'https:' + MIXPANEL_LIB_URL;
        } else {
            script.src = MIXPANEL_LIB_URL;
        }

        // LOCAL MODIFICATION (2): upstream inserts unconditionally. With the CDN
        // default emptied above, a missing MIXPANEL_CUSTOM_LIB_URL leaves src ''
        // — which resolves to the current document URL, so the browser would
        // re-request the page (cookie-bearing) and parse the HTML as JS. Bail
        // instead; wap-chat.js also refuses to inject this file without a
        // library URL, so reaching here means the host set the global to ''.
        if (script.src) {
            first_script = document.getElementsByTagName("script")[0];
            first_script.parentNode.insertBefore(script, first_script);
        }
    }
// Pass in current Mixpanel object if it exists (for ppl like Optimizely)
})(document, window['mixpanel'] || []);
