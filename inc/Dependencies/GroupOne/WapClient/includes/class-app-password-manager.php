<?php
/**
 * App Password Manager — WordPress Application Password provisioning.
 *
 * Provisions a fresh WordPress Application Password on each WAP auth call.
 * The plaintext password is passed directly to the WAP backend and never
 * stored. The uuid of the most recently created password is kept in user_meta
 * so it can be revoked before the next provisioning.
 *
 * Also where T&C consent is enforced (see ConsentGate) — provision() refuses
 * to mint unless consent is currently granted.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * Manages WordPress Application Password provisioning for WAP auth.
 *
 * One password per (user, product) pair — per site on multisite — is kept
 * active at a time. Each call to provision() revokes the previous password
 * (if any) before creating a new one so Application Passwords do not
 * accumulate in the user's profile.
 */
class AppPasswordManager
{
    /**
     * Application ID every WAP password is created with (core's `app_id` field).
     *
     * Core keeps it inside the password record itself, so it stays attached to
     * the password whatever happens to the uuid tracked in user meta: it is how
     * AppPasswordScopeEnforcer recognises a WAP password that is no longer the
     * current one, and how a host's cleanup can find every WAP password.
     *
     * @var string
     */
    public const APP_ID = 'bce5143f-4c09-4737-b5a4-ada947c2e7ec';

    /**
     * WP Application Password label prefix, visible to site admins.
     *
     * @var string
     */
    private const APP_PASSWORD_LABEL_PREFIX = 'WAP – ';

    /**
     * Minimum age, in seconds, of an untagged "WAP – " password before the
     * sweep deletes it: a release without APP_ID records the uuid of the
     * password it creates moments after creating it, without the lock.
     *
     * @var int
     */
    private const UNTAGGED_SWEEP_MIN_AGE = 60;

    /**
     * Network option set once revoke_unenforceable() has swept every user.
     *
     * @var string
     */
    private const UNENFORCEABLE_SWEPT_OPTION = 'wap_client_unenforceable_swept';

    // -------------------------------------------------------------------------
    // Static helpers
    // -------------------------------------------------------------------------

    /**
     * Check whether WordPress Application Passwords are available.
     *
     * @return bool True when App Passwords can be used.
     */
    public static function are_app_passwords_available(): bool
    {
        // Ahead of the dev bypass: this is about where the scope checks run, not about the environment.
        if (!self::is_enforced_network_wide()) {
            return false;
        }

        // Dev bypass — define WAP_CLIENT_DEV_MODE in wp-config.php to skip the
        // HTTPS requirement on local non-SSL sites. Never use in production.
        if (defined('WAP_CLIENT_DEV_MODE') && WAP_CLIENT_DEV_MODE) {
            return true;
        }

        if (function_exists('wp_is_application_passwords_available')) {
            return wp_is_application_passwords_available();
        }

        return is_ssl();
    }

    /**
     * Whether HTTPS is the (or a) reason Application Passwords are unavailable.
     *
     * `wp_is_application_passwords_available()` is filterable, so a plugin
     * (e.g. a security plugin) can disable Application Passwords outright,
     * independent of the site's SSL status. Callers use this to distinguish
     * that case from a genuine "this site is not on HTTPS" case, so the
     * message shown to the user names the actual cause.
     *
     * @return bool True when the site is not on HTTPS (and the dev bypass is
     *              not active) — regardless of whether some other factor also
     *              disables Application Passwords.
     */
    public static function is_https_missing(): bool
    {
        if (defined('WAP_CLIENT_DEV_MODE') && WAP_CLIENT_DEV_MODE) {
            return false;
        }

        return !is_ssl();
    }

    /**
     * Whether this library runs on every site a WAP password can log in to.
     *
     * Application Passwords belong to the user, and a network's sites (every
     * network's, on a multi-network install) share their users: a password
     * minted on one site also logs in on all the others. AppPasswordScopeEnforcer
     * only fences it where this library is loaded, so on multisite the library
     * mints only when it is loaded everywhere: from a must-use plugin, or from a
     * plugin network-activated on every network.
     *
     * @return bool
     */
    public static function is_enforced_network_wide(): bool
    {
        if (!is_multisite()) {
            return true;
        }

        /**
         * Filters whether this library is loaded on every site of every network.
         *
         * For loaders the check below cannot see (the library symlinked into
         * place, an mu-plugin that loads a regular plugin, a host that loads the
         * library on some sites only). Return true only when it really runs
         * everywhere: on a site where it does not, WAP passwords are unrestricted.
         *
         * @param bool $enforced Whether the library detected it runs everywhere.
         */
        return (bool) apply_filters('wap_client_enforced_network_wide', self::loaded_network_wide());
    }

