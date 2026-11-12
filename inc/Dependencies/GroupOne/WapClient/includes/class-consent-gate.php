<?php
/**
 * Consent Gate — server-side source of truth for T&C consent.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

namespace WP_Rocket\Dependencies\GroupOne\WapClient;

defined('ABSPATH') || exit;

/**
 * Answers "has this user consented for this product?" from the built-in
 * per-user meta store. Filterable via `wap_client_consent_granted` so a
 * host with its own consent store (see the JS `cfg.consent` hook) can answer from there instead.
 */
final class ConsentGate
{
    /**
     * Whether the given user has granted consent for the given product.
     */
    public static function is_granted(int $wp_user_id, string $product): bool
    {
        $meta_key = 'wap_client_consent_' . sanitize_key($product);
        $default  = (bool) get_user_meta($wp_user_id, $meta_key, true);

        return (bool) apply_filters('wap_client_consent_granted', $default, $wp_user_id, $product);
    }
}
