<?php
/**
 * App Password Scope Enforcer — hard-cutoff expiry and MCP-only scope for
 * WAP-issued Application Passwords, and refusal of WAP passwords that are not
 * this site's current one, via WordPress's own
 * `wp_authenticate_application_password_errors` action. See
 * docs/consent-and-credential-flow.md#43-enforcement for the full rationale.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * Enforces expiry and MCP-only scope for WAP-managed Application Passwords.
 */
final class AppPasswordScopeEnforcer
{
    /**
     * Register the enforcement hook. It fires on every successful Application
     * Password check (REST and XML-RPC alike); 3 args so the raw password never reaches us.
     */
    public static function register(): void
    {
        add_action('wp_authenticate_application_password_errors', [self::class, 'check'], 10, 3);
    }

    /**
     * wp_authenticate_application_password_errors callback: adding an error
     * makes core fail the authentication, so no user is ever set.
     *
     * @param \WP_Error $error Errors collected so far; core refuses the login if non-empty.
     * @param \WP_User  $user  The user whose password just matched.
     * @param array     $item  The matched Application Password record.
     */
    public static function check(\WP_Error $error, \WP_User $user, array $item): void
    {
        if ($error->has_errors()) {
            // Already refused by someone else — nothing to add.
            return;
        }

        $uuid = $item['uuid'] ?? '';
        if (!is_string($uuid) || '' === $uuid) {
            return;
        }

        $product = AppPasswordGuard::product_for_uuid($user->ID, $uuid);
        if (null === $product) {
            if (AppPasswordManager::is_wap_password($item)) {
                // WAP minted it, but it is not this site's current password for any
                // product: one a rotation replaced (and a concurrent write to the
                // user's passwords restored), or one minted for another site of the network.
                $error->add(
                    'wap_app_password_revoked',
                    __('This Application Password is no longer valid.', 'wap-client'),
                    ['status' => 401]
                );
            }

            // Otherwise not one WAP minted — leave it to whatever else the site does.
            return;
        }

        if (AppPasswordGuard::is_item_expired($item)) {
            $error->add(
                'wap_app_password_expired',
                __('This Application Password has expired.', 'wap-client'),
                ['status' => 401]
            );
            return;
        }

        if (!self::is_mcp_endpoint_request()) {
            $error->add(
                'wap_app_password_scope',
                __('This Application Password may only be used on the MCP endpoint.', 'wap-client'),
                ['status' => 403]
            );
        }
    }

    /**
     * Whether the current request is a REST request to this site's configured
     * MCP endpoint (ChatWidget::mcp_endpoint_url()).
     */
    private static function is_mcp_endpoint_request(): bool
    {
        if (!defined('REST_REQUEST') || !REST_REQUEST) {
            // XML-RPC or any other context core accepts Application Passwords in.
            return false;
        }

        $route = $GLOBALS['wp']->query_vars['rest_route'] ?? null;
        if (!is_string($route) || '' === $route) {
            return false;
        }
        $route = untrailingslashit($route);

        $mcp_route = self::mcp_route();

        // An endpoint resolving to the REST root ('') would match the REST index: never a match.
        return null !== $mcp_route && '' !== $mcp_route && $route === $mcp_route;
    }

    /**
     * The REST route of the site's configured MCP endpoint, e.g.
     * "/mcp/mcp-adapter-default-server", or null when the URL has no path.
     *
     * Read relative to this site's REST base, not to the domain root, so a
     * site whose home URL has a path (a subfolder install, a subsite of a
     * subdirectory network) resolves the route its requests carry.
     */
    private static function mcp_route(): ?string
    {
        $mcp_url = ChatWidget::mcp_endpoint_url();

        // Plain permalinks: "…/?rest_route=/mcp/…".
        $query = wp_parse_url($mcp_url, PHP_URL_QUERY);
        if (is_string($query) && '' !== $query) {
            parse_str($query, $args);
            if (isset($args['rest_route']) && is_string($args['rest_route']) && '' !== $args['rest_route']) {
                return untrailingslashit($args['rest_route']);
            }
        }

        $mcp_path = wp_parse_url($mcp_url, PHP_URL_PATH);
        if (!is_string($mcp_path) || '' === $mcp_path) {
            return null;
        }

        // "/blog/wp-json/" on a site whose home URL is "https://example.com/blog". Built from the
        // home path rather than get_rest_url(), which is "/blog/?rest_route=/" under plain permalinks.
        $rest_base = trailingslashit((string) wp_parse_url(home_url('/'), PHP_URL_PATH)) . rest_get_url_prefix() . '/';
        if (0 === strpos($mcp_path, $rest_base)) {
            return untrailingslashit('/' . substr($mcp_path, strlen($rest_base)));
        }

        // A URL off this site's REST base (e.g. a filtered one): strip a leading REST prefix if any.
        $prefix    = '/' . rest_get_url_prefix();
        $mcp_route = str_starts_with($mcp_path, $prefix)
            ? substr($mcp_path, strlen($prefix))
            : $mcp_path;

        return untrailingslashit($mcp_route);
    }
}
