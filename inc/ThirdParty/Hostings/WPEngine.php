<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WpeCommon;

/**
 * Compatibility class for WP Engine.
 *
 * @since 3.6.1
 */
class WPEngine extends AbstractNoCacheHost {
	/**
	 * Array of events this subscriber wants to listen to.
	 *
	 * @since 3.6.1
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_varnish_field_settings'           => 'varnish_addon_title',
			'rocket_display_input_varnish_auto_purge' => 'return_false',
			'rocket_cache_mandatory_cookies'          => [ 'return_empty_array', PHP_INT_MAX ],
			'rocket_set_wp_cache_constant'            => 'return_false',
			'do_rocket_generate_caching_files'        => 'return_false',
			'rocket_after_clean_domain'               => 'clean_wpengine',
			'rocket_buffer'                           => [ 'add_footprint', 50 ],
			'rocket_disable_htaccess'                 => 'return_true',
			'rocket_generate_advanced_cache_file'     => 'return_false',
		];
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
	public function varnish_addon_title( $settings ) {
		$settings['varnish_auto_purge']['title'] = sprintf(
			// Translators: %s = Hosting name.
			__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
			'WP Engine'
		);

		return $settings;
	}

	/**
	 * Call the cache server to purge the cache with WP Engine hosting.
	 *
	 * @since 3.6.1
	 */
	public function clean_wpengine() {
		if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) { // @phpstan-ignore-line
			WpeCommon::purge_memcached();
		}

		if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) { // @phpstan-ignore-line
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

		$footprint  = rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_FOOTPRINT' )
			? "\n" . '<!-- Optimized for great performance'
			: "\n" . '<!-- This website is like a Rocket, isn\'t it? Performance optimized by ' . rocket_get_constant( 'WP_ROCKET_PLUGIN_NAME' ) . '. Learn more: https://wp-rocket.me';
		$footprint .= ' -->';

		return $buffer . $footprint;
	}
}
