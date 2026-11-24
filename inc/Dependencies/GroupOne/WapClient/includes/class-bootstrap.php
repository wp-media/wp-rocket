<?php
/**
 * Activation, text domain and boot hooks.
 *
 * Lives under includes/ in the library namespace so a namespace-prefixing tool rewrites the class names
 * registered as hook callbacks. The global wap_client_* functions in wap-client-api.php delegate here.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

final class Bootstrap
{
    /**
     * Earlier than the default 10, so a copy of the library that routes by ownership answers its own requests
     * before an older copy (one that registers its handlers directly at 10) can reject them.
     */
    private const DISPATCH_PRIORITY = 1;

    /**
     * The AJAX actions, each mapped to [handler class, handler method, ownership predicate].
     *
     * - wap_client_auth: server-side auth call to WAP (avoids browser CORS restrictions).
     * - wap_client_delete_data: GDPR erasure, proxies DELETE /api/v1/me/data to WAP.
     * - wap_client_consent: T&C consent read/record (backs the widget's default consent hook).
     * - wap_client_column_state: docked-column state, persisted per user.
     * - wap_client_permission_state: settings-sheet permission state, persisted per user and product.
     */
    private const AJAX = [
        'wap_client_auth'             => [ChatWidget::class, 'ajax_auth', 'owns_auth_request'],
        'wap_client_delete_data'      => [GdprHandler::class, 'ajax_delete_data', null],
        'wap_client_consent'          => [ChatWidget::class, 'ajax_consent', 'owns_product_request'],
        'wap_client_column_state'     => [ChatColumn::class, 'ajax_state', 'owns_state_request'],
        'wap_client_permission_state' => [ChatWidget::class, 'ajax_permission_state', 'owns_product_request'],
    ];

    /**
     * On activation: grant the wap_use_ai capability to administrator and editor roles.
     *
     * Uses the filter `wap_client_capability` in case the capability name is overridden.
     * No surface is involved here, so the product and surface id the filter passes are ''.
     */
    public static function activate(): void
    {
        $capability = apply_filters('wap_client_capability', 'wap_use_ai', '', '');
        $roles      = ['administrator', 'editor'];

        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role instanceof \WP_Role) {
                $role->add_cap($capability);
            }
        }
    }

    /**
     * On deactivation: optionally remove the capability.
     *
     * We leave it in place so that re-activating the plugin does not change
     * any admin-configured capability grants.
     */
    public static function deactivate(): void
    {
        // Intentionally a no-op. Capability grants are preserved across deactivation
        // so that site administrators can manage them independently.
    }

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
    public static function load_textdomain(): void
    {
        load_plugin_textdomain(
            'wap-client',
            false,
            dirname(plugin_basename(Config::file())) . '/i18n'
        );
    }

    /**
     * Boot the WAP client after all plugins have loaded.
     *
     * Registers AJAX handlers and the GDPR erasure handler.
     */
    public static function boot(): void
    {
        // Each AJAX action is registered twice: dispatch_ajax answers requests this copy owns, and fallback_ajax
        // (priority 99) gives whatever is still unanswered the handler's usual error. See dispatch_ajax().
        foreach (array_keys(self::AJAX) as $action) {
            add_action('wp_ajax_' . $action, [self::class, 'dispatch_ajax'], self::DISPATCH_PRIORITY);
            add_action('wp_ajax_' . $action, [self::class, 'fallback_ajax'], 99);
        }

        // Enforce expiry + MCP-only scope for WAP-issued Application Passwords.
        AppPasswordScopeEnforcer::register();

        // Multisite without the library on every site: revoke the WAP passwords nothing can fence.
        add_action('admin_init', [AppPasswordManager::class, 'revoke_unenforceable']);
    }

    /**
     * Run the current AJAX action's handler if this copy of the library owns the request.
     *
     * The handler answers and exits. A copy that does not own the request returns without output, so the next
     * copy's callback on the same action gets its turn.
     */
    public static function dispatch_ajax(): void
    {
        $route = self::AJAX[self::ajax_action()] ?? null;
        if (null === $route) {
            return;
        }

        [$class, $method, $owns] = $route;

        // delete_data has no surface of its own: it is this copy's when the user may use one of its surfaces.
        $owned = null === $owns ? ChatWidget::user_can_use_any_surface() : $class::$owns();
        if (!$owned) {
            return;
        }

        $class::$method();
    }

    /**
     * Last in line (priority 99): no copy owned the request, so run the handler for its own error response.
     */
    public static function fallback_ajax(): void
    {
        $route = self::AJAX[self::ajax_action()] ?? null;
        if (null !== $route) {
            [$class, $method] = $route;
            $class::$method();
        }
    }

    /** The AJAX action being run, without the `wp_ajax_` prefix. */
    private static function ajax_action(): string
    {
        return (string) preg_replace('/^wp_ajax_/', '', (string) current_action());
    }
}
