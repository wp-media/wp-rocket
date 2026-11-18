<?php
/**
 * The `WapClient` facade — the library's public integration API.
 *
 * Lives under includes/ in the library namespace so that a namespace-prefixing tool (Mozart, Strauss)
 * rewrites it with the rest of the classes. wap-client-api.php aliases it to the global `\WapClient`.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

/**
 * WapClient — public integration API.
 *
 * Consuming plugins call WapClient::register_chat_page() from their admin
 * menu setup hook. Everything else (GRND acquisition + caching, App Password
 * provisioning, session management, widget rendering) is handled automatically.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 *
 * @example
 * // Minimal integration — call from the plugin's admin_menu callback.
 * // 'grnd' points at the brand's standardized exchange endpoint (same
 * // contract on every brand host — only the URL differs); the library
 * // handles caching, refresh, and passing the token to WAP. Brands with a
 * // non-standard backend pass a 'grnd_provider' callable instead.
 * WapClient::register_chat_page([
 *     'menu_slug'   => 'my-plugin-wap-chat',
 *     'parent_slug' => 'my-plugin-settings',
 *     'page_title'  => 'AI Assistant',
 *     'product'     => 'my-product-slug',
 *     'server_url'  => 'https://wap.group.one',
 *     'grnd'        => [
 *         'issuer_url'  => 'https://api.my-brand.com/grnd/token',
 *         'license_key' => get_option('my_product_license_key'),
 *     ],
 * ]);
 */
final class WapClient
{
    /**
     * Acquire a validated, cached GRND for a WordPress user.
     *
     * Single-call facade for consumers that need the GRND themselves (custom
     * admin pages, third-party plugins embedding the widget, exports, …).
     * Owns the full choreography — App Password provisioning, wrap-key fetch,
     * credential sealing, brand issuer exchange, caching, force_new refresh
     * — so no consumer reinvents it.
     *
     * @param array{
     *     product:         string,
     *     server_url:      string,
     *     issuer_url?:     string,
     *     license_key?:    string,
     *     grnd_provider?:  callable,
     *     user_id?:        int,
     *     user_login?:     string,
     *     force_new?:      bool,
     *     send_user_ref?:  bool,
     *     password_label?: string,
     *     extra_headers?:  array<string, string>,
     *     permissions?:    array<string, string>|callable,
     * } $args See {@see GrndService::get_grnd()}.
     *
     * @return string|\WP_Error The GRND on success; WP_Error with a named code
     *         on configuration / availability / sanity failures.
     *
     * @example
     *   // Echo of the test-client AJAX flow:
     *   $grnd = \WapClient::get_grnd_token([
     *       'product'    => 'wp-rocket',
     *       'server_url' => 'http://localhost:8010',
     *       'issuer_url' => 'https://api.brand.test/grnd/token',
     *   ]);
     *   if (is_wp_error($grnd)) { wp_send_json_error(['message' => 'unavailable'], 502); }
     *   wp_send_json_success(['token' => $grnd]);
     */
    public static function get_grnd_token(array $args)
    {
        return GrndService::get_grnd($args);
    }

    /**
     * A user's permission choices as `id => value` (default: current user) — always complete
     * and defaulted. The built-in licence exchange already sends it to the issuer; a host
     * `grnd_provider` callable sends it itself at mint time:
     * `'permissions' => \WapClient::get_permissions('rankmath')`.
     */
    public static function get_permissions(string $product, ?int $user_id = null): array
    {
        return ChatWidget::permissions_for($product, $user_id);
    }

    /**
     * A user's opaque, per-product chat reference (default: current user), created on first
     * call. Random and stable, not derived from the user's id, login or email. `''` means it
     * is unavailable: send nothing rather than mint one. It is pseudonymous: forward it only
     * to your own issuer, never to the browser, REST responses or logs.
     *
     * For a host with its own `grnd_provider`: read it inside the callable, and also pass
     * `'send_user_ref' => true` to get_grnd_token() so the first token after opting in is
     * minted with it. Creating it is not consent-gated. See docs/integrating-a-product.md.
     */
    public static function get_user_ref(string $product, ?int $user_id = null): string
    {
        $user_id = null === $user_id ? get_current_user_id() : $user_id;
        if ($user_id <= 0) {
            return '';
        }

        $ensure = static function () use ($user_id, $product) {
            return UserRef::ensure((int) $user_id, $product);
        };

        $ref = AppPasswordLock::run((int) $user_id, $ensure);
        if (is_wp_error($ref)) {
            $ref = $ensure();
        }

        return is_string($ref) ? $ref : '';
    }

    /**
     * Drop the cached GRND after changing anything its claims are minted from; the next
     * exchange mints a new App Password as usual. Unlike force_new it isn't throttled and
     * keeps the wrap key. The built-in permission endpoint already calls this.
     */
    public static function invalidate_grnd(string $product, ?int $user_id = null): void
    {
        $user_id = null === $user_id ? get_current_user_id() : $user_id;
        if ($user_id <= 0) {
            return;
        }

        (new TokenManager())->forget((int) $user_id, $product);
    }

