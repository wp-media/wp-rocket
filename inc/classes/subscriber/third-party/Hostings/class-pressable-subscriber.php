<?php
namespace WP_Rocket\Subscriber\Third_Party\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with Pressable hosting
 *
 * @since 3.3
 * @author Remy Perona
 */
class Pressable_Subscriber implements Subscriber_Interface {
	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'IS_PRESSABLE' ) || ! IS_PRESSABLE ) {
			return [];
		}

		return [
			'do_rocket_generate_caching_files'   => [ 'return_false', PHP_INT_MAX ],
			'rocket_display_varnish_options_tab' => 'return_false',
			'rocket_cache_mandatory_cookies'     => [ 'return_empty_array', PHP_INT_MAX ],
			'admin_init'                         => 'remove_advanced_cache_notices',
			'after_rocket_clean_domain'          => 'purge_pressable_cache',
			'rocket_url_to_path'                 => 'fix_wp_includes_path',
			'rocket_cdn_cnames'                  => [ 'add_pressable_cdn_cname', 1 ],
		];
	}

	/**
	 * Return false
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	public function return_false() {
		return false;
	}

	/**
	 * Return empty array
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public function return_empty_array() {
		return [];
	}

	/**
	 * Remove Advanced cache notices from WP Rocket since we can't modify it
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function remove_advanced_cache_notices() {
		remove_action( 'admin_notices', 'rocket_warning_advanced_cache_permissions' );
		remove_action( 'admin_notices', 'rocket_warning_advanced_cache_not_ours' );
	}

	/**
	 * Purge Pressable cache
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function purge_pressable_cache() {
		wp_cache_flush();
	}

	/**
	 * Modify wp-includes absolute path to be able to optimize assets in this directory on Pressable
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param string $file Absolute path to the file.
	 * @return string
	 */
	public function fix_wp_includes_path( $file ) {
		return preg_replace( '#^(.+)(wp-includes(?:.+))$#is', ABSPATH . '$2', $file );
	}

	/**
	 * Add Pressable CDN cname to WP Rocket list to recognize assets as internal ones.
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param array $hosts Array of domains.
	 * @return array
	 */
	public function add_pressable_cdn_cname( $hosts ) {
		if ( ! defined( 'WP_STACK_CDN_DOMAIN' ) || ! WP_STACK_CDN_DOMAIN ) {
			return $hosts;
		}

		$hosts[] = WP_STACK_CDN_DOMAIN;

		return $hosts;
	}
}
