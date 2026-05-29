# Cache Headers API

## Filter: `rocket_cache_http_headers`

**Since:** 3.x.x
**File:** `inc/classes/Buffer/class-cache.php` — `WP_Rocket\Buffer\Cache::send_headers()`

Filters the HTTP response headers sent when WP Rocket serves a page from its file cache.
This filter fires inside the early-bootstrap `advanced-cache.php` drop-in, before WordPress
core, plugins, and mu-plugins have loaded. Standard hooks registered via `add_action('init', ...)`
are not available at this point.

### Signature

```php
apply_filters( 'rocket_cache_http_headers', array $headers )
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$headers` | `array<string,string>` | Associative array of header name to header value. Only entries where both key and value are strings are sent. |

**Return:** `array<string,string>` — The filtered headers array.

### When it fires

The filter fires in the following cache-serving paths:

- `serve_cache_file()` — plain HTML cache, `Last-Modified` header on every request, plus `Expires` and `Cache-Control` on 304 Not Modified responses.
- `serve_gzip_cache_file()` — gzip cache, same paths as above.

The HTTP status line (e.g. `HTTP/1.1 304 Not Modified`) is sent directly via `header()` with an explicit status code and is not passed through this filter.

### Security

`send_headers()` applies three layers of defence before calling `header()`:

1. **Type guard** — entries where the key or value is not a string are silently skipped. This prevents PHP type-juggling from passing unexpected values to `header()`.
2. **CRLF guard** — entries where the header name or value contains a carriage-return (`\r`, `0x0D`) or line-feed (`\n`, `0x0A`) character are silently dropped. This prevents HTTP response-splitting on PHP versions that do not block CRLF in `header()` natively (PHP < 7.4).
3. **Non-array return cast** — if the filter returns a non-array value it is cast to `array` before iteration, preventing a fatal error from a badly-written callback.

**What is not prevented:** The CRLF guard does not validate that the header name is a legitimate RFC 7230 token. Unusual but technically valid characters (e.g. `!`, `#`, `~`) in header names are passed through unchanged. Callers are responsible for ensuring header names are well-formed.

### Registering callbacks

Because `advanced-cache.php` runs before plugins are loaded, callbacks cannot be registered
via `plugins_loaded` or `init`. There are two supported registration points:

1. **Early-hook drop-in file** (recommended): Create `wp-content/rocket-early-cache-hooks.php`. WP Rocket includes this file at the start of `advanced-cache.php` execution if it exists. Place `add_filter()` calls at file scope (not inside any hook callback):

    ```php
    <?php
    // wp-content/rocket-early-cache-hooks.php
    add_filter(
        'rocket_cache_http_headers',
        function ( array $headers ) {
            $headers['X-Cache-Provider'] = 'WP Rocket';
            return $headers;
        }
    );
    ```

2. **mu-plugins** are NOT loaded before `advanced-cache.php`, so mu-plugins cannot register callbacks for this filter.

### Early-hook drop-in security

The inclusion of `rocket-early-cache-hooks.php` uses `realpath()` with a strict path-containment check to prevent directory traversal. A trailing `DIRECTORY_SEPARATOR` is appended to the content-directory prefix so that a sibling directory (e.g. `wp-content_evil/`) cannot pass the check by sharing a common prefix:

```php
$rocket_early_hooks = WP_CONTENT_DIR . '/rocket-early-cache-hooks.php';
if ( file_exists( $rocket_early_hooks ) ) {
    $rocket_early_hooks = realpath( $rocket_early_hooks );
    if ( $rocket_early_hooks && 0 === strpos( $rocket_early_hooks, rtrim( realpath( WP_CONTENT_DIR ), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR ) ) {
        include_once $rocket_early_hooks;
    }
}
unset( $rocket_early_hooks );
```

If `rocket-early-cache-hooks.php` throws an uncaught exception or causes a PHP fatal error, the entire page request will fail. Exception handling is the responsibility of the file author.

### Known limitation

WordPress's `wp_headers` filter and `send_headers` action do not fire for cached responses, because the WordPress bootstrap never completes for those requests. The `rocket_cache_http_headers` filter is the supported replacement for adding custom HTTP headers to cached responses.
