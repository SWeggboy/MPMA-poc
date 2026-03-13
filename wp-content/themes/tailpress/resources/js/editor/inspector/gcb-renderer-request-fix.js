/**
 * Prevent oversized GCB renderer URLs by moving inner_blocks out of query args.
 */
(function () {
    if (!window.wp || !wp.apiFetch || !wp.apiFetch.use) {
        return;
    }

    if (window.tailpressGcbRendererRequestFixLoaded) {
        return;
    }
    window.tailpressGcbRendererRequestFixLoaded = true;

    var TARGET_PREFIX = '/wp/v2/block-renderer/genesis-custom-blocks/';

    function rewritePath(path, data) {
        if (typeof path !== 'string' || path.indexOf(TARGET_PREFIX) === -1) {
            return null;
        }

        var queryIndex = path.indexOf('?');
        if (queryIndex === -1) {
            return null;
        }

        var basePath = path.slice(0, queryIndex);
        var query = path.slice(queryIndex + 1);
        var params = new URLSearchParams(query);
        var innerBlocks = params.get('inner_blocks');

        if (!innerBlocks) {
            return null;
        }

        params.delete('inner_blocks');

        return {
            path: basePath + (params.toString() ? '?' + params.toString() : ''),
            data: Object.assign({}, data || {}, { inner_blocks: innerBlocks }),
        };
    }

    wp.apiFetch.use(function (options, next) {
        if (!options || typeof options !== 'object') {
            return next(options);
        }

        try {
            var rewritten = rewritePath(options.path, options.data);

            if (!rewritten && typeof options.url === 'string') {
                var parsedUrl = new URL(options.url, window.location.origin);
                rewritten = rewritePath(parsedUrl.pathname + parsedUrl.search, options.data);
                if (rewritten) {
                    rewritten.url = parsedUrl.origin + rewritten.path;
                }
            }

            if (!rewritten) {
                return next(options);
            }

            var nextOptions = Object.assign({}, options, {
                path: rewritten.path,
                method: options.method || 'POST',
                data: rewritten.data,
            });

            if (rewritten.url) {
                nextOptions.url = rewritten.url;
            } else if (Object.prototype.hasOwnProperty.call(nextOptions, 'url')) {
                delete nextOptions.url;
            }

            return next(nextOptions);
        } catch (error) {
            return next(options);
        }
    });
})();