    /**
     * Why WAP cannot use Application Passwords on this site, for a host's own
     * notice: 'multisite' (the library does not run on every site of the
     * network), 'https', 'disabled' (turned off, e.g. by a security plugin), or
     * '' when it can.
     *
     * @return string
     */
    public static function unavailable_reason(): string
    {
        if (!self::is_enforced_network_wide()) {
            return 'multisite';
        }

        if (self::are_app_passwords_available()) {
            return '';
        }

        return self::is_https_missing() ? 'https' : 'disabled';
    }

    /**
     * Multisite: revoke the WAP passwords this site cannot fence, on admin_init.
     *
     * Where the library does not run on every site, every WAP password is an
     * unrestricted credential on the sites it does not run on, including those
     * earlier releases minted before they refused to. Once per network, every
     * user's are revoked; after that, the current user's, should a release
     * elsewhere on the network have minted one since.
     *
     * @return void
     */
    public static function revoke_unenforceable(): void
    {
        if (!is_multisite() || self::is_enforced_network_wide()) {
            return;
        }

        $manager = new self();

        if (!get_site_option(self::UNENFORCEABLE_SWEPT_OPTION)) {
            foreach (self::users_with_wap_passwords() as $wp_user_id) {
                $manager->revoke_all($wp_user_id);
            }
            update_site_option(self::UNENFORCEABLE_SWEPT_OPTION, time());
        }

        $wp_user_id = get_current_user_id();
        if ($wp_user_id > 0 && self::has_wap_password($wp_user_id)) {
            $manager->revoke_all($wp_user_id);
        }
    }

