<?php
/**
 * The library's public functions and the `WapClient` facade.
 *
 * Required by wap-client.php only once WordPress is loaded. Not under includes/, which is PSR-4:
 * a global class there would trip Composer's optimised-autoloader warnings.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    return;
}

use WP_Rocket\Dependencies\GroupOne\WapClient\ChatColumn;
use WP_Rocket\Dependencies\GroupOne\WapClient\ChatEmbed;
use WP_Rocket\Dependencies\GroupOne\WapClient\ChatWidget;
use WP_Rocket\Dependencies\GroupOne\WapClient\GdprHandler;

// ---------------------------------------------------------------------------
// Boot hooks
// ---------------------------------------------------------------------------

add_action('init', 'wp_rocket_wap_client_load_textdomain');
add_action('plugins_loaded', 'wp_rocket_wap_client_boot', 20);

/**
 * Load bundled translations for the `wap-client` text domain.
 *
 * Translation files live in the package-root `i18n/` directory as
 * `wap-client-{locale}.mo` and follow the active WordPress admin locale, so all
 * widget UI strings (labels, errors, disclaimers) are localised automatically.
 *
 * Loaded on `init` (not earlier) per WordPress 6.7+ just-in-time-translation
 * guidance.
 */
function wp_rocket_wap_client_load_textdomain(): void {
    load_plugin_textdomain(
        'wap-client',
        false,
        dirname(plugin_basename(WP_ROCKET_WAP_CLIENT_FILE)) . '/i18n'
    );
}

/**
 * Boot the WAP client after all plugins have loaded.
 *
 * Registers AJAX handlers and the GDPR erasure handler.
 */
function wp_rocket_wap_client_boot(): void {
    // AJAX: server-side auth call to WAP (avoids browser CORS restrictions).
    add_action('wp_ajax_wp_rocket_wap_client_auth', ['WP_Rocket\\Dependencies\\GroupOne\\WapClient\\ChatWidget', 'ajax_auth']);

    // AJAX: GDPR erasure — proxies DELETE /api/v1/me/data to WAP.
    add_action('wp_ajax_wp_rocket_wap_client_delete_data', ['WP_Rocket\\Dependencies\\GroupOne\\WapClient\\GdprHandler', 'ajax_delete_data']);

    // AJAX: T&C consent read/record (backs the widget's default consent hook).
    add_action('wp_ajax_wp_rocket_wap_client_consent', ['WP_Rocket\\Dependencies\\GroupOne\\WapClient\\ChatWidget', 'ajax_consent']);

    // AJAX: docked-column state, persisted per user.
    add_action('wp_ajax_wp_rocket_wap_client_column_state', ['WP_Rocket\\Dependencies\\GroupOne\\WapClient\\ChatColumn', 'ajax_state']);

    // AJAX: settings-sheet permission state, persisted per user and product.
    add_action('wp_ajax_wp_rocket_wap_client_permission_state', ['WP_Rocket\\Dependencies\\GroupOne\\WapClient\\ChatWidget', 'ajax_permission_state']);

    // Enforce expiry + MCP-only scope for WAP-issued Application Passwords.
    \WP_Rocket\Dependencies\GroupOne\WapClient\AppPasswordScopeEnforcer::register();

    // Multisite without the library on every site: revoke the WAP passwords nothing can fence.
    add_action('admin_init', ['WP_Rocket\\Dependencies\\GroupOne\\WapClient\\AppPasswordManager', 'revoke_unenforceable']);
}

// ---------------------------------------------------------------------------
// WapClient static facade
// ---------------------------------------------------------------------------

/**
 * WapClient — public integration API.
 *
 * Consuming plugins call WP_Rocket_WapClient::register_chat_page() from their admin
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
 * WP_Rocket_WapClient::register_chat_page([
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
final class WP_Rocket_WapClient
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
     *     password_label?: string,
     *     extra_headers?:  array<string, string>,
     * } $args See {@see \WP_Rocket\Dependencies\GroupOne\WapClient\GrndService::get_grnd()}.
     *
     * @return string|\WP_Error The GRND on success; WP_Error with a named code
     *         on configuration / availability / sanity failures.
     *
     * @example
     *   // Echo of the test-client AJAX flow:
     *   $grnd = \WP_Rocket_WapClient::get_grnd_token([
     *       'product'    => 'wp-rocket',
     *       'server_url' => 'http://localhost:8010',
     *       'issuer_url' => 'https://api.brand.test/grnd/token',
     *   ]);
     *   if (is_wp_error($grnd)) { wp_send_json_error(['message' => 'unavailable'], 502); }
     *   wp_send_json_success(['token' => $grnd]);
     */
    public static function get_grnd_token(array $args)
    {
        return \WP_Rocket\Dependencies\GroupOne\WapClient\GrndService::get_grnd($args);
    }

    /**
     * A user's permission choices as `id => value` (default: current user) — always complete
     * and defaulted. Send it to your GRND issuer at mint time:
     * `'permissions' => \WP_Rocket_WapClient::get_permissions('rankmath')`.
     */
    public static function get_permissions(string $product, ?int $user_id = null): array
    {
        return \WP_Rocket\Dependencies\GroupOne\WapClient\ChatWidget::permissions_for($product, $user_id);
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

        (new \WP_Rocket\Dependencies\GroupOne\WapClient\TokenManager())->forget((int) $user_id, $product);
    }

    /**
     * Revoke one product's WAP Application Passwords for every user, on every site,
     * plus any WAP password no site has on record. Call it from your plugin's
     * deactivation and uninstall hooks: once your plugin stops loading this library,
     * nothing restricts them any more. Other products' current passwords are kept.
     */
    public static function revoke_app_passwords(string $product): void
    {
        (new \WP_Rocket\Dependencies\GroupOne\WapClient\AppPasswordManager())->revoke_product($product);
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
     *   WP_Rocket_WapClient::register_chat_page([
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
     *       \WP_Rocket\Dependencies\GroupOne\WapClient\ChatWidget::render_chat_root($page['menu_slug']);
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
     * WP_Rocket_WapClient::register_chat_column([
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
     *                            showLauncher?: bool, persist?: bool},
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
     *     WP_Rocket_WapClient::register_chat_embed([
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
     * WP_Rocket_WapClient::render_chatbox('my-plugin-content-ai');
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
