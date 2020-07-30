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

if ( ! function_exists( 'rocket_disable_emoji' ) ) {
	/**
	 * Disable the emoji functionality to reduce then number of external HTTP requests.
	 *
	 * @sicne 3.7 Deprecated.
	 * @since 2.7
	 *
	 * @deprecated
	 */
	function rocket_disable_emoji() {
		if ( rocket_bypass() ) {
			return;
		}

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );
	}
}

if ( ! function_exists( 'rocket_disable_emoji_tinymce' ) ) {
	/**
	 * Remove the tinymce emoji plugin.
	 *
	 * @since 3.7 Deprecated.
	 * @since 2.7
	 *
	 * @param array $plugins Plugins loaded for TinyMCE.
	 *
	 * @return array
	 *
	 * @deprecated
	 */
	function rocket_disable_emoji_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, [ 'wpemoji' ] );
		}

		return [];
	}
}
