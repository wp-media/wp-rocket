<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( class_exists( 'FlywheelNginxCompat' ) ) :
	/**
	 * Changes the text on the Varnish one-click block.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $settings Field settings data.
	 */
	function rocket_flywheel_varnish_field( $settings ) {
		// Translators: %s = Hosting name.
		$settings['varnish_auto_purge']['title'] = sprintf( __( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ), 'Flywheel' );

		return $settings;
	}
	add_filter( 'rocket_varnish_field_settings', 'rocket_flywheel_varnish_field' );

	add_filter( 'rocket_display_input_varnish_auto_purge', '__return_false' );

	/**
	 * Allow to purge Varnish on Flywheel websites
	 *
	 * @since 2.6.8
	 */
	add_filter( 'do_rocket_varnish_http_purge', '__return_true' );

	/**
	 * Set up the right Varnish IP for Flywheel
	 *
	 * @since 2.6.8
	 */
	function rocket_varnish_ip_on_flywheel() {
		return '127.0.0.1';
	}
	add_filter( 'rocket_varnish_ip', 'rocket_varnish_ip_on_flywheel' );
endif;
