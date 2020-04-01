<?php

defined( 'ABSPATH' ) || exit;

/**
 * Changes the text on the Varnish one-click block.
 *
 * @since  3.0
 * @author Remy Perona
 *
 * @param array $settings Field settings data.
 *
 * @return array modified field settings data.
 */
function rocket_savvii_varnish_field( $settings ) {
	$settings['varnish_auto_purge']['title'] = sprintf(
		// Translators: %s = Hosting name.
		__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
		'Savvii'
	);

	return $settings;
}

add_filter( 'rocket_varnish_field_settings', 'rocket_savvii_varnish_field' );

add_filter( 'rocket_display_input_varnish_auto_purge', '__return_false' );
// Prevent mandatory cookies on hosting with server cache.
add_filter( 'rocket_cache_mandatory_cookies', '__return_empty_array', PHP_INT_MAX );

/**
 * Clear WP Rocket cache after purged the Varnish cache via Savvii Hosting.
 *
 * @since 2.6.5
 */
function rocket_clear_cache_after_savvii() {
	if (
		! defined( '\Savvii\CacheFlusherPlugin::NAME_FLUSH_NOW' )
		||
		! defined( '\Savvii\CacheFlusherPlugin::NAME_DOMAINFLUSH_NOW' )
		||
		! defined( '\Savvii\Options::CACHING_STYLE' )
	) {
		return false;
	}

	if (
		(
			isset( $_REQUEST[ \Savvii\CacheFlusherPlugin::NAME_FLUSH_NOW ], $_REQUEST['_wpnonce'] )
			&&
			wp_verify_nonce( $_REQUEST['_wpnonce'], \Savvii\Options::CACHING_STYLE ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		)
		||
		(
			isset( $_REQUEST[ \Savvii\CacheFlusherPlugin::NAME_DOMAINFLUSH_NOW ], $_REQUEST['_wpnonce'] )
			&&
			wp_verify_nonce( $_REQUEST['_wpnonce'], \Savvii\Options::CACHING_STYLE ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		)
	) {
		// Clear all caching files.
		rocket_clean_domain();

		// Preload cache.
		run_rocket_bot();
		run_rocket_sitemap_preload();
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
