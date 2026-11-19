---
name: wp-rocket-frontend-architecture
description: Use this skill when changing admin JavaScript, CSS, or HTML/Twig templates in WP Rocket.
---

# WP Rocket — Frontend Architecture

Guidelines for JavaScript, CSS, and template changes in WP Rocket's admin UI.

## Core principles

- No jQuery — use native DOM APIs only.
- No inline event handlers (`onclick`, `onchange`, etc.) — use `addEventListener`.
- No unsafe `innerHTML` assignments — use `textContent` or `createElement`/`appendChild`.
- Use event delegation where appropriate (admin pages can load content dynamically).

## Admin settings UI

- Settings page templates live in `views/`.
- JS for the settings page lives in `assets/js/`.
- Use `wp_localize_script` to pass PHP data to JS — never inline `<script>` blocks with data.
- Nonces must be localized — never hardcoded.
- New JS entry points must be registered and enqueued through a Subscriber.

## AJAX / REST

- Use native `fetch` with the localized nonce for REST calls.
- Use `wp.ajax` for legacy AJAX (`admin-ajax.php`) if already present in the area.
- Always handle errors explicitly — no silent failures.

## Structural expectations

- JS files live under `assets/js/`.
- CSS files live under `assets/css/`.
- No new global variables — use module patterns or data attributes to share state.
