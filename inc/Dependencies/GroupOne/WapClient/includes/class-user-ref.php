<?php
/**
 * User reference — a random, opaque, per-user and per-product identifier.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * Stores and creates the reference a host may send to its GRND issuer so each
 * WordPress user gets a separate chat history without the user's id, login or
 * email leaving the site.
 *
 * The value is 32 random lowercase hex characters kept in user meta
 * `wap_client_user_ref_{product}`. See docs/integrating-a-product.md.
 */
final class UserRef
{
    /**
     * User meta key prefix; the sanitised product slug is appended.
     *
     * @var string
     */
    private const META_PREFIX = 'wap_client_user_ref_';

    /**
     * Error code returned when no valid reference can be read or created.
     *
     * @var string
     */
    public const ERROR_UNAVAILABLE = 'wap_user_ref_unavailable';

    /**
     * User meta key holding the reference of a product.
     *
     * @param string $product Product slug.
     *
     * @return string
     */
    public static function meta_key(string $product): string
    {
        return self::META_PREFIX . sanitize_key($product);
    }

    /**
     * Whether a value is a well-formed reference (32 lowercase hex characters).
     *
     * @param string $value Candidate value.
     *
     * @return bool
     */
    public static function is_valid(string $value): bool
    {
        // \z, not $: "$" also matches before a trailing newline.
        return 1 === preg_match('/^[0-9a-f]{32}\z/', $value);
    }

    /**
     * The stored reference, or null when none is stored or the stored value is malformed.
     *
     * @param int    $user_id WordPress user ID.
     * @param string $product Product slug.
     *
     * @return string|null
     */
    public static function stored(int $user_id, string $product): ?string
    {
        $value = get_user_meta($user_id, self::meta_key($product), true);

        return is_string($value) && self::is_valid($value) ? $value : null;
    }

    /**
     * The user's reference, created on first use.
     *
     * Callers hold AppPasswordLock when they can. The unique add followed by a
     * re-read is the safety net for the paths that cannot: two unlocked writers
     * can both insert a row, and every caller converges on the row that reads back.
     *
     * @param int    $user_id WordPress user ID.
     * @param string $product Product slug.
     *
     * @return string|\WP_Error The reference, or `wap_user_ref_unavailable`.
     */
    public static function ensure(int $user_id, string $product)
    {
        if ($user_id <= 0) {
            return self::unavailable('No WordPress user to create a reference for.');
        }

        $existing = self::stored($user_id, $product);
        if (null !== $existing) {
            return $existing;
        }

        $key = self::meta_key($product);

        // A malformed value was never sent (reads validate), so dropping it loses nothing.
        // A valid value that appeared since the read above is a rival's: keep it.
        if (metadata_exists('user', $user_id, $key)) {
            $current = get_user_meta($user_id, $key, true);
            if (is_string($current) && self::is_valid($current)) {
                return $current;
            }
            // Only the row read: a rival's valid row added since must survive. An empty value
            // cannot be scoped (WordPress then deletes every row), so that case stays unscoped.
            if (is_string($current) && '' !== $current) {
                delete_user_meta($user_id, $key, $current);
            } else {
                delete_user_meta($user_id, $key);
            }
        }

        try {
            $new = bin2hex(random_bytes(16));
        } catch (\Throwable $e) {
            return self::unavailable('No source of randomness for a user reference.');
        }

        add_user_meta($user_id, $key, $new, true);

        // Always re-read: the add may have lost a race, and the object cache may predate it.
        wp_cache_delete($user_id, 'user_meta');

        $stored = self::stored($user_id, $product);

        return $stored ?? self::unavailable('The user reference could not be stored.');
    }

    /**
     * Build the unavailable error.
     *
     * @param string $message Integrator-facing reason.
     *
     * @return \WP_Error
     */
    private static function unavailable(string $message): \WP_Error
    {
        return new \WP_Error(self::ERROR_UNAVAILABLE, $message);
    }
}
