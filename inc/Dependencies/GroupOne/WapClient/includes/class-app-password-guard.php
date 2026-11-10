<?php
/**
 * App Password Guard — centralizes the age/existence clock for a WAP-managed
 * Application Password. Reads WP core's own `created` timestamp so
 * is_expired() and should_rotate() can never drift out of sync.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * Age/existence checks for a (user, product) pair's WAP Application Password.
 */
final class AppPasswordGuard
{
    /** User meta key pattern: wap_app_password_uuid_{product_slug}, plus @{blog_id} on multisite. Canonical home — build_meta_key() below.
     * @var string */
    public const META_KEY_PREFIX = 'wap_app_password_uuid_';

    /** Separates the product slug from the blog ID in multisite keys; sanitize_key() never leaves one in a slug.
     * @var string */
    private const SITE_SEPARATOR = '@';

    /** Hard cutoff enforced by AppPasswordScopeEnforcer: password older than this fails auth outright.
     * @var int */
    private const HARD_TTL = HOUR_IN_SECONDS; // 3600s.

    /** Soft cutoff for TokenManager's cached GRND; shorter than HARD_TTL, unrelated to TokenManager::EXPIRY_SLACK.
     * @var int */
    private const ROTATE_TTL = 50 * MINUTE_IN_SECONDS; // 3000s.

    /**
     * Whether the (user, product) pair's Application Password is past the
     * hard cutoff, or missing/unresolvable entirely.
     */
    public static function is_expired(int $wp_user_id, string $product): bool
    {
        $age = self::age($wp_user_id, $product);

        return null === $age || $age >= self::HARD_TTL;
    }

    /**
     * Whether an Application Password record is past the hard cutoff, by its
     * own `created` field: the age of the password actually presented.
     */
    public static function is_item_expired(array $item): bool
    {
        return empty($item['created']) || time() - (int) $item['created'] >= self::HARD_TTL;
    }

    /**
     * Whether the (user, product) pair's Application Password should be
     * rotated — past the soft cutoff, or can no longer be found at all.
     */
    public static function should_rotate(int $wp_user_id, string $product): bool
    {
        $age = self::age($wp_user_id, $product);

        return null === $age || $age >= self::ROTATE_TTL;
    }

    /**
     * The Application Password item WP core has on record for this (user,
     * product) pair's stored uuid, or null if unresolvable. Shared lookup —
     * AppPasswordManager::stored_password_still_exists() uses this too.
     */
    public static function current_item(int $wp_user_id, string $product): ?array
    {
        $uuid = get_user_meta($wp_user_id, self::build_meta_key($product), true);
        if (!$uuid || !is_string($uuid)) {
            return null;
        }

        if (class_exists('\\WP_Application_Passwords')
            && method_exists('\\WP_Application_Passwords', 'get_user_application_password')
        ) {
            $item = \WP_Application_Passwords::get_user_application_password($wp_user_id, $uuid);

            return is_array($item) ? $item : null;
        }

        // Fall back to the functional wrapper (older WP core naming).
        if (!function_exists('wp_get_application_passwords')) {
            return null;
        }

        foreach (wp_get_application_passwords($wp_user_id) as $existing) {
            if (($existing['uuid'] ?? '') === $uuid) {
                return $existing;
            }
        }

        return null;
    }

    /**
     * Which product (if any) this user's WAP-managed uuid belongs to on this site.
     * Reverse of build_meta_key() — given a uuid, find the product that minted it.
     *
     * On multisite only this site's keys count, plus the network-wide key of
     * releases that did not key per site (legacy_meta_key()): a password
     * minted for another site of the network is not this site's.
     */
    public static function product_for_uuid(int $wp_user_id, string $uuid): ?string
    {
        foreach (self::tracked($wp_user_id) as $meta_key => $tracked_uuid) {
            if ($tracked_uuid !== $uuid) {
                continue;
            }

            $product = self::product_for_key((string) $meta_key);
            if (null !== $product) {
                return $product;
            }
        }

        return null;
    }

    /**
     * The product slug a uuid meta key tracks a password for on this site:
     * null for another site's key (multisite, unless $any_site) or a key of
     * another format.
     */
    public static function product_for_key(string $meta_key, bool $any_site = false): ?string
    {
        if (0 !== strpos($meta_key, self::META_KEY_PREFIX)) {
            return null;
        }

        $product = (string) substr($meta_key, strlen(self::META_KEY_PREFIX));
        $at      = strrpos($product, self::SITE_SEPARATOR);
        if (false !== $at) {
            if (!$any_site && (!is_multisite() || (int) substr($product, $at + 1) !== get_current_blog_id())) {
                return null;
            }
            $product = substr($product, 0, $at);
        }

        return '' !== $product ? $product : null;
    }

    /**
     * Every WAP-managed uuid on record for the user, on every site, as meta key => uuid.
     *
     * @return array<string, string>
     */
    public static function tracked(int $wp_user_id): array
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_key, meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE %s",
                $wp_user_id,
                $wpdb->esc_like(self::META_KEY_PREFIX) . '%'
            ),
            ARRAY_A
        );

        $tracked = [];
        foreach ((array) $rows as $row) {
            if (is_string($row['meta_value'] ?? null) && '' !== $row['meta_value']) {
                $tracked[(string) $row['meta_key']] = $row['meta_value'];
            }
        }

        return $tracked;
    }

    /**
     * Build the user meta key for storing a product's App Password uuid.
     *
     * Application Passwords belong to the user, and a network's sites share
     * their users, so on multisite the key also names the site:
     * "wap_app_password_uuid_{product}@{blog_id}". Each site then keeps its own
     * password, which AppPasswordScopeEnforcer refuses on every other site.
     */
    public static function build_meta_key(string $product): string
    {
        $key = self::META_KEY_PREFIX . sanitize_key($product);

        return is_multisite() ? $key . self::SITE_SEPARATOR . get_current_blog_id() : $key;
    }

    /**
     * The network-wide key releases before per-site keys used on multisite
     * (the single-site key format). Still honoured until the next rotation
     * replaces it, so upgrading never turns a tracked password into an unknown one.
     */
    public static function legacy_meta_key(string $product): string
    {
        return self::META_KEY_PREFIX . sanitize_key($product);
    }

    /**
     * Seconds since the (user, product) pair's Application Password was
     * created, or null when there is no password on record.
     */
    private static function age(int $wp_user_id, string $product): ?int
    {
        $item = self::current_item($wp_user_id, $product);
        if (null === $item || empty($item['created'])) {
            return null;
        }

        return time() - (int) $item['created'];
    }
}
