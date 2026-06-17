---
name: compliance
description: Check a change against WordPress.org plugin rules and PHPCS standards.
---

# WordPress Compliance

Ensure compatibility with:
- WordPress Plugin Check
- Repository PHPCS rules
- WordPress.org expectations

## Config

This skill targets `wp-media/wp-rocket`:
- `TEXT_DOMAIN` = `rocket`
- `CAPABILITIES` = WP Rocket's registered custom capabilities (e.g. `rocket_manage_options`)

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

Use the `rocket` text domain for all translation calls:

```php
esc_html__( 'Clear Cache', 'rocket' )
esc_attr__( 'WP Rocket Settings', 'rocket' )
```

## Custom capabilities

WP Rocket registers custom capabilities. Always use those (not `manage_options`) for
capability checks. PHPCS is configured to allow them without warnings:

```php
// Correct — use the WP Rocket–registered capability, not manage_options directly
current_user_can( 'rocket_manage_options' )
```

Using `manage_options` directly for WP Rocket–specific actions is incorrect and will flag
in code review.

## JavaScript

- Do not use jQuery. Use native DOM APIs (`document.querySelector`, `addEventListener`, `fetch`, etc.).
- jQuery is available in WordPress but its use introduces an unnecessary dependency and conflicts with modern bundling practices.

## Anti-patterns

- Echoing raw variables
- Introducing unescaped output
- Storing sensitive values in plain text
- Bypassing repository PHPCS configuration
- Using jQuery in new or modified JS code

## Related Specs

When relevant, consult repository specs under `.claude/specs/`, especially:

- `.claude/specs/phpcs/nonce-verification-recommended.md`
- `.claude/specs/phpcs/validated-sanitized-input.md`
- `.claude/specs/phpcs/escaped-output.md`

## Git Operations
Follow the policy defined in AGENTS.md §5.1. Outside the issue workflow, do not run `git commit` or `git push`.
