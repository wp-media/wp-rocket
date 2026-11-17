# WAP Client

WordPress AI Platform (WAP) client library — a drop-in Composer package for integrating an AI chat
assistant into any WordPress plugin via a single static method call.

The library is the **client ("doorway")** side only: it renders the chat widget, provisions a
WordPress Application Password (the AI's tool credential for this site), and obtains a **GRND**
(the group.one identity JWT proving the customer's entitlement, with the sealed credential
embedded). The widget sends that GRND directly to your WAP backend as the Bearer credential on
every call. The AI itself runs on a separate backend service that you host.

The bundled chat widget (`assets/wap-chat.js`) is a **platform-agnostic core**: this package wires it
up for WordPress admin pages, but any host — including SaaS apps like partners.one — can embed the
same widget by providing its own `getSession` hook (see [SaaS embedding](#saas-embedding)).

## Requirements

- PHP >= 7.4
- WordPress >= 6.0
- HTTPS (required for WordPress Application Passwords)
- On multisite, the host plugin network-activated, or this library loaded from a must-use plugin.
  Application Passwords belong to the user and work on every site of the network, and the library
  only fences them where it runs, so otherwise it mints none, revokes the WAP passwords earlier
  releases minted, and the widget shows a notice. For a loader it cannot detect, return true from
  the `wap_client_enforced_network_wide` filter, but only if the library really runs on every site.
  `\GroupOne\WapClient\AppPasswordManager::unavailable_reason()` (`'multisite'`, `'https'`,
  `'disabled'` or `''`) tells your own notices which case applies.
- A running WAP backend (the `server_url` you point to)
- A GRND issuer for your product (your brand backend's exchange endpoint, or a custom provider)

## Installation

```bash
composer require groupone/wap-client
```

## Usage

Call from your plugin's `admin_menu` action:

```php
use WapClient; // global facade defined by the package..

add_action('admin_menu', function () {
    WapClient::register_chat_page([
        'menu_slug'   => 'my-plugin-wap-chat',
        'parent_slug' => 'my-plugin-settings',
        'page_title'  => 'AI Assistant',
        'product'     => 'my-product-slug',
        'server_url'  => 'https://your-wap-backend.example.com',
        'grnd'        => [
            'issuer_url'  => 'https://api.my-brand.com/grnd/token',
            'license_key' => get_option('my_product_license_key'),
        ],
        'terms_url'   => 'https://example.com/terms',   // optional: footer "Terms of Use" link + consent gate
        'privacy_url' => 'https://example.com/privacy', // optional: footer "Privacy Policy" link
    ]);
});
```

The library handles capability gating, GRND acquisition + caching, App Password provisioning,
and widget rendering automatically.

### Who can use it: `capability`

Pass the capability a user needs for the surface. The key is the same on `register_chat_page()`,
`register_chat_column()` and `register_chat_embed()`:

```php
WapClient::register_chat_page([
    'menu_slug'  => 'my-plugin-wap-chat',
    'product'    => 'my-product-slug',
    'capability' => 'manage_options',   // or your plugin's own capability
    // …
]);
```

It gates the menu entry, the page, the assets, the mount point and the admin-ajax endpoints:

| Endpoint | Checked against |
|---|---|
| Auth (GRND minting) | The surface named by the request (`menu_slug`). The request's `product` must be that surface's product, so one surface can never mint, or cache, a GRND for another product. |
| Column state | The column named by the request. |
| Consent, Permissions | The product's surfaces: the user needs to be able to use at least one of them, and can only change the Permissions rows declared by the ones they can use. |
| Delete my data | Any registered surface the user can use. It only erases the current user's own data. |

Without `capability`, the `wap_client_capability` filter decides (default `wap_use_ai`, which only
the standalone WAP Client plugin grants, to administrators and editors, when activated). That filter
is shared by every plugin that bundles this library: a callback that ignores which surface it is
asked about gates every product's surfaces on the site, so with two such plugins, the last callback
to run decides for both. Declare `capability` instead. A declared capability is final: the filter is
not applied to it, so another plugin's callback cannot change it. A value that is not a non-empty
string closes the surface to every user and raises a `_doing_it_wrong()` notice.

If you keep a filter callback, scope it with the product and surface id it receives (both `''` when
it runs at activation):

```php
add_filter('wap_client_capability', function (string $capability, string $product = '', string $surface_id = ''): string {
    return 'my-product-slug' === $product ? 'my_plugin_capability' : $capability;
}, 10, 3);
```

### Page presentation: hidden menu, standalone document, custom body

By default `register_chat_page()` creates a normal wp-admin page with a menu entry, and the library
owns the whole page body. Three independent options change that — use only the ones you need.

| Option | Default | What it does |
|---|---|---|
| `hidden_admin_menu` | `false` | Register the page with **no menu entry anywhere**. Reachable only at `admin.php?page={menu_slug}`. Overrides `parent_slug`. |
| `render_mode` | `'admin'` | `'standalone'` emits a **bare HTML document** — no admin header, sidebar, footer or admin bar. Unrecognised values fall back to `'admin'`. |
| `standalone_shell_css` | `true` | `false` skips the library's document-shell stylesheet, so none of its `body` / `box-sizing` defaults reach the page. Only meaningful in standalone mode. |
| `render` | — | A callable that **owns the page body**. Works in either render mode. |

They compose freely: a hidden page can render with normal chrome, a standalone page can keep a menu
entry, and `render` applies to both modes.

A first-run onboarding flow that replaces a classic setup wizard typically wants the first two:

```php
WapClient::register_chat_page([
    'menu_slug'         => 'my-plugin-onboarding',
    'page_title'        => 'Set up your site',   // used as the document <title>
    'hidden_admin_menu' => true,
    'render_mode'       => 'standalone',
    'product'           => 'my-product',
    'server_url'        => 'https://your-wap-backend.example.com',
    'grnd'              => ['issuer_url' => '…', 'license_key' => '…'],
]);
```

#### `hidden_admin_menu`

Registers through `add_submenu_page('')`. WordPress treats an empty parent slug as "valid page, no
menu entry" — the same mechanism core-era setup wizards use. The page is still fully
capability-gated: registration is skipped entirely for users without the page's capability, and
the capability is re-checked before any output.

#### `render_mode => 'standalone'`

The page renders on `load-{$hook_suffix}` and exits, so `wp-admin/admin-header.php` and
`admin-footer.php` never run. That is what removes the chrome — and it also means **core's admin
stylesheets are never enqueued**, so the document carries only the Gravity design system and the
widget's own CSS. Your page will not inherit `.wp-core-ui` button styles, dashicons, or any other
wp-admin CSS; if you want them, enqueue them yourself.

Because those two core files are skipped, the library fires the hooks they would have fired, in the
same order, so your own asset loading keeps working unchanged:

```
admin_enqueue_scripts            admin_print_footer_scripts-{$hook_suffix}
admin_print_styles-{$hook_suffix}    admin_print_footer_scripts
admin_print_styles               admin_footer-{$hook_suffix}
admin_print_scripts-{$hook_suffix}
admin_print_scripts
admin_head-{$hook_suffix}
admin_head
```

One deliberate omission: the **global `admin_footer`** action is not fired. It exists for arbitrary
plugin markup injection, which is precisely what a chrome-free page must not inherit. The
page-scoped `admin_footer-{$hook_suffix}` *is* fired, since only code targeting your page uses it.

The document is guaranteed a `<meta name="viewport">`, so a standalone page is responsive on mobile.
Core supplies it through `wp_admin_viewport_meta()` on `admin_head`; the library emits its own only
when that action is absent, so you never get two. Setting the core `admin_viewport_meta` filter to an
empty string still suppresses the tag, as on any admin screen.

The `<title>` is `page_title`, falling back to `menu_title` when `page_title` is empty — a standalone
document has no other heading, so an empty title would leave the browser tab showing the raw URL.

Standalone also picks widget defaults suited to a full-bleed page — `width: fluid`, `height: fill`,
`chrome: flat`, `expandToggle: off`. Any key you set explicitly in `layout` wins over the matching
default, per key.

#### `standalone_shell_css => false` — own the document shell yourself

To compensate for the missing core stylesheets, a standalone page gets `assets/wap-standalone.css`:
a shell that resets `html`/`body` margins, sets a background and text colour, applies
`box-sizing: border-box` to everything, and supplies the height chain `layout.height => 'fill'`
resolves against.

On a page you style yourself that shell is unwanted — it loads after your stylesheet and its
`body.wap-standalone *` reset outranks your own element and single-class rules, so your `body` and
`button` styles lose. Pass `standalone_shell_css => false` and the library never enqueues it:

```php
WapClient::register_chat_page([
    'menu_slug'            => 'my-plugin-onboarding',
    'render_mode'          => 'standalone',
    'standalone_shell_css' => false,
    // …
]);
```

Sites that cannot change the registration call can do the same with a filter:

```php
add_filter('wap_client_standalone_shell_css', function (bool $enabled, string $menu_slug): bool {
    return 'my-plugin-onboarding' === $menu_slug ? false : $enabled;
}, 10, 2);
```

Only the shell stylesheet is affected — `wap-chat.css`, the Gravity design system and the widget
script still load, so the chat itself is untouched.

**What you take over** is the height chain — without it `fill` hits the `calc(100vh - 170px)`
fallback described under [`render`](#render--owning-the-page-body) below. Either pass an explicit
`layout.height`, or put these four rules in your own stylesheet; they are the structural half of
the shell, with the cosmetic resets left out:

```css
html, body { height: 100%; }
.wap-standalone-shell { display: flex; flex-direction: column; height: 100%; }
.wap-standalone-shell > .wap-chat-root { flex: 1 1 auto; min-height: 0; }
.wap-standalone-shell > .wap-chat-root.wap-height-fill { height: auto; min-height: 0; }
```

Add classes to the `<body>` element with the `wap_client_standalone_body_class` filter (values are
run through `sanitize_html_class()`):

```php
add_filter('wap_client_standalone_body_class', function (array $classes, string $menu_slug): array {
    if ('my-plugin-onboarding' === $menu_slug) {
        $classes[] = 'my-wizard';
    }
    return $classes;
}, 10, 2);
```

#### `render` — owning the page body

Use this when the chat needs your markup around it: a branded header, progress steps, a skip link,
a footer. Without it the library emits the chat container and nothing else.

```php
'render' => function (array $page): void {
    ?>
    <header class="my-wizard-header">
        <p><?php esc_html_e('Step 2 of 3', 'my-plugin'); ?></p>
    </header>
    <?php
    \GroupOne\WapClient\ChatWidget::render_chat_root($page['menu_slug']);
    ?>
    <footer class="my-wizard-footer">
        <a href="<?php echo esc_url(admin_url()); ?>"><?php esc_html_e('Skip', 'my-plugin'); ?></a>
    </footer>
    <?php
},
```

The contract:

- **Must be callable at register time.** A non-callable value is ignored and you get the default body.
- **You must call `ChatWidget::render_chat_root($menu_slug)`** somewhere. It emits the element the JS
  widget mounts into — skip it and there is no chat on the page.
- It receives a **credential-free** copy of the page config: `menu_slug`, `page_title`, `menu_title`,
  `product`, `mode`, `render_mode`, `hidden_admin_menu`. No license key, issuer URL or GRND provider.
- It runs **after** the capability check, so the page is already gated.
- **Escaping is yours.** The library escapes what it emits, not what you do.
- In `'admin'` mode the library still emits its `.wrap.wap-client-wrap` wrapper (that is wp-admin
  integration, not content) and your callback fills it. The default `<h1>` is **suppressed** when
  `render` is set, so the heading is yours to emit.

**Keep the chat root a direct child of the shell.** In standalone mode your output lands inside
`<div class="wap-standalone-shell gv-activated">`, a column flexbox — so a header and footer stack
and the chat absorbs the remaining height. The stylesheet does that with direct-child selectors:

```css
.wap-standalone-shell > .wap-chat-root { flex: 1 1 auto; min-height: 0; }
```

If you nest the chat root deeper (inside your own `<main>`, say), those rules stop matching and the
widget falls back to `wap-chat.css`'s `calc(100vh - 170px)`, which is sized for wp-admin's chrome
and will look wrong on a bare page. Either keep it a direct child of the shell, pass an explicit
`layout.height`, or give your own wrapper a height chain.

#### Registering a second page on the same product

Registering more than one chat page is supported — pages are keyed by `menu_slug` and each gets its
own assets and config. Both pages share the GRND cache and the stored Application Password, which
are keyed by **user + product**, so a user who authenticates on one page is already authenticated on
the other.

One consequence worth knowing: the library uses `page_title` as the label of the Application
Password it mints (falling back to the product slug when `page_title` is empty). Whichever page
triggers the first mint names the credential, so two pages with different titles on the same product
will show a title that may not match the page the user is on. It is cosmetic — no second password is
minted while a valid one exists — but pass matching titles if you want the label to be predictable.

## Permissions

Adds **Permissions** rows to the widget's settings page so the user decides what the assistant may
act on. The rows are yours; a new permission needs no release of this library. A row whose values are only `allow` / `deny` renders as an on/off toggle; any other set (e.g. with a third `ask` value) renders as a dropdown. An optional `badge` adds a short tag next to the label (e.g. "Coming soon").

```php
\WapClient::register_chat_page([
    // …menu_slug, product, server_url, grnd, etc…
    'permissions' => [
        [
            'id'           => 'manage-plugin',                      // [a-z0-9-]{1,64}, never shown
            'label'        => __('Manage your plugin', 'my-plugin'),
            'options'      => [                                     // ONE option renders read-only
                ['value' => 'allow', 'label' => __('Allow', 'my-plugin')],
                ['value' => 'deny',  'label' => __('Deny',  'my-plugin')],
            ],
            'defaultValue' => 'deny',                               // MUST be one of `options`
        ],
    ],
]);
```

Requires `layout.showSettings` (the default) — with the gear hidden the page cannot be opened,
and the widget warns on the console if you configure both.

Values are stored **per user and per product** in user meta behind an authenticated admin-ajax
endpoint. The built-in licence exchange (`grnd.issuer_url` + `license_key`) sends them to your
issuer as `permissions` in the token request, so most products need no code. To send something
else, pass a `permissions` argument to `get_grnd_token()`: an array, or a callable read on each
exchange. Either is final — a callable returning anything but an array sends no `permissions`
at all — and the registered rows are then not read. A host `grnd_provider` callable owns its
exchange and reads them back itself:

```php
'permissions' => \WapClient::get_permissions('my-product'),   // ['manage-plugin' => 'allow', …]
```

The map is always complete and defaulted. A write also drops the cached GRND, so the next token
carries the new claim instead of asserting the old one until it expires — if you persist a
permission outside this endpoint, call `\WapClient::invalidate_grnd($product)` yourself rather
than `get_grnd_token(['force_new' => true])`, which is throttled. Either way the next exchange
mints a new Application Password, as every exchange does. Nothing is enforced client-side: WAP
enforces from the signed claim.

A changed choice reaches the next token **of the tab that changed it** (the widget
re-authenticates after the save). Other open tabs keep the token they hold in memory until it is
re-minted or expires (your issuer's TTL); on multisite the cached GRND is per site while the
choice applies to the user on every site. The old Application Password is revoked once the next
token is minted, which limits how long an old token keeps working. The save itself takes the
user's Application Password lock, so it can wait up to 20 seconds behind a token being minted
for that user in another request.

Full details, including sanitisation rules and the SaaS/JS equivalent, in
[docs/integrating-a-product.md](../../docs/integrating-a-product.md#3h-permissions).

## The chat as a docked column

A chat page is somewhere users have to navigate to. A **column** docks the assistant beside the
screen they are already on, collapsed until they want it. It is the *same widget* — one
implementation, no fork — wrapped in panel chrome.

Two mount paths, because a product's admin may be classic PHP or a React SPA and this library
assumes neither. It also hard-codes **no screen list** — the host decides where the column belongs.

### PHP path

```php
\WapClient::register_chat_column([
    'id'         => 'my-plugin-assistant',   // namespaces state + DOM ids
    'product'    => 'my-product',
    'server_url' => 'https://wap.group.one',
    'grnd'       => ['issuer_url' => '…', 'license_key' => get_option('my_product_license')],

    // Opt screens in — at least one of these, or the column renders nowhere.
    'screens'       => ['toplevel_page_my-plugin'],
    'should_render' => fn ($screen, string $hook): bool
        => $screen && 0 === strpos($screen->id, 'my-plugin'),

    'column' => ['side' => 'right', 'width' => '400px'],
]);
```

Credentials, capability gating and the 401 refresh choreography are identical to
`register_chat_page()`.

| Option | Effect |
|---|---|
| `id` | Required. Namespaces the per-user state meta key and the widget's DOM id. |
| `capability` | Who may use the column. See [`capability`](#who-can-use-it-capability). |
| `screens` | Admin hook suffixes (`toplevel_page_x`) or `WP_Screen` ids (`options-general`); either form matches. |
| `should_render` | `fn (WP_Screen\|null, string $hook): bool` for dynamic screens. Runs *after* `screens`, so it can only add screens. |
| `column` | Panel framing — table below. |
| `title` | Accessible name for the panel and launcher. Defaults to *Assistant*. |
| `layout` | Usual widget framing. A column defaults to `width: fluid`, `height: fill`, `chrome: flat`, `expandToggle: off`; explicit keys win. |

**Screen opt-in is fail-closed.** With neither `screens` nor `should_render` the column renders on
*no* screen. Registration alone is deliberately not enough — a library that injected a panel across
all of wp-admin because an argument was forgotten would be the wrong default.

Per-site overrides, no plugin edit required:

```php
// Add (or remove) screens for one column.
add_filter('wap_client_column_screens', fn (array $s, string $id): array
    => 'my-plugin-assistant' === $id ? [...$s, 'dashboard'] : $s, 10, 2);

// Retheme the framing (mirrors wap_client_layout).
add_filter('wap_client_column', fn (array $c): array => [...$c, 'width' => '360px']);
```

Both are re-sanitised after filtering, so an override cannot smuggle an invalid enum through.

> **Do not opt a column onto a chat page's own screen.** Both surfaces localise the same
> `WapClientConfig` global and cannot share one screen. The library detects the clash and the column
> stands down in favour of the more specific page surface.

### JS path

For a React/JS admin, skip the PHP registration entirely:

```js
const chat = WapChat.mount(el, {
    product: 'my-product',
    wapBrowserUrl: 'https://wap.group.one',
    getSession,
    column: { side: 'right', width: '400px' },
});
// on unmount:
chat.destroy();
```

`mount(target, options)` treats `target` as the column **host**: it appends **one** child to it (its
own `[data-wap-chat-column]` wrapper) and never replaces or restyles the element you pass. With
React, hand it a ref'd element you keep empty — React does not know about that appended child, so
don't render children into the same node. Returns `null` (with a console warning) when `target`
can't be resolved. The handle exposes `expand(opts?)`, `collapse(opts?)`, `toggle(opts?)`,
`isCollapsed()`, `root` and `destroy()` — enough to drive the panel from your own header button with
`showLauncher: false`. Those three **do not move focus** unless you pass `{focus: true}`. **Call
`destroy()` on unmount**; without it an SPA route change leaks the listeners bound outside the widget
shell.

**One column per page.** The column's runtime state is module-level, so a second `mount()`
re-initialises the widget — releasing the first column's panel, scrim, launcher, media listener and
document handlers — rather than adding a second, independent panel. `id` namespaces the *stored
preference*, not the instance.

Or emit the documented mount point and let the widget find it on load:

```html
<div data-wap-chat-column="my-plugin-assistant"></div>
```

### `column` framing options

| Key | Values | Default | Effect |
| --- | --- | --- | --- |
| `side` | `'left'` \| `'right'` | `'right'` | Edge to dock to. **Logical, not physical** — `'right'` is the inline-end edge, so RTL docks on the left automatically, and collapses towards that same edge. In wp-admin, `'left'` + `'push'` also insets `#adminmenuwrap`. |
| `width` | CSS length | `'400px'` | Panel width. Validated as a CSS length on both the PHP and JS sides; anything else falls back to the default. Capped at `100vw` — panel *and* page inset. |
| `mode` | `'push'` \| `'overlay'` | `'push'` | `'push'` insets the page so the panel never covers content (non-modal). `'overlay'` floats above it behind a scrim (modal). |
| `breakpoint` | CSS length | `'960px'` | At or below this viewport width the mode is **always** `'overlay'` and the panel goes full-bleed. Same validation as `width`. |
| `defaultState` | `'expanded'` \| `'collapsed'` | `'collapsed'` | State before the user has a stored preference. |
| `showLauncher` | bool | `true` | Render the floating launcher button. |
| `persist` | bool | `true` | Remember the preference at all. |
| `startOnOpen` | bool | `false` | Defer the session, credential and consent request until the column is first opened. Must be boolean `true`: a string or number stays eager (PHP's `register_chat_column` coerces for you). |
| `label` / `id` | string | — | Accessible name; state namespace (the PHP path derives both from `title`/`id`). |

**Starting on first open.** By default the widget connects at page load, even while the column is
collapsed. With `startOnOpen: true` a collapsed column makes no session, Application Password or
consent request until the user first opens it; that single start also covers the consent check, so
the consent modal and the Mixpanel / Page Viewed events move to the first open as well (a user who
never opens the column never triggers them). A column that starts expanded (`defaultState`, a
server-seeded state, or a restored preference) connects at load, as before. Because `persist` is on
by default, a user who leaves the column open keeps it open and connects on every load, so only
users whose saved state is collapsed benefit.

**Layout safety.** In push mode the library insets wp-admin's `#wpcontent` and `#wpfooter`, so the
panel sits beside the page rather than over it. A host with a different shell marks its own container
with `data-wap-column-push`, or writes a rule against the `--wap-column-push` custom property the
widget sets on `<html>`. Below `breakpoint` the panel becomes a full-bleed overlay sheet, because
insetting a 400px column on a phone leaves nothing usable behind it. `side: 'left'` additionally
insets `#adminmenuwrap`/`#adminmenuback`, which are `position: fixed` and so immune to padding.

**State persistence.** WordPress stores the preference **per user** in user meta
(`wap_client_column_{id}`) via an authenticated admin-ajax endpoint — nonce, the column's capability,
and an allowlist of registered ids, so arbitrary meta keys are not writable through it. Because PHP
knows the state at render time it is emitted on the wrapper server-side, so a column the user left
collapsed never flashes open. Non-WordPress hosts fall back to `localStorage` (per browser profile,
not per account) unless they pass a `columnState: {get, set}` hook.

> Register the column on `init` or `admin_init`, **not** `admin_menu`: the endpoint's id allowlist is
> the live registry and `admin_menu` never fires on `admin-ajax.php`. If the endpoint or its nonce
> fails, the widget warns on the console and degrades to `localStorage` instead of going silently
> read-only.

### Accessibility contract

Implemented by the widget — documented so integrators know what not to duplicate:

- **Docked (push) mode is non-modal**: `role="complementary"`, named by a heading, **no** focus trap
  and **no** `aria-modal`. The rest of the page stays keyboard-reachable.
- **Overlay mode is modal**: `role="dialog"` + `aria-modal="true"`, scrim, focus trap, locked scroll.
  The trap is **Tab-only** — siblings are not `inert`, so a screen reader's virtual cursor and
  find-in-page still reach the page behind the scrim — and the scrim sits below `#wpadminbar`, so
  the admin bar stays reachable.
- **Launcher** carries `aria-expanded` and `aria-controls`.
- **Escape** collapses and returns focus to whatever opened the panel. In non-modal push mode it is
  only intercepted while focus is **inside** the panel, so Escape aimed at the host's own inputs,
  dropdowns or dialogs reaches them untouched. The widget's modal, its settings sheet and the
  expanded (fullscreen) view each own Escape ahead of the column.
- **Opening** moves focus into the panel (composer, or the close button while it is disabled);
  **closing** returns it to the element that opened it, falling back to the launcher — with
  `showLauncher: false` and no captured trigger, focus is left where it is rather than dumped on
  `<body>`; **restoring** an expanded column on load moves focus nowhere and does not animate.
- **Collapsed** panels are `aria-hidden`, `inert` *and* `visibility: hidden`, so their controls leave
  the tab order even in browsers without `inert`.

> **Gravity note.** The column is deliberately *not* built on `gv-sidedrawer`. That component is a
> modal overlay whose contract mandates `role="dialog"` + `aria-modal` + a focus trap; a docked column
> that pushes content is non-modal, and announcing it as a modal dialog would be an accessibility
> defect. The panel is composed the way the rest of this widget is: namespaced `wap-*` classes for
> layout glue, Gravity **tokens** for every colour/space/radius/shadow, and Gravity atoms
> (`gv-button`, `gv-icon`) for the controls. Overlay mode does take the full modal semantics.

### Just need the GRND?

If you're not using the bundled widget (custom chat surface, export, third-party integration),
the same GRND acquisition is exposed as a single call:

```php
$grnd = \WapClient::get_grnd_token([
    'product'        => 'my-product',
    'server_url'     => 'https://wap.group.one',
    'issuer_url'     => 'https://api.my-brand.com/grnd/token', // or pass a grnd_provider callable
    'license_key'    => get_option('my_product_license'),
    'force_new'      => $_POST['force_new'] ?? false,           // widget's 401-retry flag
    // Forward any extra headers verbatim to the brand issuer on every
    // exchange. Useful for relaying auth/identity headers (TOTP, client
    // domain, custom tokens) from the surrounding request.
    'extra_headers'  => [
        'X-TOTP'                 => $_POST['totp']   ?? '',
        'X-Onecom-Client-Domain' => $_POST['domain'] ?? home_url(),
    ],
]);

if (is_wp_error($grnd)) {
    error_log('WAP get_grnd_token failed: ' . $grnd->get_error_code());
    wp_send_json_error(['message' => 'Assistant unavailable right now.'], 502);
}
wp_send_json_success(['token' => $grnd]);  // the browser hands it to WAP directly
```

Everything that happens inside `register_chat_page` (App Password → wrap key → seal → issuer →
cache → refresh) is reused; you just skip the widget parts. Return the result to your frontend
as `Authorization: Bearer <token>`.

## The chatbox inline, with no admin page

A page is somewhere users navigate to; a column is docked beside the screen. An **embed** is neither
— it is the chatbox dropped *into* a screen you already own: a tab on your settings page, a metabox,
a panel in the post editor. The library registers no page, adds no menu entry and draws no panel
chrome; you decide where the widget appears.

Register once, on `init` or `admin_init`:

```php
add_action('admin_init', function () {
    \WapClient::register_chat_embed([
        'id'         => 'my-plugin-content-ai',   // namespaces the DOM id + consent/auth lookups
        'product'    => 'my-product',
        'server_url' => 'https://wap.group.one',
        'grnd'       => ['issuer_url' => '…', 'license_key' => get_option('my_product_license')],

        // Where the assets load — at least one of these, or the embed activates nowhere.
        'screens'       => ['my-plugin_page_my-plugin-content-ai', 'post'],
        'should_render' => fn ($screen, string $hook): bool => $screen && 'post' === $screen->base,

        'layout' => ['height' => '640px'],
    ]);
});
```

Then place it, anywhere in your own markup:

```php
// Inside your tab body, metabox callback, template partial…
\WapClient::render_chatbox('my-plugin-content-ai');          // echoes

$html = \WapClient::get_chatbox('my-plugin-content-ai');     // …or returns the markup

if (\WapClient::has_chatbox('my-plugin-content-ai')) {       // …or ask first
    echo '<h2>Chat</h2>';
    \WapClient::render_chatbox('my-plugin-content-ai');
}
```

| Method | Returns | Use it for |
|---|---|---|
| `register_chat_embed($args)` | — | One-time registration. `init`/`admin_init`, **not** `admin_menu`. |
| `render_chatbox($id)` | echoes | The normal case — call it at the point the chatbox belongs. |
| `get_chatbox($id)` | `string` | A tab renderer that returns markup rather than echoing, or a template variable. Same request only — **not** usable from `admin-ajax.php` or a REST route, where `admin_enqueue_scripts` never fires so the embed is never active (and the assets would not be in that response anyway). |
| `has_chatbox($id)` | `bool` | Skipping your *own* chrome — a tab, a heading, a panel — when the chatbox won't appear. |

All three are **safe to call unconditionally**: on a screen that was not opted in, for an unknown id,
or for a user without the embed's capability they render nothing and return `''`/`false` rather than
warning.

**Place the chatbox exactly once per screen.** Only one mount point can work — the widget resolves it
from a single selector — so the second call on a request returns nothing and raises a
`_doing_it_wrong()` notice. `render_chatbox()` and `get_chatbox()` share that budget: calling one after
the other on the same screen gets you one chatbox, not two.

`layout` takes the usual widget framing. An embed defaults to `width: fluid` and `chrome: flat` — it
fills the container you give it and draws no card, because your tab or panel almost always draws one
already. Unlike a column it forces **no height**, since only you know how tall the host container is;
pass `layout.height` (a CSS length, or `'fill'` if your container has its own height chain).

### Narrow containers

The widget adapts to **its own width**, not the viewport's. `.wap-chat-root` is a CSS container
(`container: wap-chat / inline-size`) with two compact tiers at **≤ 480px** and **≤ 360px** of
*widget* width: tighter header padding, a truncating status label, smaller and wrapping
suggestion chips, an edge-to-edge settings sheet, and reclaimed padding on the confirm/consent modal.

Without this, a 300px editor sidebar on a 1920px desktop gets full desktop spacing, because none of
the viewport media queries fire. Measured at a 300px container, that clipped the welcome block by
20px — the suggestion chips were `nowrap`, so a long prompt ran under the shell's `overflow: hidden`
edge. The chips now wrap.

The modal tier addresses a different constraint: Gravity's own `.gv-modal { padding: 48px }` plus a
48px content padding left roughly 106px of usable text column in a 300px panel, wrapping
*"Delete your data?"* over three lines. Both paddings drop to 16px there, and the content is capped to
the container.

The viewport media queries remain in place as the floor, so a browser without container-query support
behaves exactly as before.

> This applies to **every** surface, not just embeds — a docked column at its default `400px` now
> picks up the ≤ 480px tier on desktop too, where it previously rendered with desktop spacing.

| Option | Effect |
|---|---|
| `id` | Required. Namespaces the mount point's DOM id and identifies the surface to the auth/consent endpoints. |
| `capability` | Who may use the embed. See [`capability`](#who-can-use-it-capability). |
| `screens` | Admin hook suffixes (`toplevel_page_x`) or `WP_Screen` ids (`post`, `edit.php`); either form matches. |
| `should_render` | `fn (WP_Screen\|null, string $hook): bool` for dynamic screens. Runs *after* `screens`, so it can only add screens. |
| `title` | Accessible name for the widget. Defaults to *Assistant*. |
| `layout` | Widget framing. Defaults to `width: fluid`, `chrome: flat`; explicit keys win. |

Credentials, capability gating, consent and the 401 refresh choreography are identical to
`register_chat_page()`.

**Screen opt-in is fail-closed**, exactly as for a column: with neither `screens` nor
`should_render` the embed activates on *no* screen, so a forgotten argument cannot leak the widget
across wp-admin. Per-site overrides go through `wap_client_embed_screens` (the embed's own filter —
`wap_client_column_screens` does not apply to it):

```php
add_filter('wap_client_embed_screens', fn (array $s, string $id): array
    => 'my-plugin-content-ai' === $id ? [...$s, 'dashboard'] : $s, 10, 2);
```

### One chatbox per screen

The widget keeps module-level state and a single frozen `WapClientConfig`, so a second surface on one
screen would re-initialise the first rather than run beside it. The library therefore lets exactly
one surface take a screen, with a fixed precedence:

**chat page → embed → column**

A page wins because it is the most specific surface. Between an embed and a column the embed wins,
decided by hook priority (the embed activates on `admin_enqueue_scripts` priority 5, the column at
10) so the outcome does **not** depend on which plugin registered first. Whichever surface stands
down does so silently and completely — no assets, no markup.

Registering several embeds is fine as long as they resolve to different screens; that is the normal
case, e.g. one for a settings tab and one for the post editor. Two that match the *same* screen is
not supported: the first to activate takes it and `has_chatbox()` returns `false` for the other.

### From a React admin (still Composer-only)

The Composer package ships the widget JS, so a React screen does not need the npm package — it needs
the same `register_chat_embed()` call (for the credentials, assets and config) and then mounts from
JS instead of rendering a PHP mount point:

```jsx
useEffect(() => {
    const chat = WapChat.mount(ref.current, {
        column: false,                      // plain in-place embed, no panel chrome
        layout: { width: 'fluid', height: 'fill', chrome: 'flat' },
    });
    return () => chat?.destroy();            // required — an SPA route change leaks listeners otherwise
}, []);
```

`column: false` is what distinguishes this from the docked-column JS path. `getSession` is **not**
needed: the localised config from `register_chat_embed()` already carries the admin-ajax endpoint and
nonce, so the GRND and Application Password never reach the browser. `mount()` appends one child to
the element you pass and never replaces or restyles it, so hand it a ref'd node you keep empty.

Because the mount point does not exist when the page loads, the widget's auto-init finds nothing and
does nothing — your `mount()` call is what starts it. That is expected, not an error.

## Authentication (GRND)

WAP does **not** verify product licenses itself — it verifies a **GRND**, a signed JWT issued by a
backend your product trusts after that backend validated the customer. The library obtains and
caches the GRND server-side, refreshes it on expiry, and hands it to the browser widget, which
uses it as the Bearer credential on every WAP call (WAP verifies it per request — there is no
session exchange). The sealed credential inside is opaque to the browser.

On WordPress, acquiring a GRND is a **mint → seal → exchange** cycle that runs only on a cache
miss: the library mints a fresh Application Password, fetches WAP's public wrap key
(`GET {server_url}/api/v1/auth/wrap-key`), seals `username:password` to it with libsodium's
sealed box (`sodium_crypto_box_seal`, PHP >= 7.2 built-in), and sends the ciphertext to the brand
endpoint as `wrapped_app_token`/`wrap_key_id`. The brand embeds it into the GRND unchanged — it
only ever handles ciphertext; the plaintext credential never leaves the site. WAP unwraps it after
verifying the GRND on each call (see `docs/wap-backend-grnd-requirements.md` in the platform repo
for the full backend contract).

Every freshly obtained token is **sanity-checked** before use (structure only — signature
verification is WAP's job). Whatever the provider, the token must be:

- a three-part JWT with base64url-encoded JSON header and payload
- signed with `alg: EdDSA` (the only algorithm the GRND spec allows)
- carrying a `jti` of the form `grn:2@int:grnd::wap/{nonce}` (tag `wap`)
- carrying a positive `exp` claim (the issuer's `expires_at` may shorten the cache lifetime,
  never extend it past `exp`)

A token violating any rule is rejected with a `WP_Error` naming the broken rule — visible in
`debug.log` when `WP_DEBUG` is on — and is never cached, so a fixed issuer takes effect on the
next request. End users only ever see a generic "temporarily unavailable" message.

The cached GRND is coupled to the WordPress Application Password lifecycle: whenever the App
Password rotates (re-provisioning, 401 re-auth, GDPR erasure), the cached GRND is invalidated in
the same call, since a GRND issued over a revoked credential must never be reused.

**Deactivation and uninstall.** The expiry and MCP-only checks run only while the library is loaded:
once the host plugin is deactivated, the WAP Application Password left on each user is an ordinary,
unrestricted one again. Revoke them from your deactivation and uninstall hooks with
`\WapClient::revoke_app_passwords('your-product')`: it deletes your product's passwords and uuid meta
for every user on every site, plus any WAP password no site has on record, and works without a
logged-in user. Don't delete every password tagged with `AppPasswordManager::APP_ID` yourself: every
host shares that tag, so you would revoke another plugin's live credential (and reset its consent).

Configure one of:

- **`grnd` (recommended)** — the standardized brand exchange. Every brand backend exposes the same
  endpoint contract; only the host differs:

  ```
  POST {issuer_url}
  { "license_key": "...", "site_url": "https://customer-site.com", "product": "my-product-slug",
    "permissions": { "manage-plugin": "allow" },
    "wrapped_app_token": "<base64 sealed box>", "wrap_key_id": "2026-07-a" }
    → { "grnd": "<JWT>", "expires_at": 1750000000 }
  ```

  `permissions` carries the user's [Permissions](#permissions) choices, only when the product
  registers rows (or passes a `permissions` argument, below).
  The `wrapped_app_token`/`wrap_key_id` pair is added automatically by the WordPress adapter;
  hosts without a platform credential use the same contract without those fields. Unknown extra
  response fields are ignored (the schema is draft v0 — additive changes are safe). A 401/403
  from the brand is surfaced as `wap_grnd_not_entitled` (invalid or expired license).

  When the host sets `grnd.send_user_ref` the body also carries an optional `user_ref` (see
  *Per-user chat reference* below). `get_grnd_token()` takes the matching `send_user_ref` argument.

- **`grnd_provider`** — escape hatch for brands whose backends cannot conform. A callable returning
  the raw GRND string (or `['grnd' => ..., 'expires_at' => ...]`, or a `WP_Error` when the customer
  is not entitled). The returned token must still pass the sanity check above — opaque non-JWT
  strings are rejected:

  ```php
  'grnd_provider' => function () {
      return MyBrand\Api::exchange_license_for_grnd(get_option('my_license'));
  },
  ```

## Per-user chat reference (`send_user_ref`)

By default every WordPress user of a site shares one chat history, because the issuer mints the
identity from the licence and the site only. A host can opt in to a separate history per user:

```php
'grnd' => [
    'issuer_url'    => 'https://api.my-brand.com/grnd/token',
    'license_key'   => get_option('my_license_key'),
    'send_user_ref' => true,   // default false
],
```

- **What is sent.** One extra optional field, `user_ref`, in the token exchange: 32 random lowercase
  hex characters, not derived from the user's id, login or email. Those never leave the site.
  With the option off (the default) the request body is exactly what it was without it.
- **When and where.** Only to the configured issuer, only when a token is minted. The reference is
  created at the start of the first token request after opting in, when none is stored (a token
  cached before is then replaced), under the user's lock. The built-in exchange creates nothing
  before the user has consented.
- **Where it is stored.** User meta `wap_client_user_ref_<product>`: per user and per product,
  network-wide on multisite.
- **What it enables.** A separate chat history per WordPress user. The issuer decides what to do
  with `user_ref` (see `docs/wap-backend-grnd-requirements.md`). The option is effectively per
  product: the token cache is shared by every surface of a product, so all surfaces should set the
  same value (a `_doing_it_wrong()` notice under `WP_DEBUG` flags a mismatch).
- **Fails closed.** When the option is on and the reference cannot be read or created, the exchange
  stops with `wap_user_ref_unavailable`. The library never falls back to sending no reference, which
  would silently put the user back on the shared identity. The user sees the generic "temporarily
  unavailable" message.
- **Where it ends up.** The issuer derives a per-user `wap_user_id` from it, and that identity
  reaches Mixpanel, LangFuse and the WAP admin conversation list, none of which WAP's erasure
  endpoint clears. Treat it as pseudonymous personal data: never expose it to the browser, REST
  responses or logs, and cover it in your own privacy policy or terms (the consent text can be
  extended through the `i18n` overrides `consentAccessText` / `consentText`).
- **Retention and erasure.** "Delete my data" resets the reference: it is deleted together with the
  consent, so the user's next chat starts a new identity and what WAP kept outside the conversations
  (analytics, logs, backups) can't be linked to it. A GRND already cached keeps the old identity until
  it expires. WordPress deletes user meta only when the user is deleted: Tools > Erase Personal Data
  does not remove it, so a host that must honour erasure should register a personal-data eraser and
  exporter, and remove the key for all users on uninstall (`delete_metadata('user', 0, $key, '', true)`).
- **Choose once.** Switching the option on or off changes the user's identity and orphans their
  existing conversations (they are not migrated). A token cached without a reference is dropped only
  when none was stored yet; if one already existed (for example the option went on, off, on), that
  token is served until it expires.
- **No consent for the getter, network-wide on multisite.** `get_user_ref()` creates the row without
  consent (it leaves the site only when you send it), so it exists for users who never consented. On
  multisite user meta is network-wide: every site of the network uses the same reference, and
  unlinkability across sites rests on the issuer deriving the per-user part from the licence and
  domain (see `wap-backend-grnd-requirements.md`).
- **Hosts with their own `grnd_provider`.** Read the reference inside the callable with
  `\WapClient::get_user_ref(string $product, ?int $user_id = null): string` (current user by
  default) and forward it only to your own issuer. It creates the reference on first call and
  returns `''` when unavailable (then send nothing and mint nothing). Also pass
  `'send_user_ref' => true` to `get_grnd_token()` so the first token after opting in is minted with
  it. The getter is not consent-gated: a reference may exist for a user who never consented, or who
  can never obtain a token (multisite without network activation, Application Passwords disabled).
  It never leaves the site unless the host sends it.

**Residual stale-token windows**, all bounded by the token's expiry (the cache key is fixed):

1. Two surfaces of one product with different `send_user_ref` share a cached token: whichever mints
   first decides what it carries.
2. Turning the option off and on again: a token minted while off stays cached and the reference is
   already stored, so the user keeps the site identity until it expires.
3. Calling `get_user_ref()` before the first token creates the reference, so a token cached earlier
   keeps the old identity until it expires.
4. Multisite: the reference is network-wide but the token cache is per site, so each site switches
   on its own schedule.
5. A token minted by an issuer that does not yet know `user_ref` stays cached until it expires, so
   deploy the issuer before enabling the option.

## SaaS embedding

SaaS hosts don't use the PHP side of this package — they consume the widget from the npm package
**`@group-one/wap-client`** (published from this same directory; see *npm package* below) and
provide the platform hooks. The full, shareable integration guide for non-WordPress brands lives
in `docs/integrating-a-saas-host.md`.

```js
import { init } from '@group-one/wap-client/widget';
import '@group-one/wap-client/widget.css';

init({
  wapBrowserUrl: 'https://your-wap-backend.example.com',
  product: 'your-product-slug',
  root: '#assistant-panel',
  getSession: async (opts) => {
    // Your backend authenticates its own logged-in user, issues a short-lived
    // GRND for them, and returns it as {token} — the widget sends it directly
    // to WAP as the Bearer credential on every call. Forward opts.forceNew so
    // the backend mints a fresh GRND after a WAP 401 (expiry/revocation).
    const res = await fetch('/api/assistant/session', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ forceNew: !!opts.forceNew }),
    });
    return res.json();
  },
});
```

The widget renders with [Gravity](https://gravity.group.one/) components, so the Gravity brand
stylesheet and runtime must be on the page. By default the widget loads them itself: on `init()`
it injects the version-pinned Gravity tags **only if they are not already present**, so a
standalone page works with zero config and the CDN version lives in the widget package.

Hosts that already provide Gravity are detected and left untouched — the WordPress adapter enqueues
it server-side, and SaaS pages that share Gravity with other group.one widgets keep loading it once
themselves. Pass `loadGravity: false` to opt out entirely (e.g. a strict CSP that blocks the
Gravity CDN, or a host that manages Gravity through a path the widget can't detect).

The Gravity brand defaults to `group-one`. Pick another with `brand` (`'rankmath'`, …) — the
`register_chat_page()` / `register_chat_column()` option on WordPress (also filterable through
`wap_client_brand`), `WapChat.init({ brand })` on SaaS hosts.

A classic script-tag embed (no bundler) works too, using `dist/wap-chat.js` from the npm tarball —
it exposes the same API as `window.WapChat`:

```html
<div id="assistant-panel"></div>
<script src="wap-chat.js"></script>
<script>
  WapChat.init({
    wapBrowserUrl: 'https://your-wap-backend.example.com',
    // Mount container: an Element or CSS selector. Defaults to #wap-chat-root.
    root: '#assistant-panel',
    // The widget loads Gravity itself when it isn't already on the page.
    // Set false to opt out (host manages Gravity / strict CSP).
    // loadGravity: false,
    getSession: async function (opts) {
      // Your backend issues a short-lived GRND for the logged-in user and
      // returns it as {token}; the widget uses it as the WAP Bearer credential.
      const res = await fetch('/api/assistant/session', { method: 'POST' });
      return res.json();
    },
    // T&C consent persistence in your own storage. When provided, the first
    // chat is gated behind an in-widget consent prompt until set(true) succeeds.
    consent: {
      get: async function () {
        const res = await fetch('/api/assistant/consent');
        return (await res.json()).granted;
      },
      set: async function () {
        await fetch('/api/assistant/consent', { method: 'POST' });
      },
    },
    // Optional: host-side cleanup after GDPR erasure, string overrides, etc.
    // eraseLocalData: async function () { ... },
    // i18n: { assistantName: 'Assistant', ... },
  });
</script>
```

`getSession` is the entire platform contract: the widget calls it on load and again with
`{forceNew: true}` after a 401 — at most twice in a row, after which it shows a terminal notice
with a Try again button instead of re-minting forever — and everything else (streaming, history,
GDPR UI) is shared. On
WordPress pages the library injects the default implementation automatically (server-side auth via
admin-ajax), so plugin integrations never touch this.

### npm package

This directory is dual-published: `composer.json` → Packagist (`groupone/wap-client`, WordPress
integrations) and `package.json` → npm (`@group-one/wap-client`, SaaS integrations). Both are built
from the same canonical `assets/wap-chat.{js,css}`, so a widget UI change ships to the WordPress
plugin and the npm package from one edit.

- `dist/` is **never committed** — it is regenerated by `node npm/build.mjs` on every
  `npm pack` / `npm publish` (`prepack` hook), so the tarball cannot drift from `assets/`.
- The build fails if the `package.json` version differs from the `Version:` header in
  `wap-client.php` — bump both together when releasing.
- Entry points: `@group-one/wap-client/widget` (ESM, SSR-safe import, TypeScript types included),
  `@group-one/wap-client/widget.css`, `dist/wap-chat.js` for script-tag embeds, and
  `@group-one/wap-client/server` (Node-only — see below).
- Zero runtime and zero build dependencies — the build is a plain Node ≥ 18 script.
- Tests: `npm test` (runs `npm/test-server.mjs`, standalone like the PHP suites; also wired into
  `prepack` so a publish cannot ship with failing tests).

### Server SDK (`@group-one/wap-client/server`)

The brand backend has exactly one job in the WAP flow: **issue a GRND for the logged-in user and
hand it to the browser** — the widget sends that GRND directly to WAP as the Bearer credential on
every call, and the brand backend never talks to WAP at all. This SDK implements that job with
the same guarantees as the PHP library, so no brand hand-rolls it: structural GRND sanity checks
(EdDSA, designation `jti`, positive `exp`), per-user caching with TTL = min(jwt `exp`, issuer
`expires_at`) − 60 s, and the `forceNew` refresh choreography (the widget's retry-after-401).
It mints whenever asked — the retry cap lives in the widget, so a custom frontend driving this
SDK must bound its own 401 retries.
Because the GRND is browser-held, **issue short-lived GRNDs** (minutes–hours). The entry is
`node`-conditional and throws if bundled for the browser.

```js
import { WapGrndClient } from '@group-one/wap-client/server';

const wap = new WapGrndClient({
  product: 'partners-one',
  // The ONLY brand-specific code: how to obtain a GRND for a user. Either a
  // plain async function like this, or omit `provider` and pass issuerUrl +
  // siteUrl (+ licenseKey) to use the standardized issuer exchange. Pass a
  // Redis-backed `storage` in multi-instance deploys.
  provider: async ({ cacheKey }) => issueGrndForUser(cacheKey), // → JWT string or {grnd, expires_at}
});

// Endpoint behind YOUR login + CSRF — this is what the widget's getSession calls.
app.post('/api/wap/session', requireLogin, async (req, res) => {
  try {
    const token = await wap.getGrnd({
      userKey: String(req.user.id),      // GRNDs are cached per user
      forceNew: !!req.body.forceNew,     // widget's retry-after-401 flag
    });
    res.json({ token });                 // the token IS the GRND
  } catch (e) {
    // Never leak technical detail to end users.
    res.status(502).json({ message: 'The assistant is unavailable right now. Please try again.' });
  }
});
```

Exports: `WapGrndClient` (the facade — most integrations need only `getGrnd()`), `TokenManager`,
`createIssuerProvider` (the standardized brand-issuer contract, mirroring `LicenseGrndProvider`),
`MemoryStorage`, `sanityCheckGrnd`, `WapError`. The full wire contract lives in `docs/wap-backend-grnd-requirements.md`; WAP-side
per-call GRND verification is still being built — schema changes during development stay inside
your provider function.

### Platform hooks

- `root` — Element or CSS selector to mount the widget into. Defaults to `#wap-chat-root`
  (the id the WordPress adapter renders), so existing integrations need no change.
- `consent` — `{get, set}` pair persisting the user's T&C acceptance in host storage. `get()`
  resolves a boolean; `set(true)` records acceptance. While consent is missing, the composer is
  disabled behind an in-chat prompt ("Agree and continue", with the `terms_url` link when set).
  On WordPress pages the default implementation stores acceptance per user and per product in
  user meta (via admin-ajax), and GDPR erasure clears it. Hosts that provide no hook and no
  admin-ajax config get no gate — the feature is opt-in for bare embeds.
- `eraseLocalData` — async host-side cleanup after GDPR erasure (WordPress default revokes App
  Passwords and cached tokens).

### Optional parameters

- `terms_url` — the host's Terms of Use / T&C document. When set, its link appears in the footer
  legal notice (as **Terms of Use**) and inside the first-use consent prompt.
- `privacy_url` — the host's Privacy Policy document. When set, its link appears in the footer
  legal notice (as **Privacy Policy**).
- `site_access_url` — the host's article explaining how the assistant accesses the site. The
  WordPress consent prompt reads _"Please review and accept the Terms & Conditions for the AI
  assistant."_ followed, on a new line, by _"It only gets limited, safe access to your site
  through a WordPress Application Password."_ and a **Learn more** link on a line of its own.
  The link points here; when this is unset it is left out. SaaS
  hosts pass `siteAccessUrl` together with their own `i18n.consentAccessText` (empty by default,
  which hides the sentence).

  To link words inside the sentence instead of adding **Learn more**, wrap them in
  `{link}…{/link}` in your `consentAccessText` override, e.g.
  `'The assistant uses a safe {link}WordPress Application Password{/link}.'`. The wrapped words
  link to `site_access_url` and stay plain text when it is unset.

  The footer shows a one-line AI + legal notice — _"This tool uses AI to generate content. Accuracy
  and legal compliance are not guaranteed. By using this tool you agree to Terms of Use and
  acknowledge Privacy Policy."_ — with the two links inline; each falls back to plain text when its
  URL is unset. The bottom-right of the bar shows the running `wap-client` version.

## UI, timestamps & localisation

The widget is built entirely from the [Gravity design system](https://gravity.group.one/) (group.one
brand). Each turn shows the author, a light AI icon on assistant turns, and a timestamp. Timestamps
honour the site's **Settings → General → Time format** (`time_format`) option.

The header **New chat** button shows a `+` icon, the column-mode close control an `x` icon, and chat
assistant answers and the ask-user reply card (question, options and the free-text field) render at 14px (your own questions are unchanged). These apply to every host that embeds the widget.

All UI strings follow the active WordPress admin locale. Bundled translations ship in `i18n/` as
`wap-client-{locale}.mo` — those compiled files are all WordPress reads. Shipped locales: German,
French, Spanish, Dutch, Danish. The widget core ships English defaults, so non-WordPress hosts work
without any i18n setup.

The `.po` sources and the `wap-client.pot` template are **not** in the published package (see
*Development* below) — they live in the repo. To add a locale, work from a clone: copy
`wap-client.pot`, translate the strings, and compile with
`msgfmt -o wap-client-xx_XX.mo wap-client-xx_XX.po`.

## Bundling under a namespace prefix (Mozart / Strauss)

Every class name the library resolves at runtime is a `::class` constant, and the facade and boot logic live in
the `GroupOne\WapClient` namespace (`WapClient`, `Bootstrap`, `Config`), so a prefixer rewrites all of them. The root
`wap-client.php` / `wap-client-api.php` stay as the unprefixed entry point: they alias the facade to the global
`\WapClient` and hook `Bootstrap` for you. A prefixed copy never loads them, so the host wires up three things itself.

**1. Copy the classes.** Mozart 0.7.1 copies only the PSR-4 class files and ignores the package's `files` entry, so no
extra configuration is needed. Some tools do copy `files` entries; if yours does, limit the package to its classes:

```json
"extra": { "mozart": {
    "packages": ["groupone/wap-client"],
    "override_autoload": { "groupone/wap-client": { "psr-4": { "GroupOne\\WapClient\\": "includes/" } } }
} }
```

The files are named `class-*.php`, which is not a PSR-4 file name, so the host's autoloader needs a classmap for the
prefixed directory (for example `"autoload": { "classmap": ["inc/Dependencies/"] }`).

**2. Boot it.** With `Your\Prefix` standing for your prefix:

```php
use Your\Prefix\GroupOne\WapClient\Bootstrap;
use Your\Prefix\GroupOne\WapClient\Config;

add_action('plugins_loaded', [Bootstrap::class, 'boot'], 20);
add_action('init', [Bootstrap::class, 'load_textdomain']);          // the `wap-client` translations
register_activation_hook(MY_PLUGIN_FILE, [Bootstrap::class, 'activate']); // grants the `wap_use_ai` capability
```

and call `Your\Prefix\GroupOne\WapClient\WapClient::register_chat_page()` instead of `\WapClient::`.

**3. Tell it where its assets are.** `assets/` and `i18n/` are not copied by a prefixer, so copy them next to your
prefixed classes and say where they are, once, before the chat renders. This is per copy (a class, not a global
constant), so two plugins never share assets:

```php
Config::configure([
    'url'     => plugins_url('inc/Dependencies/GroupOne/WapClient/', MY_PLUGIN_FILE), // holds assets/
    'file'    => MY_PLUGIN_DIR . 'inc/Dependencies/GroupOne/WapClient/wap-client.php', // sibling of i18n/
    'version' => MY_PLUGIN_VERSION, // optional ?ver= cache-buster
]);
```

Unprefixed, `Config` defaults to the package's own location, which is what the `WAP_CLIENT_*` constants always were.

### Several copies on one site

Prefixed copies run side by side. They all register the same `wp_ajax_wap_client_*` actions (the action names are
baked into `wap-chat.js`), so `Bootstrap::boot()` routes each request to the copy that registered the surface,
product or column it names: `dispatch_ajax` answers owned requests at priority 1, and `fallback_ajax` at priority 99
gives a request no copy owns the handler's usual 400/403. Because the fallback re-runs the handler, every handler
must end its response with `wp_send_json_*`.

- **The AJAX callbacks changed.** The handlers are no longer registered directly at priority 10 as
  `[ChatWidget::class, 'ajax_auth']` and so on; the hooks are `Bootstrap::dispatch_ajax` and `Bootstrap::fallback_ajax`.
  Code that called `remove_action()` or `has_action()` with the old callbacks must use these instead.
- **A copy only answers what it registered.** If two copies register the same `menu_slug`, the first one answers it.
- **An older copy next to a newer one.** A copy that predates this routing registers its handlers directly at priority 10
  and rejects requests it does not own. A routing copy answers its own requests first (priority 1) and passes the
  rest on, so both chats work whichever copy loads first. Two older copies still conflict with each other, so
  update every copy.
- **Shared by design:** meta and option keys, the `wap-client` text domain, the `wap_use_ai` capability and the
  `wap_client_*` filters. Copies on one site should run compatible library versions, since they share stored data.

Two unprefixed copies are guarded too: the first one loaded declares the global names, the second skips them.

## Development

The published package is trimmed to what runs in production — `tests/`, `npm/`, `scripts/`,
`package.json` and the `.po`/`.pot` translation sources are `export-ignore`d in `.gitattributes`,
so they are **not** in `vendor/groupone/wap-client/`. To work on the library, clone the repo (or
install with `composer install --prefer-source`), which gives you everything.

Unit tests are standalone PHP scripts with built-in WordPress shims — no PHPUnit or WordPress
install needed:

```bash
php tests/test-token-manager.php
php tests/test-grnd-acquisition.php
php tests/test-consent-gate.php
php tests/test-chat-widget-page-modes.php
php tests/test-chat-column.php
php tests/test-surface-capability.php
php tests/test-user-ref.php
```

Those are examples: run all of them (`for f in tests/test-*.php; do php "$f" || echo "FAILED $f"; done`).

The npm half has its own standalone suite: `npm test` (builds `dist/` and runs
`npm/test-server.mjs`; also wired into `prepack`).

## License

GPL-2.0-or-later
