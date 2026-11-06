# SDD: WordPress.Security.NonceVerification.Recommended

## Goal
Ensure that any request data processing in an admin/UI context includes a proper nonce
verification without breaking existing flows. When the flow is not user-driven
(cron, webhooks, external jobs), apply equivalent authentication/validation and
document why a nonce does not apply.

## Scope
Files in `inc/` that emit the warning `WordPress.Security.NonceVerification.Recommended`.

## Project discovery (before changing)
1. Map existing patterns with a search for `check_admin_referer`, `wp_verify_nonce`, `wp_nonce_field`.
2. Reuse the screen's existing action/field whenever possible.
3. Reuse existing helpers when applicable.
4. If no helper fits, create a private method in the class.
5. Do not invent a new action if the screen already uses an action.

## Out of scope
- Do not add `phpcs:ignore` for `NonceVerification.Recommended` by default.
- Do not change how public endpoints work without validating impact on external integrations.

## Decision (quick tree)
1. Is it a user action on an admin screen?
   - Yes: add required nonce.
   - No: go to 2.
2. Is it cron/CLI/external endpoint with its own auth (token/secret)?
   - Yes: validate that mechanism and document why nonce does not apply. Use `wp_unslash` + sanitization; avoid nonce.
   - No: re-evaluate. Prefer adding a nonce or a simple auth token.

## Strategy by use type
### 1) Admin forms and actions (POST)
- Add `wp_nonce_field( 'action_name', 'nonce_field' )` in the form.
- In the handler, call `check_admin_referer( 'action_name', 'nonce_field' )` before reading data.
- Always `wp_unslash()` before sanitizing (`sanitize_text_field`, `absint`, etc.).

### 2) List filters (GET) without side effects
- If only ordering/filter UI:
  - Verify nonce (`wp_verify_nonce`) and only use values when valid.
  - If invalid/missing, fall back to defaults (no aggressive error/redirect).

### 3) Cron / automatic endpoints
- If existing auth exists (key, hash, token):
  - Validate explicitly and document why nonce does not apply.
- If no auth exists:
  - Propose a simple token in query string or header, and only add nonce for admin endpoints.

## Implementation patterns
- Always use `wp_unslash()` before sanitizing.
- Prefer `check_admin_referer()` when state changes.
- For GET filters/ordering, prefer `wp_verify_nonce()` and a safe fallback.
- Do not change external behavior without reviewing usage (search/grep for callers).

## Example (admin POST)
```php
// Form
wp_nonce_field( 'rocket_clear_cache', 'rocket_clear_cache_nonce' );

// Handler
check_admin_referer( 'rocket_clear_cache', 'rocket_clear_cache_nonce' );
$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
```

## Example (GET filter)
```php
$nonce = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$nonce = $nonce ? sanitize_text_field( $nonce ) : '';
if ( $nonce && wp_verify_nonce( $nonce, 'rocket_list_filter' ) ) {
    $order = sanitize_key( wp_unslash( $_GET['order'] ?? '' ) );
} else {
    $order = 'desc';
}
```

## Git Operations
Do not run `git commit` or `git push`. You may only suggest a commit message.

## Verification
- Review each warning and classify by type (admin POST, GET filter, cron/external).
- Check impacts on tests and integrations before irreversible changes.
