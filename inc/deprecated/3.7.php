<?php

defined( 'ABSPATH' ) || exit;

/**
 * Require deprecated classes.
 */
if ( ! class_exists( 'Minify_HTML' ) ) {
	require_once __DIR__ . '/vendors/classes/class-minify-html.php';
}
if ( ! class_exists( 'WP_Rocket\Subscriber\Optimization\Minify_HTML_Subscriber' ) ) {
	require_once __DIR__ . '/subscriber/admin/Optimization/class-minify-html-subscriber.php';
}

class_alias( '\WP_Rocket\Engine\Heartbeat\HeartbeatSubscriber', '\WP_Rocket\Subscriber\Heartbeat_Subscriber' );

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
 *
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
 * @since  3.7 deprecated
 * @since  2.9.5
 *
 * @param string $old_value Previous autoptimize option value.
 * @param string $value     New autoptimize option value.
 *
 * @author Remy Perona
 *
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
 * @since  3.7 deprecated
 * @since  2.9.5
 * @return bool|null True if it is active
 * @author Remy Perona
 *
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
 *
 * @return array Updated WP Rocket options
 */
function rocket_deactivate_js_minifier_with_revslider( $html_options ) {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	if ( isset( $html_options['jsMinifier'] ) && class_exists( 'RevSliderFront' ) ) {
		unset( $html_options['jsMinifier'] );
	}

	return $html_options;
}

/**
 * Disable the emoji functionality to reduce then number of external HTTP requests.
 *
 * @since 3.7 Deprecated.
 * @since 2.7
 *
 * @deprecated
 */
function rocket_disable_emoji() {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );

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
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, [ 'wpemoji' ] );
	}

	return [];
}

/**
 * Disable embeds on init.
 *
 * - Removes the needed query vars.
 * - Disables oEmbed discovery.
 * - Completely removes the related JavaScript.
 *
 * @since 3.7 Deprecated.
 * @since 2.10
 *
 * @deprecated
 */
function rocket_disable_embeds_init() {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	if ( rocket_bypass() ) {
		return;
	}

	global $wp;

	// Remove the embed query var.
	$wp->public_query_vars = array_diff(
		$wp->public_query_vars,
		[
			'embed',
		]
	);

	// Remove the oembed/1.0/embed REST route.
	add_filter( 'rest_endpoints', 'rocket_disable_embeds_remove_embed_endpoint' );

	// Disable handling of internal embeds in oembed/1.0/proxy REST route.
	add_filter( 'oembed_response_data', 'rocket_disable_embeds_filter_oembed_response_data' );

	// Turn off oEmbed auto discovery.
	add_filter( 'embed_oembed_discover', '__return_false' );

	// Don't filter oEmbed results.
	remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

	// Remove oEmbed discovery links.
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

	// Remove oEmbed-specific JavaScript from the front-end and back-end.
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	add_filter( 'tiny_mce_plugins', 'rocket_disable_embeds_tiny_mce_plugin' );

	// Remove all embeds rewrite rules.
	add_filter( 'rewrite_rules_array', 'rocket_disable_embeds_rewrites' );

	// Remove filter of the oEmbed result before any HTTP requests are made.
	remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );

	// Load block editor JavaScript.
	add_action( 'enqueue_block_editor_assets', 'rocket_disable_embeds_enqueue_block_editor_assets' );

	// Remove wp-embed dependency of wp-edit-post script handle.
	add_action( 'wp_default_scripts', 'rocket_disable_embeds_remove_script_dependencies' );
}

/**
 * Removes the 'wpembed' TinyMCE plugin.
 *
 * @since  3.7 Deprecated.
 * @since  2.10
 *
 * @param array $plugins List of TinyMCE plugins.
 *
 * @return array The modified list.
 *
 * @deprecated
 */
function rocket_disable_embeds_tiny_mce_plugin( $plugins ) {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	return array_diff( $plugins, [ 'wpembed' ] );
}

/**
 * Remove all rewrite rules related to embeds.
 *
 * @since 3.7 Deprecated.
 * @since 2.10
 *
 * @param array $rules WordPress rewrite rules.
 *
 * @return array Rewrite rules without embeds rules.
 *
 * @deprecated
 */
function rocket_disable_embeds_rewrites( $rules ) {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	if ( empty( $rules ) ) {
		return $rules;
	}

	foreach ( $rules as $rule => $rewrite ) {
		if ( false !== strpos( $rewrite, 'embed=true' ) ) {
			unset( $rules[ $rule ] );
		}
	}

	return $rules;
}

/**
 * Removes the oembed/1.0/embed REST route.
 *
 * @since 3.6 Deprecated.
 * @since 3.3.3
 *
 * @param array $endpoints Registered REST API endpoints.
 *
 * @return array Filtered REST API endpoints.
 *
 * @deprecated
 */
function rocket_disable_embeds_remove_embed_endpoint( $endpoints ) {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	unset( $endpoints['/oembed/1.0/embed'] );

	return $endpoints;
}

/**
 * Disables sending internal oEmbed response data in proxy endpoint.
 *
 * @since 3.7 Deprecated.
 * @since 3.3.3
 *
 * @param array $data The response data.
 *
 * @return array|false Response data or false if in a REST API context.
 *
 * @deprecated
 */
function rocket_disable_embeds_filter_oembed_response_data( $data ) {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}

	return $data;
}

/**
 * Enqueues JavaScript for the block editor.
 *
 * This is used to unregister the `core-embed/wordpress` block type.
 *
 * @since 3.7 Deprecated.
 * @since 3.3.3
 *
 * @deprecated
 */
function rocket_disable_embeds_enqueue_block_editor_assets() {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	wp_enqueue_script(
		'rocket-disable-embeds',
		WP_ROCKET_ASSETS_JS_URL . 'editor/editor.js',
		[
			'wp-edit-post',
			'wp-editor',
			'wp-dom',
		],
		WP_ROCKET_VERSION,
		true
	);
}

/**
 * Removes wp-embed dependency of core packages.
 *
 * @since 3.7 deprecated
 * @since 3.3.3
 *
 * @param \WP_Scripts $scripts WP_Scripts instance, passed by reference.
 *
 * @deprecated
 */
function rocket_disable_embeds_remove_script_dependencies( $scripts ) {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	if ( ! empty( $scripts->registered['wp-edit-post'] ) ) {
		$scripts->registered['wp-edit-post']->deps = array_diff(
			$scripts->registered['wp-edit-post']->deps,
			[ 'wp-embed' ]
		);
	}
}
