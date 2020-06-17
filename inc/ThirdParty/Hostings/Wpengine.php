<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Compatibility class for wpengine
 *
 * @since 3.6.1
 */
class Wpengine implements Subscriber_Interface {
	/**
	 * Array of events this subscriber wants to listen to.
	 *
	 * @since 3.6.1
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if (
			! (
				class_exists( 'WpeCommon' )
				&&
				function_exists( 'wpe_param' )
			)
		) {
			return [];
		}

		return [
			'rocket_varnish_field_settings'           => 'varnish_field',
			'rocket_display_input_varnish_auto_purge' => 'return_false',
			'rocket_cache_mandatory_cookies'          => [ 'return_empty_array', PHP_INT_MAX ],
			'rocket_advanced_cache_file',
			'return_empty_string',
			'admin_init'                              => [
				'remove_notices',
				'run_rocket_bot_after_wpengine',
			],
			'set_rocket_wp_cache_define'              => 'return_true',
			'do_rocket_generate_caching_files'        => 'return_false',
			'after_rocket_clean_domain',
			'clean_wpengine',
			'rocket_buffer'                           => [ 'add_footprint', 50 ],
			'rocket_disable_htaccess'                 => 'disable_htaccess',
		];
	}

	/**
	 * Returns false
	 *
	 * @since 3.6.1
	 *
	 * @return bool
	 */
	public function return_false() {
		return false;
	}

	/**
	 * Returns true
	 *
	 * @since 3.6.1
	 *
	 * @return true
	 */
	public function return_true() {
		return true;
	}

	/**
	 * Returns Empty string.
	 *
	 * @since 3.6.1
	 *
	 * @return string Empty string
	 */
	public function return_empty_string() {
		return '';
	}

	/**
	 * Returns Empty Array.
	 *
	 * @since 3.6.1
	 *
	 * @return array Empty array.
	 */
	public function return_empty_array() {
		return [];
	}

	/**
	 * Changes the text on the Varnish one-click block.
	 *
	 * @since 3.6.1
	 *
	 * @param array $settings Field settings data.
	 *
	 * @return array modified field settings data.
	 */
	public function varnish_field( $settings ) {
		$settings['varnish_auto_purge']['title'] = sprintf(
		// Translators: %s = Hosting name.
			__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
			'WP Engine'
		);

		return $settings;
	}

	/**
	 * Stop showing not valid notices with wpengine.
	 *
	 * @since 3.6.1
	 */
	public function remove_notices() {
		$container  = apply_filters( 'rocket_container', null );
		$subscriber = $container->get( 'admin_cache_subscriber' );

		remove_action( 'admin_notices', [ $subscriber, 'notice_advanced_cache_permissions' ] );
		remove_action( 'admin_notices', [ $subscriber, 'notice_advanced_cache_content_not_ours' ] );
	}

	/**
	 * Run WP Rocket preload bot after purged the Varnish cache via WP Engine Hosting.
	 *
	 * @since 3.6.1
	 */
	public function run_rocket_bot_after_wpengine() {
		if ( wpe_param( 'purge-all' ) && defined( 'PWP_NAME' ) && check_admin_referer( PWP_NAME . '-config' ) ) {
			// Preload cache.
			run_rocket_bot();
			run_rocket_sitemap_preload();
		}
	}

	/**
	 * Call the cache server to purge the cache with WP Engine hosting.
	 *
	 * @since 3.6.1
	 */
	public function clean_wpengine() {
		if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
			WpeCommon::purge_memcached();
		}

		if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
			WpeCommon::purge_varnish_cache();
		}
	}

	/**
	 * Add WP Rocket footprint on Buffer.
	 *
	 * @since 3.6.1
	 *
	 * @param string $buffer HTML content.
	 *
	 * @return string HTML with WP Rocket footprint.
	 */
	public function add_footprint( $buffer ) {
		if ( ! preg_match( '/<\/html>/i', $buffer ) ) {
			return $buffer;
		}

		$footprint  = defined( 'WP_ROCKET_WHITE_LABEL_FOOTPRINT' )
			? "\n" . '<!-- Optimized for great performance'
			: "\n" . '<!-- This website is like a Rocket, isn\'t it? Performance optimized by ' . WP_ROCKET_PLUGIN_NAME . '. Learn more: https://wp-rocket.me';
		$footprint .= ' -->';

		return $buffer . $footprint;
	}

	/**
	 * Disable dealing with htaccess if php version is 7.4 or above.
	 *
	 * @since 3.6.1
	 *
	 * @param bool $disable True to disable, false otherwise.
	 * @return bool
	 */
	public function disable_htaccess( $disable = false ) {
		// PHP version should be 7.4 or above.
		if ( version_compare( PHP_VERSION, '7.4' ) >= 0 ) {
			return true;
		}
		return (bool) $disable;
	}

}