    /**
     * Revoke one product's WAP Application Passwords for every user, on every site,
     * plus any WAP password no site has on record. Call it from your plugin's
     * deactivation and uninstall hooks: once your plugin stops loading this library,
     * nothing restricts them any more. Other products' current passwords are kept.
     */
    public static function revoke_app_passwords(string $product): void
    {
        (new AppPasswordManager())->revoke_product($product);
    }

    /**
     * Private constructor — this class is a static facade only.
     */
    private function __construct()
    {
    }

    /**
     * Register a WAP chat admin page.
     *
     * Call this from your plugin's `admin_menu` action. The library handles
     * capability gating, App Password provisioning, session management, and
     * widget rendering automatically.
     *
     * 'capability' is the capability a user needs to see and use the page, and
     * the same key works for columns and embeds. Pass it rather than hooking the
     * `wap_client_capability` filter: that filter is shared by every plugin that
     * bundles this library, and it only applies to surfaces that declare none.
     *
     * Two options control how the page is presented, independently of each
     * other:
     *
     *   'hidden_admin_menu' => true         Register the page with no menu entry
     *                                       anywhere in wp-admin; it is reachable
     *                                       only at admin.php?page={menu_slug}.
     *                                       Takes precedence over 'parent_slug'.
     *   'render_mode'       => 'standalone' Output a bare HTML document with no
     *                                       wp-admin header, sidebar or footer,
     *                                       instead of a normal admin subpage.
     *                                       Defaults to 'admin'.
     *   'standalone_shell_css' => false     Skip the library's document-shell
     *                                       stylesheet so none of its body /
     *                                       box-sizing defaults reach a page
     *                                       the host styles itself. The host
     *                                       then owns the height chain too.
     *
     * Together they cover a first-run onboarding flow that should look like a
     * standalone wizard rather than a settings screen:
     *
     *   WapClient::register_chat_page([
     *       'menu_slug'         => 'my-plugin-onboarding',
     *       'page_title'        => 'Set up your site',
     *       'hidden_admin_menu' => true,
     *       'render_mode'       => 'standalone',
     *       'product'           => 'my-product-slug',
     *       'server_url'        => 'https://wap.group.one',
     *       'grnd'              => ['issuer_url' => '…', 'license_key' => '…'],
     *   ]);
     *
     * 'render' optionally hands the page body to a callable in either mode. It
     * receives a credential-free copy of the page config; call
     * ChatWidget::render_chat_root($menu_slug) inside it to place the widget:
     *
     *   'render' => function (array $page): void {
     *       echo '<header class="my-wizard-header">…</header>';
     *       ChatWidget::render_chat_root($page['menu_slug']);
     *   },
     *
     * @param array{
     *     menu_slug:    string,
     *     parent_slug?: string,
     *     page_title?:  string,
     *     menu_title?:  string,
     *     hidden_admin_menu?: bool,
     *     render_mode?: string,
     *     standalone_shell_css?: bool,
     *     render?:      callable,
     *     capability?:  string,
     *     product:      string,
     *     server_url:   string,
     *     grnd?:        array{issuer_url?: string, license_key?: string},
     *     grnd_provider?: callable,
     *     mode?:        string,
     *     mcp_endpoint?: string,
     *     available_products?: string[],
     *     terms_url?:   string,
     *     privacy_url?: string,
     *     site_access_url?: string,
     *     custom_css_url?: string,
     *     layout?:      array{width?: string, innerWidth?: string, align?: string,
     *                         expandToggle?: string, height?: string, chrome?: string,
     *                         accent?: string, keepScrollbar?: bool, showHeader?: bool,
     *                         showNewChat?: bool, showSettings?: bool, showDeleteData?: bool,
     *                         showAgentSelector?: bool},
     *     i18n?:        array<string, string> UI string overrides, merged with the
     *                         built-in defaults (caller keys win), e.g. 'consentText'
     *                         to customize the T&C consent modal copy.
     * } $args Configuration array.
     *
     * @return void
     */
    public static function register_chat_page(array $args): void
    {
        // Registered unconditionally, even when Application Passwords are
        // unavailable (no HTTPS, or disabled by another plugin) — the page
        // itself is where that reason is explained via a contextual notice
        // in place of the widget, rather than the page silently disappearing.
        //
        // ChatWidget::register() queues an admin_menu hook that checks current_user_can()
        // at render time, so this method is safe to call at plugins_loaded before the
        // user session is authenticated.
        ChatWidget::register($args);
    }

