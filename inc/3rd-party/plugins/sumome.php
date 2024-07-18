<?php
/**
 * Compatibility with SumoMe
 *
 * Prevents conflict with SumoMe and the WP Rocket UI by removing SumoMe
 * styles and scripts on WP Rocket admin pages.
 *
 * @link https://wordpress.org/plugins/sumome/
 * @since 3.0.4
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_Plugin_SumoMe' ) ) {

	/**
	 * Dequeue SumoMe styles
	 *
	 * @since 3.0.4
	 * @author Arun Basil Lal
	 */
	function rocket_dequeue_sumo_me_css() {

		// Return on all pages but WP Rocket settings page.
		$screen = get_current_screen();
		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		wp_dequeue_style( 'sumome-admin-styles' );
		wp_dequeue_style( 'sumome-admin-media' );
	}
	add_action( 'admin_enqueue_scripts', 'rocket_dequeue_sumo_me_css', PHP_INT_MAX );

	/**
	 * Dequeue SumoMe inline script
	 *
	 * @since 3.0.4
	 * @author Arun Basil Lal
	 */
	function rocket_dequeue_sumo_me_js() {

		// Return on all pages but WP Rocket settings page.
		$screen = get_current_screen();
		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		global $wp_plugin_sumome;
		remove_action( 'admin_footer', [ $wp_plugin_sumome, 'append_admin_script_code' ] );
	}
	add_action( 'admin_head', 'rocket_dequeue_sumo_me_js' );
}
