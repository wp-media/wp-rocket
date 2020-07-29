<?php

defined( 'ABSPATH' ) || exit;

/**
 * Require deprecated classes.
 */
require_once __DIR__ . '/vendors/classes/class-minify-html.php';
require_once __DIR__ . '/subscriber/admin/Optimization/class-minify-html-subscriber.php';

class_alias('\WP_Rocket\Engine\Heartbeat\HeartbeatSubscriber', '\WP_Rocket\Subscriber\Heartbeat_Subscriber' );

/**
 * Conflict with WP Serveur hosting: don't apply inline JS on all pages.
 *
 * @since 3.7 deprecated
 * @since 2.6.11
 *
 * @param array $html_options WP Rocket options array.
 *
 * @return array Updated WP Rocket options array.
 */
function rocket_deactivate_inline_js_on_wp_serveur( $html_options ) {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	if ( isset( $html_options['jsMinifier'] ) ) {
		unset( $html_options['jsMinifier'] );
	}

	return $html_options;
}

/**
 * Conflict with AppBanners: don't minify inline script when HTML minification is activated
 *
 * @since 3.7 deprecated
 * @since 2.2.4
 *
 * @param array $html_options An array of WP Rocket options.
 * @return array Array without the inline js minify option
 */
function rocket_deactivate_js_minifier_with_appbanner( $html_options ) {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	if ( isset( $html_options['jsMinifier'] ) && class_exists( 'AppBanners' ) ) {
		unset( $html_options['jsMinifier'] );
	}

	return $html_options;
}

/**
 * Deactivate WP Rocket HTML Minification if Autoptimize HTML minification is enabled
 *
 * @since 3.7 deprecated
 * @since 2.9.5
 * @author Remy Perona
 *
 * @param string $old_value Previous autoptimize option value.
 * @param string $value New autoptimize option value.
 */
function rocket_maybe_deactivate_minify_html( $old_value, $value ) {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	if ( $value !== $old_value && 'on' === $value ) {
		update_rocket_option( 'minify_html', 0 );
	}
}

/**
 * Disable WP Rocket HTML minification field if Autoptimize HTML minification is enabled
 *
 * @since 3.7 deprecated
 * @since 2.9.5
 * @author Remy Perona
 *
 * @return bool|null True if it is active
 */
function rocket_maybe_disable_minify_html() {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	if ( is_plugin_active( 'autoptimize/autoptimize.php' ) && 'on' === get_option( 'autoptimize_html' ) ) {
		return true;
	}
}

/**
 * Conflict with Revolution Slider: don't minify inline script when HTML minification is activated
 *
 * @since 3.7 deprecated
 * @since 2.6.8
 *
 * @param array $html_options WP Rocket options array.
 * @return array Updated WP Rocket options
 */
function rocket_deactivate_js_minifier_with_revslider( $html_options ) {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	if ( isset( $html_options['jsMinifier'] ) && class_exists( 'RevSliderFront' ) ) {
		unset( $html_options['jsMinifier'] );
	}

	return $html_options;
}