    /**
     * Register the chat widget as a collapsible column docked onto admin screens
     * the host owns. Same credential keys as register_chat_page().
     *
     * Pass `screens` (hook suffixes / WP_Screen ids), a `should_render` callable,
     * or both. With neither, the column renders nowhere.
     *
     * ```php
     * WapClient::register_chat_column([
     *     'id'         => 'wp-rocket-assistant',
     *     'product'    => 'wp-rocket',
     *     'server_url' => 'https://wap.group.one',
     *     'grnd'       => ['issuer_url' => '…', 'license_key' => get_option('…')],
     *     'screens'    => ['toplevel_page_wprocket'],
     *     'column'     => ['side' => 'right', 'width' => '400px'],
     * ]);
     * ```
     *
     * A React/SPA admin mounts from JS instead — `WapChat.mount(el, opts)`, or a
     * `[data-wap-chat-column]` element. See docs/integrating-a-product.md.
     *
     * @param array{
     *     id:              string,
     *     product:         string,
     *     server_url:      string,
     *     capability?:     string,
     *     screens?:        string[],
     *     should_render?:  callable,
     *     grnd?:           array{issuer_url?: string, license_key?: string},
     *     grnd_provider?:  callable,
     *     title?:          string,
     *     column?:         array{side?: string, width?: string, mode?: string,
     *                            defaultState?: string, breakpoint?: string,
     *                            showLauncher?: bool, persist?: bool,
     *                            startOnOpen?: bool},
     *     layout?:         array<string, mixed>,
     *     i18n?:           array<string, string>,
     * } $args Configuration array.
     *
     * @return void
     */
    public static function register_chat_column(array $args): void
    {
        // Registered unconditionally — see register_chat_page() for why.
        ChatColumn::register($args);
    }

    /**
     * Register the chat widget as an inline embed — no admin page, no menu
     * entry, no panel chrome. Same credential keys as register_chat_page().
     *
     * Use this when the chat belongs *inside* a screen you already own: a tab on
     * your settings page, a metabox, a post-editor sidebar panel. The library
     * provides the credentials, the assets and the localised config; you decide
     * where the widget appears by calling {@see self::render_chatbox()} or
     * {@see self::get_chatbox()} from your own template.
     *
     * Call on `init` or `admin_init`, **not** `admin_menu` — the AJAX auth and
     * consent handlers resolve the surface from the live registry, and
     * `admin_menu` never fires on `admin-ajax.php`.
     *
     * Pass `screens` (hook suffixes / WP_Screen ids), a `should_render`
     * callable, or both, to say which screens load the assets. With neither, the
     * embed activates nowhere — fail-closed, as for a column.
     *
     * ```php
     * add_action('admin_init', function () {
     *     WapClient::register_chat_embed([
     *         'id'         => 'my-plugin-content-ai',
     *         'product'    => 'my-product',
     *         'server_url' => 'https://wap.group.one',
     *         'grnd'       => ['issuer_url' => '…', 'license_key' => get_option('…')],
     *         'screens'    => ['toplevel_page_my-plugin', 'post', 'page'],
     *         'layout'     => ['height' => '640px'],
     *     ]);
     * });
     *
     * // …then, inside your own tab body / metabox:
     * WapClient::render_chatbox('my-plugin-content-ai');
     * ```
     *
     * A React admin mounts from JS against the same registration —
     * `WapChat.mount(el, {column: false})` — so no PHP mount point is needed.
     * See docs/integrating-a-product.md.
     *
     * @param array{
     *     id:              string,
     *     product:         string,
     *     server_url:      string,
     *     capability?:     string,
     *     screens?:        string[],
     *     should_render?:  callable,
     *     grnd?:           array{issuer_url?: string, license_key?: string},
     *     grnd_provider?:  callable,
     *     mode?:           string,
     *     mcp_endpoint?:   string,
     *     page_context?:   string,
     *     terms_url?:      string,
     *     privacy_url?:    string,
     *     site_access_url?: string,
     *     custom_css_url?: string,
     *     title?:          string,
     *     layout?:         array<string, mixed>,
     *     i18n?:           array<string, string>,
     * } $args Configuration array.
     *
     * @return void
     */
    public static function register_chat_embed(array $args): void
    {
        // Registered unconditionally — see register_chat_page() for why.
        ChatEmbed::register($args);
    }

    /**
     * Echo a registered embed's chatbox at this point in the markup.
     *
     * A no-op — no markup, no notice — when the embed is not registered, the
     * current screen was not opted in, or the user lacks the capability, so it
     * is safe to call unconditionally from a template.
     *
     * @param string $id The embed id passed to register_chat_embed().
     *
     * @return void
     */
    public static function render_chatbox(string $id): void
    {
        ChatEmbed::render($id);
    }

    /**
     * A registered embed's chatbox as an HTML string.
     *
     * For hosts that build markup rather than echo it. Returns '' in exactly the
     * cases where render_chatbox() emits nothing.
     *
     * @param string $id The embed id passed to register_chat_embed().
     *
     * @return string
     */
    public static function get_chatbox(string $id): string
    {
        return ChatEmbed::get($id);
    }

    /**
     * True when a registered embed will render on the current screen for the
     * current user.
     *
     * Use it to skip building your own chrome — a tab, a panel header, a
     * metabox — around a chatbox that is not going to appear.
     *
     * @param string $id The embed id passed to register_chat_embed().
     *
     * @return bool
     */
    public static function has_chatbox(string $id): bool
    {
        return ChatEmbed::is_active($id);
    }
}
