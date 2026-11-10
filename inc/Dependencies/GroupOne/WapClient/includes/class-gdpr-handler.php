<?php
/**
 * GDPR Handler — implements the right-to-erasure flow.
 *
 * Proxies DELETE /api/v1/me/data to the WAP backend server-side (no CORS
 * preflight from the browser). The session token is passed through PHP memory
 * only — it is never stored or persisted in WordPress.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * GDPR erasure handler for the WAP client.
 *
 * Implements US12 — when a user requests deletion of their WAP data:
 *  1. Call DELETE /api/v1/me/data on the WAP backend (deletes the user's
 *     LangGraph checkpoints; WAP stores no credentials of its own).
 *  2. Delete all locally cached session transients for the user.
 *  3. Delete all locally stored App Passwords for the user.
 */
class GdprHandler
{
    /**
     * AJAX action name.
     *
     * @var string
     */
    public const AJAX_ACTION = 'wp_rocket_wap_client_delete_data';

    // -------------------------------------------------------------------------
    // AJAX handler
    // -------------------------------------------------------------------------

    /**
     * AJAX handler: clean up local WordPress state after GDPR erasure.
     *
     * The WAP backend deletion is performed directly by the JS widget
     * (DELETE /api/v1/me/data with Bearer token). This handler only removes
     * local WordPress state (App Passwords) — no token is received or stored.
     *
     * @return void (sends JSON response and exits)
     */
    public static function ajax_delete_data(): void
    {
        check_ajax_referer('wp_rocket_wap_client_delete_data');

        // Erases the current user's own data for every product, so any surface
        // the user may use is enough.
        if (!ChatWidget::user_can_use_any_surface()) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wap-client')], 403);
        }

        $current_user = wp_get_current_user();
        if (!$current_user->exists()) {
            wp_send_json_error(['message' => __('User not authenticated.', 'wap-client')], 401);
        }

        self::purge_local_state($current_user->ID);

        wp_send_json_success(['message' => __('Your data has been deleted.', 'wap-client')]);
    }

    // -------------------------------------------------------------------------
    // Local state cleanup
    // -------------------------------------------------------------------------

    /**
     * Remove all WAP-related local state for a WordPress user.
     *
     * Called after a successful WAP data deletion. Revokes all stored
     * App Password user meta keys (wap_app_password_uuid_*).
     *
     * @param int $wp_user_id WordPress user ID.
     *
     * @return void
     */
    public static function purge_local_state(int $wp_user_id): void
    {
        $purge = static function () use ($wp_user_id): void {
            // Revoke all active Application Passwords for this user.
            self::delete_all_app_passwords($wp_user_id);

            // Drop recorded T&C consent (wap_client_consent_{product}) so the
            // consent gate is shown again before the next conversation.
            self::delete_all_consent_meta($wp_user_id);
        };

        // Under the user's lock, consent included: a tab waiting to rotate then
        // finds the consent gone and mints nothing after the erasure.
        if (is_wp_error(AppPasswordLock::run($wp_user_id, $purge))) {
            $purge();
        }

        // Permission choices (wap_client_permission_*) are settings, not conversation data: kept, so
        // a wipe can't silently put a user who chose Deny back on a more permissive default.
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Revoke every WAP App Password of a given user, on every site, with the
     * wap_app_password_uuid_* meta that tracks them.
     *
     * @param int $wp_user_id WordPress user ID.
     *
     * @return void
     */
    private static function delete_all_app_passwords(int $wp_user_id): void
    {
        (new AppPasswordManager())->revoke_all($wp_user_id);
    }

    /**
     * Delete all T&C consent user meta entries (wap_client_consent_*).
     *
     * @param int $wp_user_id WordPress user ID.
     *
     * @return void
     */
    private static function delete_all_consent_meta(int $wp_user_id): void
    {
        self::delete_meta_with_prefix($wp_user_id, 'wap_client_consent_');
    }

    /**
     * Delete every user meta entry whose key starts with `$prefix`, e.g. 'wap_client_consent_'.
     */
    private static function delete_meta_with_prefix(int $wp_user_id, string $prefix): void
    {
        global $wpdb;

        $like_pattern = $wpdb->esc_like($prefix) . '%';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $meta_keys = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT meta_key FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE %s",
                $wp_user_id,
                $like_pattern
            )
        );

        foreach ($meta_keys as $meta_key) {
            delete_user_meta($wp_user_id, (string) $meta_key);
        }
    }
}