    /**
     * Whether this library is loaded from a must-use plugin, or from a plugin
     * network-activated on every network.
     *
     * @return bool
     */
    private static function loaded_network_wide(): bool
    {
        $file = wp_normalize_path(__FILE__);
        if (defined('WPMU_PLUGIN_DIR')) {
            // __FILE__ is resolved; the folder may be a symlink.
            foreach (array_filter([WPMU_PLUGIN_DIR, realpath(WPMU_PLUGIN_DIR)]) as $mu_dir) {
                if (0 === strpos($file, trailingslashit(wp_normalize_path((string) $mu_dir)))) {
                    return true;
                }
            }
        }

        // "wp-rocket/vendor/groupone/wap-client/includes/…" → "wp-rocket/", the host plugin's folder.
        // Outside the plugins folder plugin_basename() returns the path itself (minus its leading
        // slash), whose first segment no active plugin starts with.
        $basename = plugin_basename(__FILE__);
        $slash    = strpos($basename, '/');
        if (false === $slash) {
            return false;
        }
        $folder = substr($basename, 0, $slash + 1);

        $network_ids = function_exists('get_networks')
            ? get_networks(['fields' => 'ids'])
            : [get_current_network_id()];

        foreach ($network_ids as $network_id) {
            $network_wide = false;
            foreach (array_keys((array) get_network_option((int) $network_id, 'active_sitewide_plugins', [])) as $plugin) {
                if (0 === strpos((string) $plugin, $folder)) {
                    $network_wide = true;
                    break;
                }
            }

            if (!$network_wide) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether the user has a WAP password (from their cached meta: no query).
     *
     * @param int $wp_user_id WordPress user ID.
     *
     * @return bool
     */
    private static function has_wap_password(int $wp_user_id): bool
    {
        foreach (\WP_Application_Passwords::get_user_application_passwords($wp_user_id) as $item) {
            if (self::is_wap_password($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * IDs of the users with a WAP password or a WAP uuid key, on any site.
     *
     * @return int[]
     */
    private static function users_with_wap_passwords(): array
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $user_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE (meta_key = %s AND (meta_value LIKE %s OR meta_value LIKE %s)) OR meta_key LIKE %s",
                \WP_Application_Passwords::USERMETA_KEY_APPLICATION_PASSWORDS,
                '%' . $wpdb->esc_like(self::APP_ID) . '%',
                '%' . $wpdb->esc_like(self::APP_PASSWORD_LABEL_PREFIX) . '%',
                $wpdb->esc_like(AppPasswordGuard::META_KEY_PREFIX) . '%'
            )
        );

        return array_map('intval', (array) $user_ids);
    }

    /**
     * Whether an Application Password record is one this library minted:
     * created with APP_ID, or, for releases before it, labelled "WAP – …".
     *
     * @param array $item Application Password record, as core stores it.
     *
     * @return bool
     */
    public static function is_wap_password(array $item): bool
    {
        if (self::APP_ID === ($item['app_id'] ?? '')) {
            return true;
        }

        $name = $item['name'] ?? '';

        return is_string($name) && 0 === strpos($name, self::APP_PASSWORD_LABEL_PREFIX);
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Provision a new Application Password for the given user and product.
     *
     * The single consent enforcement point (see ConsentGate). Revokes the
     * previous password first; the plaintext returned must not be stored by the caller.
     */
    public function provision(int $wp_user_id, string $product, string $product_label = '')
    {
        if (!self::is_enforced_network_wide()) {
            // Including what earlier releases minted here before they refused to.
            $this->revoke_all($wp_user_id);

            return new \WP_Error(
                'wap_app_passwords_disabled',
                __('On a multisite network, the assistant is only available when the plugin that provides it is network-activated.', 'wap-client')
            );
        }

        if (!self::are_app_passwords_available()) {
            return new \WP_Error(
                'wap_app_passwords_disabled',
                __('WordPress Application Passwords are not available on this site. HTTPS is required.', 'wap-client')
            );
        }

        if (!ConsentGate::is_granted($wp_user_id, $product)) {
            return new \WP_Error(
                'wap_client_consent_required',
                __('Terms & Conditions consent is required before the assistant can be used.', 'wap-client')
            );
        }

        // Core rewrites all of a user's Application Passwords at once on every
        // change: two rotations at the same time would lose each other's writes.
        return AppPasswordLock::run(
            $wp_user_id,
            function () use ($wp_user_id, $product, $product_label) {
                return $this->rotate($wp_user_id, $product, $product_label);
            }
        );
    }

    /**
     * Revoke the stored Application Password for a (user, product) pair and
     * remove the uuid from user meta.
     *
     * Called during force-new re-auth (after a 401).
     *
     * @param int    $wp_user_id WordPress user ID.
     * @param string $product    Product slug.
     *
     * @return void
     */
    public function delete_stored_password(int $wp_user_id, string $product): void
    {
        $revoke = function () use ($wp_user_id, $product): void {
            $this->revoke_previous($wp_user_id, $product);

            // A GRND issued over the revoked password is dead weight — drop it so
            // the next auth cycle provisions both credentials fresh.
            (new TokenManager())->forget($wp_user_id, $product);
        };

        // A revocation is never worth skipping: without the lock, a concurrent
        // rotation can at worst restore a password the enforcer already refuses.
        if (is_wp_error(AppPasswordLock::run($wp_user_id, $revoke))) {
            $revoke();
        }
    }

    /**
     * Revoke every WAP password the user has, on every site, and forget them.
     *
     * Called during GDPR erasure. Uses core's API rather than the REST API,
     * which refuses when the current user cannot edit the owner.
     *
     * @param int $wp_user_id WordPress user ID.
     *
     * @return void
     */
    public function revoke_all(int $wp_user_id): void
    {
        $revoke = function () use ($wp_user_id): void {
            $token_manager = new TokenManager();

            foreach (AppPasswordGuard::tracked($wp_user_id) as $meta_key => $uuid) {
                \WP_Application_Passwords::delete_application_password($wp_user_id, $uuid);
                delete_user_meta($wp_user_id, $meta_key);

                // GRNDs cached on this site carry the deleted passwords.
                $product = AppPasswordGuard::product_for_key((string) $meta_key);
                if (null !== $product) {
                    $token_manager->forget($wp_user_id, $product);
                }
            }

            foreach (\WP_Application_Passwords::get_user_application_passwords($wp_user_id) as $item) {
                if (self::is_wap_password($item)) {
                    \WP_Application_Passwords::delete_application_password($wp_user_id, (string) $item['uuid']);
                }
            }
        };

        if (is_wp_error(AppPasswordLock::run($wp_user_id, $revoke))) {
            $revoke();
        }
    }

    /**
     * Revoke, for every user, one product's WAP passwords on every site, and
     * the WAP passwords no site has on record any more.
     *
     * For a host plugin's deactivation and uninstall hooks: once the host stops
     * loading this library, nothing fences its passwords. Another product's
     * current passwords are left alone (every host tags with the same APP_ID).
     * Uses core's API, so it works without a logged-in user (WP-CLI, cron).
     *
     * @param string $product Product slug.
     *
     * @return void
     */
    public function revoke_product(string $product): void
    {
        $product = sanitize_key($product);

        foreach (self::users_with_wap_passwords() as $wp_user_id) {
            $revoke = function () use ($wp_user_id, $product): void {
                foreach (AppPasswordGuard::tracked($wp_user_id) as $meta_key => $uuid) {
                    if ($product === AppPasswordGuard::product_for_key((string) $meta_key, true)) {
                        \WP_Application_Passwords::delete_application_password($wp_user_id, $uuid);
                        delete_user_meta($wp_user_id, (string) $meta_key);
                    }
                }

                $this->delete_untracked($wp_user_id);
                (new TokenManager())->forget($wp_user_id, $product);
            };

            if (is_wp_error(AppPasswordLock::run($wp_user_id, $revoke))) {
                $revoke();
            }
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Replace the (user, product) pair's password with a new one. Runs under
     * the user's AppPasswordLock.
     *
     * @param int    $wp_user_id    WordPress user ID.
     * @param string $product       Product slug.
     * @param string $product_label Label suffix for the new password.
     *
     * @return string|\WP_Error The new password's plaintext.
     */
    private function rotate(int $wp_user_id, string $product, string $product_label)
    {
        // Again under the lock: an erasure may have withdrawn consent while this request waited.
        if (!ConsentGate::is_granted($wp_user_id, $product)) {
            return new \WP_Error(
                'wap_client_consent_required',
                __('Terms & Conditions consent is required before the assistant can be used.', 'wap-client')
            );
        }

        // Password removed out-of-band (revoked, pruned): reset consent too, don't silently re-mint.
        $meta_key = $this->build_meta_key($product);
        $uuid_on_record = get_user_meta($wp_user_id, $meta_key, true);
        if ($uuid_on_record && !$this->stored_password_still_exists($wp_user_id, $product)) {
            delete_user_meta($wp_user_id, $meta_key);
            delete_user_meta($wp_user_id, 'wap_client_consent_' . sanitize_key($product));

            return new \WP_Error(
                'wap_client_consent_required',
                __('Terms & Conditions consent is required before the assistant can be used.', 'wap-client')
            );
        }

        // Revoke the previous password for this (user, product) pair.
        $this->revoke_previous($wp_user_id, $product);
        if (is_multisite()) {
            $this->revoke_network_wide_password($wp_user_id, $product);
        }

        $label   = self::APP_PASSWORD_LABEL_PREFIX . ($product_label ?: $product);
        $request = new \WP_REST_Request('POST', "/wp/v2/users/{$wp_user_id}/application-passwords");
        $request->set_body_params(['name' => $label, 'app_id' => self::APP_ID]);
        $response = rest_do_request($request);

        if ($response->is_error()) {
            $error   = $response->as_error();
            $code    = $error instanceof \WP_Error ? $error->get_error_code() : '';
            $message = $error instanceof \WP_Error ? $error->get_error_message() : '';

            if ('application_passwords_disabled' === $code) {
                return new \WP_Error(
                    'wap_app_passwords_disabled',
                    __('WordPress Application Passwords are not available on this site. HTTPS is required.', 'wap-client')
                );
            }

            if ('application_passwords_disabled_for_user' === $code) {
                return new \WP_Error(
                    'wap_app_passwords_disabled',
                    __('WordPress Application Passwords are not available for this account.', 'wap-client')
                );
            }

            return new \WP_Error(
                'wap_app_password_provision_failed',
                sprintf(
                    /* translators: 1: error message */
                    __('Failed to provision WAP Application Password: %s', 'wap-client'),
                    $message
                )
            );
        }

        $data = $response->get_data();

        if (empty($data['password']) || empty($data['uuid'])) {
            return new \WP_Error(
                'wap_app_password_missing',
                __('WordPress Application Password response did not contain expected fields.', 'wap-client')
            );
        }

        // Store the uuid so we can revoke this password before the next provisioning.
        update_user_meta($wp_user_id, $this->build_meta_key($product), sanitize_text_field($data['uuid']));

        // The app token is baked into the GRND, so their lifecycles are
        // coupled: a cached GRND issued over the previous (now revoked)
        // password must never be reused. Invalidate it in the same call.
        (new TokenManager())->forget($wp_user_id, $product);

        $this->delete_untracked($wp_user_id);

        return $data['password'];
    }

    /**
     * Revoke the previously stored Application Password via the WP REST API and
     * delete its uuid from user meta.
     *
     * @param int    $wp_user_id WordPress user ID.
     * @param string $product    Product slug.
     *
     * @return void
     */
    private function revoke_previous(int $wp_user_id, string $product): void
    {
        $uuid = get_user_meta($wp_user_id, $this->build_meta_key($product), true);
        if (!$uuid || !is_string($uuid)) {
            return;
        }

        $request = new \WP_REST_Request(
            'DELETE',
            "/wp/v2/users/{$wp_user_id}/application-passwords/{$uuid}"
        );
        rest_do_request($request);

        delete_user_meta($wp_user_id, $this->build_meta_key($product));
    }

    /**
     * Multisite: revoke the password a release without per-site keys kept for
     * the whole network (AppPasswordGuard::legacy_meta_key()), and forget it.
     * From here on each site rotates its own.
     *
     * @param int    $wp_user_id WordPress user ID.
     * @param string $product    Product slug.
     *
     * @return void
     */
    private function revoke_network_wide_password(int $wp_user_id, string $product): void
    {
        $meta_key = AppPasswordGuard::legacy_meta_key($product);
        $uuid     = get_user_meta($wp_user_id, $meta_key, true);
        if (!$uuid || !is_string($uuid)) {
            return;
        }

        \WP_Application_Passwords::delete_application_password($wp_user_id, $uuid);
        delete_user_meta($wp_user_id, $meta_key);
    }

    /**
     * Delete the user's WAP passwords that no site has on record any more.
     *
     * The lock keeps WAP's own rotations from undoing each other, but core
     * also rewrites the passwords from outside it (e.g. recording a
     * password's first use), and releases before the lock left such passwords
     * behind. AppPasswordScopeEnforcer refuses them wherever this library runs;
     * anywhere else, e.g. once the host plugin is deactivated, they would be
     * ordinary, unrestricted Application Passwords.
     *
     * @param int $wp_user_id WordPress user ID.
     *
     * @return void
     */
    private function delete_untracked(int $wp_user_id): void
    {
        $tracked = array_values(AppPasswordGuard::tracked($wp_user_id));

        foreach (\WP_Application_Passwords::get_user_application_passwords($wp_user_id) as $item) {
            $uuid = (string) ($item['uuid'] ?? '');
            if ('' === $uuid || in_array($uuid, $tracked, true) || !self::is_wap_password($item)) {
                continue;
            }

            $untagged = self::APP_ID !== ($item['app_id'] ?? '');
            if ($untagged && time() - (int) ($item['created'] ?? 0) < self::UNTAGGED_SWEEP_MIN_AGE) {
                continue;
            }

            \WP_Application_Passwords::delete_application_password($wp_user_id, $uuid);
        }
    }

    /**
     * Build the user meta key for storing a product's App Password uuid.
     * Delegates to AppPasswordGuard, the canonical owner of this format.
     */
    private function build_meta_key(string $product): string
    {
        return AppPasswordGuard::build_meta_key($product);
    }

    /**
     * Whether the App Password uuid on record for this (user, product) pair
     * still exists in WordPress core's own store (detects out-of-band
     * revocation/pruning; see rotate()). Shares AppPasswordGuard::current_item().
     */
    private function stored_password_still_exists(int $wp_user_id, string $product): bool
    {
        return null !== AppPasswordGuard::current_item($wp_user_id, $product);
    }
}
