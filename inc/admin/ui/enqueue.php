<?php

defined( 'ABSPATH' ) || exit;

/**
 * Add the CSS and JS files for WP Rocket options page
 *
 * @since 1.0.0
 */
function rocket_add_admin_css_js() {
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_enqueue_style( 'wpr-admin', WP_ROCKET_ASSETS_CSS_URL . 'wpr-admin' . $suffix . '.css', null, WP_ROCKET_VERSION );
	wp_enqueue_script( 'micromodal', WP_ROCKET_ASSETS_JS_URL . 'micromodal.min.js', null, '0.4.10', true );
	wp_enqueue_script( 'wpr-admin', WP_ROCKET_ASSETS_JS_URL . 'wpr-admin' . $suffix . '.js', [ 'micromodal' ], WP_ROCKET_VERSION, true );

	wp_localize_script(
		'wpr-admin',
		'rocket_ajax_data',
		/**
		 * Filters the data passed to the localize script function for WP Rocket admin JS
		 *
		 * @since 3.7.4
		 *
		 * @param array $data Localize script data.
		 */
		apply_filters(
			'rocket_localize_admin_script',
			[
				'nonce'      => wp_create_nonce( 'rocket-ajax' ),
				'origin_url' => 'https://api.wp-rocket.me',
			]
		)
	);

	if ( is_rtl() ) {
		wp_enqueue_style( 'wpr-admin-rtl', WP_ROCKET_ASSETS_CSS_URL . 'wpr-admin-rtl' . $suffix . '.css', null, WP_ROCKET_VERSION );
	}
}
add_action( 'admin_print_styles-settings_page_' . WP_ROCKET_PLUGIN_SLUG, 'rocket_add_admin_css_js' );

/**
 * Add the CSS and JS files needed by WP Rocket everywhere on admin pages
 *
 * @since 2.1
 */
function rocket_add_admin_css_js_everywhere() {
	wp_enqueue_script( 'wpr-admin-common', WP_ROCKET_ASSETS_JS_URL . 'wpr-admin-common.js', [ 'jquery' ], WP_ROCKET_VERSION, true );
	wp_enqueue_style( 'wpr-admin-common', WP_ROCKET_ASSETS_CSS_URL . 'wpr-admin-common.css', [], WP_ROCKET_VERSION );
}
add_action( 'admin_enqueue_scripts', 'rocket_add_admin_css_js_everywhere', 11 );

/**
 * Adds mixpanel JS code in header when analytics data should be sent
 *
 * @since 2.11
 * @deprecated 3.19.2 Use WP_Rocket\Engine\Tracking\Tracking class instead
 * @author Remy Perona
 */
function rocket_add_mixpanel_code() {
	_deprecated_function( __FUNCTION__, '3.19.2', 'WP_Rocket\Engine\Tracking\Tracking' );

	// This functionality has been moved to the new Tracking system.
	// The Tracking class now handles Mixpanel script injection via the
	// inject_mixpanel_script() method.
}
// Remove the action hook - the new Tracking system handles this.


/**
 * Add CSS & JS files for the Imagify installation call to action
 *
 * @since 2.7
 */
function rocket_enqueue_modal_plugin() {
	$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

	if ( defined( 'IMAGIFY_VERSION' ) || in_array( 'rocket_imagify_notice', (array) $boxes, true ) || 1 === get_option( 'wp_rocket_dismiss_imagify_notice' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_enqueue_style( 'plugin-install' );

	wp_enqueue_script( 'plugin-install' );
	wp_enqueue_script( 'updates' );
	add_thickbox();
}
add_action( 'admin_print_styles-media-new.php', 'rocket_enqueue_modal_plugin' );
add_action( 'admin_print_styles-upload.php', 'rocket_enqueue_modal_plugin' );
add_action( 'admin_print_styles-settings_page_' . WP_ROCKET_PLUGIN_SLUG, 'rocket_enqueue_modal_plugin' );
