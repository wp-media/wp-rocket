<?php

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Admin\Options_Data;

class Data {
	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Array of WP Rocket options to send
	 *
	 * @var array
	 */
	private $to_send = [
		'cache_mobile'            => 'Mobile Cache',
		'do_caching_mobile_files' => 'Specific Cache for Mobile',
		'cache_logged_user'       => 'User Cache',
		'emoji'                   => 'Disable Emojis',
		'defer_all_js'            => 'Defer JS',
		'delay_js'                => 'Delay JS',
		'async_css'               => 'Load CSS asynchronously',
		'lazyload'                => 'Lazyload Images',
		'lazyload_css_bg_img'     => 'Lazyload CSS Background Images',
		'lazyload_iframes'        => 'Lazyload Iframes',
		'lazyload_youtube'        => 'Lazyload Youtube',
		'cache_webp'              => 'WebP Cache',
		'minify_css'              => 'Minify CSS',
		'remove_unused_css'       => 'Remove Unused CSS',
		'minify_js'               => 'Minify JS',
		'minify_concatenate_js'   => 'Combine JS',
		'minify_google_fonts'     => 'Combine Google Fonts',
		'manual_preload'          => 'Preload',
		'preload_links'           => 'Preload Links',
		'cdn'                     => 'CDN Enabled',
		'do_cloudflare'           => 'Cloudflare Enabled',
		'varnish_auto_purge'      => 'Varnish Purge Enabled',
		'control_heartbeat'       => 'Heartbeat Control',
		'sucury_waf_cache_sync'   => 'Sucuri Add-on',
	];

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Returns the data to populate the support information
	 *
	 * @since 3.7.5
	 *
	 * @return array
	 */
	public function get_support_data() {
		$active_options = array_intersect_key( $this->to_send, array_filter( $this->options->get_options() ) );

		return [
			'Website'                  => home_url(),
			'WordPress Version'        => get_bloginfo( 'version' ),
			'WP Rocket Version'        => rocket_get_constant( 'WP_ROCKET_VERSION' ),
			'Theme'                    => wp_get_theme()->get( 'Name' ),
			'Plugins Enabled'          => implode( ' - ', rocket_get_active_plugins() ),
			'WP Rocket Active Options' => implode( ' - ', $active_options ),
		];
	}
}
