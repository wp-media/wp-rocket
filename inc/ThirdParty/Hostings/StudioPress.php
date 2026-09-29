<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Compatibility class for StudioPress / Genesis Accelerator.
 *
 * This subscriber is intentionally registered unconditionally (no
 * HostResolver detection gate). SP_Accel_Nginx_Proxy_Cache_Purge can be
 * defined by a Genesis/StudioPress child theme's functions.php, which
 * WordPress loads AFTER the plugins_loaded action that HostResolver runs
 * on. A resolver-time class_exists() check would silently fail to detect
 * theme-defined instances and drop these hooks on exactly the sites this
 * integration targets. Detection is therefore performed at hook-fire time
 * inside each callback (isset()/is_a() guard below), same as the legacy
 * procedural file it replaces.
 */
class StudioPress implements Subscriber_Interface {
	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_init'                => 'clear_cache_after_accelerator',
			'rocket_after_clean_domain' => 'clean_accelerator_cache',
		];
	}

	/**
	 * Clear WP Rocket cache after the StudioPress Accelerator cache was purged.
	 *
	 * Ported verbatim from rocket_clear_cache_after_studiopress_accelerator().
	 *
	 * @return void
	 */
	public function clear_cache_after_accelerator() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( isset( $GLOBALS['sp_accel_nginx_proxy_cache_purge'] ) && is_a( $GLOBALS['sp_accel_nginx_proxy_cache_purge'], 'SP_Accel_Nginx_Proxy_Cache_Purge' ) && isset( $_REQUEST['_wpnonce'] ) ) {
			$nonce = $_REQUEST['_wpnonce']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.NonceVerification.Recommended
			if ( wp_verify_nonce( $nonce, 'sp-accel-purge-url' ) && ! empty( $_REQUEST['cache-purge-url'] ) ) {
				$submitted_url = sanitize_text_field( wp_unslash( $_REQUEST['cache-purge-url'] ) );

				// Clear the URL.
				rocket_clean_files( [ $submitted_url ] );
			} elseif ( wp_verify_nonce( $nonce, 'sp-accel-purge-theme' ) ) {
				// Clear all caching files.
				rocket_clean_domain();

				// Preload cache.
				run_rocket_bot();
				run_rocket_sitemap_preload();
			}
		}
	}

	/**
	 * Call the cache server to purge the cache with StudioPress Accelerator.
	 *
	 * Ported verbatim from rocket_clean_studiopress_accelerator().
	 *
	 * @return void
	 */
	public function clean_accelerator_cache() {
		if ( isset( $GLOBALS['sp_accel_nginx_proxy_cache_purge'] ) && is_a( $GLOBALS['sp_accel_nginx_proxy_cache_purge'], 'SP_Accel_Nginx_Proxy_Cache_Purge' ) ) {
			$GLOBALS['sp_accel_nginx_proxy_cache_purge']->cache_flush_theme(); // @phpstan-ignore-line
		}
	}
}
