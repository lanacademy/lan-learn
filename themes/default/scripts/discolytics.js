/* 
    Discolytics v1.0
    http://discolytics.com
    By: Jacob Gable, Copyright 2012    

    Uses: 
    Queue.js
    Created by Stephen Morley - http://code.stephenmorley.org/ - and released under
    the terms of the CC0 1.0 Universal legal code: 
    http://creativecommons.org/publicdomain/zero/1.0/legalcode
    ==
    Parts of jQuery: core.js : $.extend
    Created by John Resig - http://github.com/jquery/jquery
    MIT License
*/

var _dq = _dq || [];
window._dq = _dq;

window.disco = (function (undefined) {
    "use strict";

    var hasStorage = typeof (localStorage) !== "undefined",
    /* Queue.js, for our in-memory persistence provider */
    Queue = function () { var a = []; var b = 0; this.getLength = function () { return a.length - b }; this.isEmpty = function () { return a.length == 0 }; this.enqueue = function (b) { a.push(b) }; this.dequeue = function () { if (a.length == 0) return undefined; var c = a[b]; if (++b * 2 >= a.length) { a = a.slice(b); b = 0 } return c }; this.peek = function () { return a.length > 0 ? a[b] : undefined } },
        $ = {
            //$.extend from jQuery core.js
            extend: function () { var options, name, src, copy, copyIsArray, clone, target = arguments[0] || {}, i = 1, length = arguments.length, deep = false; if (typeof target === "boolean") { deep = target; target = arguments[1] || {}; i = 2 } if (typeof target !== "object" && !jQuery.isFunction(target)) { target = {} } if (length === i) { return; } for (; i < length; i++) { if ((options = arguments[i]) != null) { for (name in options) { src = target[name]; copy = options[name]; if (target === copy) { continue } if (deep && copy && (jQuery.isPlainObject(copy) || (copyIsArray = jQuery.isArray(copy)))) { if (copyIsArray) { copyIsArray = false; clone = src && jQuery.isArray(src) ? src : [] } else { clone = src && jQuery.isPlainObject(src) ? src : {} } target[name] = jQuery.extend(deep, clone, copy) } else if (copy !== undefined) { target[name] = copy } } } } return target }
        };

    // A helper for serializing our tracking data to a URL encoded string.
    var serialize = {
        urlLimit: 2000,
        forUpload: function (key, dataProv) {
            // This method will build up a url string from the data passed in.

            var keys = [],
                currEvt = null,
                events = [],
                preamble = "ApiKey=" + key,
                eventStr = null,
                length = preamble.length + 1, // + 1 for &
                count = 0,
                done = false;

            keys.push(preamble);

            currEvt = dataProv.peek();
            done = currEvt === undefined;
            eventStr = this.encodeEvent(currEvt, count);


            while ((length + (eventStr.length + 1)) < this.urlLimit && !done) {

                length += eventStr.length + 1;
                count++;

                keys.push(eventStr);
                if (currEvt) { events.push(dataProv.get(1)[0]); }

                currEvt = dataProv.peek();
                done = currEvt === undefined;
                eventStr = this.encodeEvent(currEvt, count);
            }

            return { count: events.length, events: events, query: "?" + keys.join("&") };
        },

        encodeEvent: function (obj, index) {
            var str = [],
                l = null,
                r = null;

            for (var p in obj) {
                if (!obj.hasOwnProperty(p)) { continue; }

                l = "d[" + index + "]." + p;
                r = encodeURIComponent(obj[p]);
                str.push(l + "=" + r);
            }

            return str.join("&");
        }
    };

    // returns a new object extended from a base settings object
    var settingsBase = function (obj) {
        return $.extend({
            settings: {
                trackUrl: "//discolytics.com/track.gif",
                apiKey: undefined,
                autoStart: true,
                processDelayMs: 1000 * 10 /* 10 Second delay */
            },

            options: function (newOpts) {
                if (newOpts) {
                    $.extend(this.settings, newOpts);
                }

                return this.settings;
            }
        }, obj);
    };

    // Our main disco object.
    var d = settingsBase({

        init: function (opts) {
            // Updates the settings with the options.
            this.options(opts);

            processor.init(this.settings);
        },

        trackPage: function (name, context, tsDate) {
            // Add a new track record to the queue for uploading.
            this._commonTrack("Page", name, context, tsDate);
        },

        trackEvent: function (name, context, tsDate) {
            // Add a new event record to the queue for uploading.
            this._commonTrack("Event", name, context, tsDate);
        },

        clear: function () {
            processor.persistenceProvider.clear();
        },

        // Used internally.
        _commonTrack: function (type, name, context, tsDate) {
            processor.addTracking({
                type: type,
                name: name,
                context: context,
                ts: tsDate
            });
        }
    });

    var utility = {
        _utc_timestamp: function (now) {
            return Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate(),
                      now.getUTCHours(), now.getUTCMinutes(), now.getUTCSeconds(),
                      now.getUTCMilliseconds());
        },

        createEventFromDetails: function (details) {
            // event
            return {
                // type
                t: details.type === "Page" ? 1 : 2,
                // name
                n: details.name || "/",
                // context
                c: details.context || "",
                // timestamp
                ts: this._utc_timestamp(details.ts || new Date)
            };
        }
    };

    // The processor will handle checking for online access, storing trackings for upload, and uploading.
    // TODO: may want to seperate these concerns into their own class.
    var processor = settingsBase({
        init: function (opts) {

            // Override the default options with passed in ones.
            this.options(opts);

            // Auto start
            if (this.settings.autoStart) {
                this.start();
            }
        },

        queueProvider: {
            // For testing
            name: "queue",
            queue: new Queue(),

            add: function (details) {
                if (details.length && details.length > 0) {
                    for (var d in details) {
                        this.queue.enqueue(d);
                    }
                } else {
                    this.queue.enqueue(details);
                }
            },

            get: function (count) {
                count = count || 1;
                var result = [],
                    i = 0;

                for (; i < count; i++) {
                    result.push(this.queue.dequeue());
                }

                return result;
            },

            peek: function () {
                return this.queue.peek();
            },

            length: function () {
                return this.queue.getLength();
            },

            clear: function () {
                this.queue = new Queue();
            }
        },

        storageProvider: {
            name: "storage",
            storage: localStorage,
            queueKey: "disco-queue",
            setObject: function (key, value) {
                try {
                    this.storage.setItem(key, JSON.stringify(value));
                } catch (e) {
                    if (e === QUOTA_EXCEEDED_ERR) {
                        alert('Local storage limit exceeded');
                    }

                    throw e;
                }
            },
            getObject: function (key) {
                var retrieved = this.storage.getItem(key);
                if (!retrieved) {
                    return;
                }

                return JSON.parse(retrieved);
            },
            getQueue: function () {
                return this.getObject(this.queueKey) || [];
            },
            setQueue: function (queue) {
                this.setObject(this.queueKey, queue);
            },
            add: function (details) {
                // Get the current queue, add new tracking to it, then re-store it.
                var queue = this.getQueue(),
                    i = 0,
                    event = null,
                    add = function (e) {
                        if (e.type) {
                            // Handle pushing in details
                            queue.push(utility.createEventFromDetails(e));
                        } else if (e.t) {
                            // Handle pushing events back in.
                            queue.push(e);
                        }
                    };

                if (details.length) {
                    for (; i < details.length; i++) {
                        add(details[i]);
                    }
                } else {
                    add(details);
                }

                this.setQueue(queue);
            },
            get: function (count) {
                // Get the current queue, pop (count) off, then re-store it.
                var queue = this.getQueue(),
                    result = [],
                    i = 0;

                count = count || 1;

                if (count > queue.length) {
                    count = queue.length;
                }

                for (; i < count; i++) {
                    result.push(queue[i]);
                }

                queue = queue.slice(count);

                this.setQueue(queue);

                return result;
            },

            peek: function () {
                var queue = this.getQueue();

                if (queue.length > 0) {
                    return queue[0];
                }

                return;
            },

            length: function () {
                return (this.getQueue()).length;
            },
            clear: function () {
                this.setQueue([]);
            }
        },

        addTracking: function (details) {
            // We expect a type, name, context, timestamp?
            this.persistenceProvider.add(details);
        },

        start: function () {
            var self = this;
            this.intervalId = setInterval(function () {
                self.processQueue();
            }, this.settings.processDelayMs);
        },

        stop: function () {
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = undefined;
            }
        },

        processQueue: function (doneCb) {

            // Check for online access.
            if (window.navigator && !window.navigator.onLine) {
                return;
            }

            // Check for any updates
            if (this.persistenceProvider.length() < 1) {
                return;
            }

            var trackImg = new Image(),
                self = this,
                doneCb = doneCb || function () { },
                urlResult = null;

            // Make sure to attach handlers _before_ setting src.
            trackImg.onload = function () {

                doneCb({ success: true, events: urlResult.events });
            };
            trackImg.onerror = function () {

                // TODO: Add a way to insert these at the front of the line?
                self.persistenceProvider.add(urlResult.events);

                doneCb({ success: false, events: urlResult.events });
            };

            urlResult = serialize.forUpload(this.settings.apiKey, this.persistenceProvider);

            trackImg.src = this.settings.trackUrl + urlResult.query;
        }
    });

    // Choose whether we use the storage or in-memory provider.
    processor.persistenceProvider = hasStorage ? processor.storageProvider : processor.queueProvider;

    // Export our private types for testing.
    //d.types = {
    //    Queue: Queue,
    //    Processor: processor,
    //    Serializer: serialize,
    //    Utility: utility
    //};

    if (window._dq && window._dq.length > 0) {
        // Process any queued events before we loaded.
        var i = 0,
            q = window._dq,
            curr = null;

        for (; i < q.length; i++) {
            curr = q[i];
            if (curr.length > 1 && type(d[curr[0]]) === "function") {
                d[curr[0]].apply(d, curr.slice(1));
            }
        }
    }

    return d;
})();