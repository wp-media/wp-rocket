<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

if ( class_exists( '\\Savvii\\CacheFlusherPlugin' ) & class_exists( '\\Savvii\\Options' ) ) :
	/**
	 * Don't display the Varnish options tab for Savvii users
	 *
	 * @since 2.7
	 */
	add_filter( 'rocket_display_varnish_options_tab', '__return_false' );

	/**
	 * Clear WP Rocket cache after purged the Varnish cache via Savvii Hosting
	 *
	 * @since 2.6.5
	 */
	function rocket_clear_cache_after_savvii() {
		if ( ! defined( '\Savvii\CacheFlusherPlugin::NAME_FLUSH_NOW' ) || ! defined( '\Savvii\CacheFlusherPlugin::NAME_DOMAINFLUSH_NOW' ) || ! defined( '\Savvii\Options::CACHING_STYLE' ) ) {
			return false;
		}

		if ( ( isset( $_REQUEST[ \Savvii\CacheFlusherPlugin::NAME_FLUSH_NOW ] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], \Savvii\Options::CACHING_STYLE ) ) || ( isset( $_REQUEST[ \Savvii\CacheFlusherPlugin::NAME_DOMAINFLUSH_NOW ] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], \Savvii\Options::CACHING_STYLE ) ) ) {
			// Clear all caching files.
			rocket_clean_domain();

			// Preload cache.
			run_rocket_preload_cache( 'cache-preload' );
		}
	}
	add_action( 'init', 'rocket_clear_cache_after_savvii' );

	/**
	 * Call the cache server to purge the cache with Savvii hosting.
	 *
	 * @since 2.6.5
	 */
	function rocket_clean_savvii() {
		$plugin = new \Savvii\CacheFlusherPlugin();

		if ( method_exists( $plugin, 'domainflush' ) ) {
			$plugin->domainflush();
		}
	}
	add_action( 'after_rocket_clean_domain', 'rocket_clean_savvii' );
endif;
