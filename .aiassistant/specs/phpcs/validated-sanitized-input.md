# SDD: WordPress.Security.ValidatedSanitizedInput

## Goal
Ensure any input read from GET/POST/COOKIE/SERVER/REQUEST is unslashed, validated,
and sanitized before use, reducing PHPCS warnings and security risk.

## Scope
Files in `inc/`, `views/`, and `src/` that read superglobals or external input.

## Project discovery (before changing)
1. Map access to `$_GET`, `$_POST`, `$_REQUEST`, and `$_SERVER`.
2. Reuse existing patterns:
   - `sanitize_text_field( wp_unslash( $_GET['foo'] ) )`
   - `sanitize_email( wp_unslash( $_POST['email'] ) )`
   - `absint( wp_unslash( $_GET['id'] ) )`
   - `sanitize_key()` + allowlist (`in_array`) for filters/ordering
   - `array_map( 'absint', (array) wp_unslash( $_GET['ids'] ) )`
3. Verify nonces for admin actions (see
   `.aiassistant/specs/phpcs/nonce-verification-recommended.md`).

## Decision (quick tree)
1. Is the input from a superglobal? If yes, apply `wp_unslash()`.
2. Is there a clear type/allowlist? Validate before use.
3. Sanitize with the correct function for the context.
4. If invalid/missing, use a safe default.

## Implementation patterns
- **MissingUnslash**: always `wp_unslash()` before `sanitize_*`.
- **InputNotSanitized**: apply `sanitize_*` or `esc_url_raw()`.
- **InputNotValidated**: validate with allowlist, `absint`, `filter_var()`, or controlled regex.
- Prefer `filter_input()`/`filter_input_array()` with `FILTER_SANITIZE_*` when possible.
- If `filter_input()` returns `null` but `$_GET`/`$_POST` is set, fall back to
  `sanitize_text_field( wp_unslash( $_GET['field'] ) )` for compatibility.
- Avoid `$_REQUEST`; prefer `$_GET` or `$_POST`.
- For arrays, sanitize each item with `array_map`.
- For URLs: `esc_url_raw()` + optionally `filter_var( ..., FILTER_VALIDATE_URL )`.
- For multiline text: `sanitize_textarea_field()` (do not use `sanitize_text_field`).
- For allowed HTML: `wp_kses_post()` (only when needed and documented).
- For boolean flags: `filter_input( INPUT_POST, 'flag', FILTER_VALIDATE_BOOLEAN )`
  or `(bool) filter_input(...)`.
- For filters/ordering (GET without side effects): validate nonce and allowlist.

## Examples
```php
check_admin_referer( 'rocket_settings_page' );
$value = isset( $_POST['option'] )
    ? sanitize_text_field( wp_unslash( $_POST['option'] ) )
    : '';
```

```php
$allowed = [ 'all', 'active', 'inactive' ];
$status = isset( $_GET['status'] )
    ? sanitize_key( wp_unslash( $_GET['status'] ) )
    : 'all';
$status = in_array( $status, $allowed, true ) ? $status : 'all';
```

```php
$ids = isset( $_GET['urls'] )
    ? array_map( 'esc_url_raw', (array) wp_unslash( $_GET['urls'] ) )
    : [];
```

## Git Operations
Do not run `git commit` or `git push`. You may only suggest a commit message.

## Verification
- Run `composer phpcs` and confirm there are no:
  - `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized`
  - `WordPress.Security.ValidatedSanitizedInput.InputNotValidated`
  - `WordPress.Security.ValidatedSanitizedInput.MissingUnslash`
