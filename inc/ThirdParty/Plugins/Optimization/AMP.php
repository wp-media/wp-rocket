<?php
namespace WP_Rocket\ThirdParty\Plugins\Optimization;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with AMP
 *
 * @since  3.5.2
 */
class AMP implements Subscriber_Interface {
	const QUERY       = 'amp';
	const AMP_OPTIONS = 'amp-options';

	/**
	 * WP Rocket CDN Subscriber.
	 *
	 * @var Subscriber_Interface
	 */
	private $cdn_subscriber;

	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Options_Data         $options        WP Rocket Options instance.
	 * @param Subscriber_Interface $cdn_subscriber WP Rocket CDN Subscriber.
	 */
	public function __construct( Options_Data $options, Subscriber_Interface $cdn_subscriber ) {
		$this->options        = $options;
		$this->cdn_subscriber = $cdn_subscriber;
	}

	/**
	 * Subscribed events for AMP.
	 *
	 * @since  3.5.2
	 */
	public static function get_subscribed_events() {
		$events = [
			'activate_amp/amp.php'   => 'generate_config_file',
			'deactivate_amp/amp.php' => 'generate_config_file',
			'wp'                     => 'disable_options_on_amp',
		];
		if ( function_exists( 'is_amp_endpoint' ) ) {
			$events['rocket_cache_query_strings'] = 'is_amp_compatible_callback';
			$events['update_option_amp-options']  = 'generate_config_file';
		}

		return $events;
	}

	/**
	 * Regenerate config file on plugin activation / deactivation.
	 *
	 * @since  3.5.2
	 */
	public function generate_config_file() {
		rocket_generate_config_file();
	}

	/**
	 * Add compatibility with AMP query string by adding it as a cached query string.
	 *
	 * @since  3.5.2
	 *
	 * @param  array $value WP Rocket cache_query_strings value.
	 * @return array
	 */
	public function is_amp_compatible_callback( $value ) {
		$options       = get_option( self::AMP_OPTIONS, [] );
		$query_strings = array_diff( $value, [ static::QUERY ] );

		if ( empty( $options['theme_support'] ) ) {
			return $query_strings;
		}

		if ( in_array( $options['theme_support'], [ 'transitional', 'reader' ], true ) ) {
			$query_strings[] = static::QUERY;
		}

		return $query_strings;
	}

	/**
	 * Removes Minification, DNS Prefetch, LazyLoad, Defer JS when on an AMP document.
	 *
	 * This covers AMP documents as output by the official AMP plugin for WordPress
	 * (https://amp-wp.org/) as well as Web Stories for WordPress (https://wp.stories.google/),
	 * which both support the `is_amp_endpoint` function checks.
	 *
	 * However, in the case of Web Stories, `is_amp_endpoint` is only defined on
	 * the `wp` action, not earlier. Hence doing the `function_exists` check at this stage
	 * instead of in the `get_subscribed_events()` method.
	 *
	 * @since  3.5.2
	 */
	public function disable_options_on_amp() {
		if ( ! function_exists( 'is_amp_endpoint' ) || ! is_amp_endpoint() ) {
			return;
		}

		global $wp_filter;

		remove_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 );
		add_filter( 'do_rocket_lazyload', '__return_false' );
		add_filter( 'do_rocket_lazyload_iframes', '__return_false' );
		unset( $wp_filter['rocket_buffer'] );

		$options = get_option( self::AMP_OPTIONS, [] );

		if ( ! empty( $options['theme_support'] )
			&&
			in_array( $options['theme_support'], [ 'transitional', 'reader' ], true ) ) {
			add_filter( 'rocket_cdn_reject_files', [ $this, 'reject_files' ], PHP_INT_MAX );
			add_filter( 'rocket_buffer', [ $this->cdn_subscriber, 'rewrite' ] );
			add_filter( 'rocket_buffer', [ $this->cdn_subscriber, 'rewrite_srcset' ] );
		}

		if (
			(bool) $this->options->get( 'do_cloudflare', 0 )
			&&
			(
				(bool) $this->options->get( 'cloudflare_protocol_rewrite', 0 )
				||
				// this filter is documented in inc/front/protocol.php.
				(bool) apply_filters( 'do_rocket_protocol_rewrite', false ) // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			)
		) {
			remove_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX );
		}
	}

	/**
	 * Adds all CSS and JS files to the list of excluded CDN files.
	 *
	 * @since 3.5.5
	 *
	 * @param  array $files List of excluded files.
	 * @return array        List of excluded files.
	 */
	public function reject_files( $files ) {
		return array_merge(
			$files,
			[
				'(.*).css',
				'(.*).js',
			]
		);
	}
}
