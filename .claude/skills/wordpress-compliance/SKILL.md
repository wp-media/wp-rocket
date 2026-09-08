---
name: wordpress-compliance
description: Use this skill when modifying templates, admin UI, output, hooks, plugin metadata, sanitization, escaping, or any code that must remain compliant with WordPress.org and repository PHPCS rules.
---

# WordPress Compliance

Ensure compatibility with:
- WordPress Plugin Check
- Repository PHPCS rules
- WordPress.org expectations

## Responsibilities

- Respect repository PHPCS configuration.
- Follow WordPress escaping standards.
- Avoid forbidden or deprecated APIs.
- Avoid direct access to superglobals without sanitization.
- Ensure output is escaped for context.

## Escaping heuristics

HTML text: `esc_html()`
HTML attribute: `esc_attr()`
URL: `esc_url()`
Allowed HTML: `wp_kses_post()`

## Text domain

WP Rocket uses the text domain `rocket`.

```php
esc_html__( 'Clear Cache', 'rocket' )
esc_attr__( 'WP Rocket Settings', 'rocket' )
```

## Custom capabilities

WP Rocket registers custom capabilities. Always use these (not `manage_options`) for capability checks. PHPCS is configured to allow them without warnings:

```php
current_user_can( 'rocket_manage_options' )        // general plugin management
current_user_can( 'rocket_purge_cache' )            // clear/purge cache
current_user_can( 'rocket_preload_cache' )          // preload cache
current_user_can( 'rocket_remove_unused_css' )      // RUCSS
current_user_can( 'rocket_regenerate_critical_css' ) // critical CSS
current_user_can( 'rocket_purge_cloudflare_cache' ) // Cloudflare
current_user_can( 'rocket_purge_sucuri_cache' )     // Sucuri
current_user_can( 'rocket_purge_posts' )
current_user_can( 'rocket_purge_terms' )
current_user_can( 'rocket_purge_users' )
```

Using `manage_options` directly for WP Rocket–specific actions is incorrect and will flag in code review.

## JavaScript

- Do not use jQuery. Use native DOM APIs (`document.querySelector`, `addEventListener`, `fetch`, etc.).
- jQuery is available in WordPress but its use introduces an unnecessary dependency and conflicts with modern bundling practices.

## Anti-patterns

- Echoing raw variables
- Introducing unescaped output
- Storing sensitive values in plain text
- Writing files containing secrets/credentials (API keys, tokens, license data) to a path under `wp-content/` without a real access-control mechanism. `.htaccess deny` alone does not block Nginx, and an `index.php` stub in the directory does not block direct requests to sibling static files (both web servers serve those straight from disk, bypassing PHP entirely) — neither is sufficient by itself. Store the data outside the web-served tree, or serve it only through an authenticated PHP handler (e.g. an admin-ajax/REST callback gated by `current_user_can()`) instead of a directly fetchable static file
- Bypassing repository PHPCS configuration
- Using jQuery in new or modified JS code

## Related Specs

When relevant, consult repository specs under `.claude/specs/`, especially:

- `.claude/specs/phpcs/nonce-verification-recommended.md`
- `.claude/specs/phpcs/validated-sanitized-input.md`
- `.claude/specs/phpcs/escaped-output.md`

## Git Operations
Follow the policy defined in AGENTS.md §5.1. Outside the issue workflow, do not run `git commit` or `git push`.
