# SDD: WordPress.Security.EscapeOutput.OutputNotEscaped

## Goal
Ensure all HTML output is escaped in the correct context, reducing PHPCS warnings
and preventing XSS.

## Scope
Templates and PHP that use `echo`, `print`, `printf` in `views/`, `inc/`, and `src/`.

## Project discovery (before changing)
1. Map all output points.
2. Reuse existing patterns:
   - `esc_html__`, `esc_html_e`, `esc_attr__`, `esc_attr_e`
   - `esc_html()`, `esc_attr()`, `esc_url()`
   - `wp_kses()` and `wp_kses_post()` when HTML is allowed
3. When a translated string contains HTML, use `wp_kses()` with an allowlist.

## Decision by context
- HTML text: `esc_html()`
- HTML attribute / `data-*`: `esc_attr()`
- URL: `esc_url()`
- Inline JS: `esc_js()` or `wp_json_encode()` + `esc_attr()`
- Allowed HTML: `wp_kses_post()` or `wp_kses( $html, $allowed_html )`

## Implementation patterns
- Escape at the output boundary, not at the source.
- Use pre-escaped translation helpers (`esc_html__`, `esc_attr__`, etc).
- For `printf`/`sprintf`, escape each variable before injecting.
- Avoid raw `echo $html`; use `wp_kses` with an explicit allowlist.
- Do not double-escape when the value is already escaped.
- Do not use `echo esc_html_e()`/`echo esc_attr_e()` (these already echo).
- For dynamic classes/ids, prefer `esc_attr()` or `sanitize_html_class()` as appropriate.

## Examples
```php
echo esc_html( $message );
```

```php
printf(
    esc_html__( 'Cache cleared for %s.', 'rocket' ),
    esc_html( $url )
);
```

```php
echo wp_kses(
    sprintf(
        __( 'See <a href="%s">documentation</a>.', 'rocket' ),
        esc_url( $url )
    ),
    [ 'a' => [ 'href' => true, 'target' => true, 'rel' => true ] ]
);
```

```php
echo esc_url( $link );
```

## Exceptions
- Only for internally built and validated HTML, always run through `wp_kses`.
- Never ignore `OutputNotEscaped` without a clear justification and review.

## Git Operations
Do not run `git commit` or `git push`. You may only suggest a commit message.
