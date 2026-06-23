# MCP Abilities — Developer API

WP Rocket exposes WordPress Abilities (MCP-compatible) for reading and writing plugin
options. The abilities live under `inc/Engine/Abilities/` and are gated by the
`rocket_manage_options` capability.

## Filters

### `rocket_mcp_options_allowlist`

**Type:** `array`  
**Defined in:** `inc/Engine/Abilities/Options/AllowedOptions.php`

Controls which WP Rocket option keys are accessible via the `wp-rocket/get-options` and
`wp-rocket/set-option` MCP abilities. Both abilities read from the same allowlist, so a
single filter callback controls the full read/write surface.

**Parameters:**

| # | Type | Description |
|---|------|-------------|
| 1 | `string[]` | Flat array of option key strings. |

**Returns:** `string[]` — the (possibly modified) list of allowed option keys.

**Usage:**

```php
// Expose a custom option key to MCP abilities.
add_filter(
    'rocket_mcp_options_allowlist',
    function ( array $allowlist ): array {
        $allowlist[] = 'my_plugin_option_key';
        return $allowlist;
    }
);

// Restrict — remove a key so it cannot be read or written via AI.
add_filter(
    'rocket_mcp_options_allowlist',
    function ( array $allowlist ): array {
        return array_values( array_diff( $allowlist, [ 'cdn' ] ) );
    }
);
```

**Notes:**

- Keys added via the filter that are not in `SetOption`'s type-routing constants fall
  through to the untyped sanitizer fallback (`return $option_value`). Advanced users who
  add custom keys own their sanitization.
- The `cdn_type` value `rocketcdn` is intentionally absent from the default allowlist;
  RocketCDN configuration requires manual setup and must not be changed via AI.
- The filter fires on every call to `AllowedOptions::get()`, which is called once each
  from `GetOptions::register()`, `GetOptions::execute()`, and `SetOption::register()` /
  `SetOption::execute()`. Keep the callback lightweight.

---

## Container bindings

The following services are registered by `WP_Rocket\Engine\Abilities\ServiceProvider`:

| Container key              | Class                                             | Lifetime |
|----------------------------|---------------------------------------------------|----------|
| `abilities_allowed_options`| `WP_Rocket\Engine\Abilities\Options\AllowedOptions` | shared   |
| `abilities_get_options`    | `WP_Rocket\Engine\Abilities\Options\GetOptions`   | factory  |
| `abilities_set_option`     | `WP_Rocket\Engine\Abilities\Options\SetOption`    | factory  |
| `abilities_context`        | `WP_Rocket\Engine\Abilities\Context`              | shared   |
| `abilities_subscriber`     | `WP_Rocket\Engine\Abilities\Options\Subscriber`   | shared   |
