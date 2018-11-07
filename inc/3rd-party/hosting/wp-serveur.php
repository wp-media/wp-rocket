<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( defined( 'DB_HOST' ) && strpos( DB_HOST, '.wpserveur.net' ) !== false ) {
	/**
	 * Allow to purge Varnish on WP Serveur websites
	 *
	 * @since 2.6.11
	 */
	add_filter( 'do_rocket_varnish_http_purge', '__return_true' );
	// Prevent mandatory cookies on hosting with server cache.
	add_filter( 'rocket_cache_mandatory_cookies', '__return_empty_array', PHP_INT_MAX );

	/**
	 * Changes the text on the Varnish one-click block.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $settings Field settings data.
	 */
	function rocket_wpserveur_varnish_field( $settings ) {
		// Translators: %s = Hosting name.
		$settings['varnish_auto_purge']['title'] = sprintf( __( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ), 'WP Serveur' );

		return $settings;
	}
	add_filter( 'rocket_varnish_field_settings', 'rocket_wpserveur_varnish_field' );

	add_filter( 'rocket_display_input_varnish_auto_purge', '__return_false' );

	/**
	 * Conflict with WP Serveur hosting: don't apply inline JS on all pages
	 *
	 * @since 2.6.11
	 *
	 * @param array $html_options WP Rocket options array.
	 * @return array Updated WP Rocket options array
	 */
	function rocket_deactivate_inline_js_on_wp_serveur( $html_options ) {
		if ( isset( $html_options['jsMinifier'] ) ) {
			unset( $html_options['jsMinifier'] );
		}
		return $html_options;
	}
	add_action( 'rocket_minify_html_options', 'rocket_deactivate_inline_js_on_wp_serveur' );
}
