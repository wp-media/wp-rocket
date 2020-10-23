<?php

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Admin\Options_Data;

class Data {
	private $options;
	private $to_send = [
		'cache_mobile'            => 'Mobile Cache',
		'do_caching_mobile_files' => 'Specific Cache for Mobile',
		'cache_logged_user'       => 'User Cache',
		'emoji'                   => 'Disable Emojis',
		'embeds'                  => 'Disable Embeds',
		'defer_all_js'            => 'Defer JS',
		'defer_all_js_safe'       => 'Defer JS Safe',
		'delay_js'                => 'Delay JS',
		'async_css'               => 'Optimize CSS Delivery',
		'lazyload'                => 'Lazyload Images',
		'lazyload_iframes'        => 'Lazyload Iframes',
		'lazyload_youtube'        => 'Lazyload Youtube',
		'cache_webp'              => 'WebP Cache',
		'minify_css'              => 'Minify CSS',
		'minify_concatenate_css'  => 'Combine CSS',
		'minify_js'               => 'Minify JS',
		'minify_concatenate_js'   => 'Combine JS',
		'minify_google_fonts'     => 'Combine Google Fonts',
		'manual_preload'          => 'Preload',
		'sitemap_preload'         => 'Sitemap Preload',
		'preload_links'           => 'Preload Links',
		'cdn'                     => 'CDN Enabled',
		'do_cloudflare'           => 'Cloudflare Enabled',
		'varnish_auto_purge'      => 'Varnish Purge Enabled',
		'google_analytics_cache'  => 'Google Tracking Add-on',
		'facebook_pixel_cache'    => 'Facebook Tracking Add-on',
		'control_heartbeat'       => 'Hearbeat Control',
		'sucury_waf_cache_sync'   => 'Sucuri Add-on',
	];

	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	public function get_support_data() {
		$active_options = array_filter( $this->options->get_options() );
		$active_options = array_intersect_key( $this->to_send, $active_options );

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
