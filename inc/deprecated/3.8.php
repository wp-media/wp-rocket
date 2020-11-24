<?php

defined( 'ABSPATH' ) || exit;

class_alias( '\WP_Rocket\Engine\Cache\PurgeExpired\PurgeExpiredCache', '\WP_Rocket\Cache\Expired_Cache_Purge');
class_alias( '\WP_Rocket\Engine\Cache\PurgeExpired\Subscriber', '\WP_Rocket\Subscriber\Cache\Expired_Cache_Purge_Subscriber');

/**
 * Defer all JS files.
 *
 * @since 3.8 deprecated
 * @since 2.10
 * @author Remy Perona
 *
 * @param string $buffer HTML content.
 * @return string Updated HTML content
 */
function rocket_defer_js( $buffer ) {
	_deprecated_function( __FUNCTION__ . '()', '3.8', 'WP_Rocket\Engine\Optimization\DeferJS\DeferJS::defer_js()' );

	if ( ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) || ( defined( 'DONOTASYNCCSS' ) && DONOTASYNCCSS ) ) {
		return;
	}

	if ( ! get_rocket_option( 'defer_all_js' ) ) {
		return $buffer;
	}

	if ( is_rocket_post_excluded_option( 'defer_all_js' ) ) {
		return $buffer;
	}

	$buffer_nocomments = preg_replace( '/<!--(.*)-->/Uis', '', $buffer );
	// Get all JS files with this regex.
	preg_match_all( '#<script\s+([^>]+[\s\'"])?src\s*=\s*[\'"]\s*?([^\'"]+\.js(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>#iU', $buffer_nocomments, $tags_match );

	if ( ! isset( $tags_match[0] ) ) {
		return $buffer;
	}

	$exclude_defer_js = implode( '|', get_rocket_exclude_defer_js() );

	foreach ( $tags_match[0] as $i => $tag ) {
		// Check if this file should be deferred.
		if ( preg_match( '#(' . $exclude_defer_js . ')#i', $tags_match[2][ $i ] ) ) {
			continue;
		}

		// Don't add defer if already async.
		if ( false !== strpos( $tags_match[1][ $i ], 'async' ) || false !== strpos( $tags_match[3][ $i ], 'async' ) ) {
			continue;
		}

		// Don't add defer if already defer.
		if ( false !== strpos( $tags_match[1][ $i ], 'defer' ) || false !== strpos( $tags_match[3][ $i ], 'defer' ) ) {
			continue;
		}

		$deferred_tag = str_replace( '>', ' defer>', $tag );
		$buffer       = str_replace( $tag, $deferred_tag, $buffer );
	}

	return $buffer;
}

/**
 * Get list of JS files to be excluded from defer JS.
 *
 * @since 3.8 deprecated
 * @since 2.10
 * @author Remy Perona
 *
 * @return array An array of URLs for the JS files to be excluded.
 */
function get_rocket_exclude_defer_js() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	_deprecated_function( __FUNCTION__ . '()', '3.8', 'WP_Rocket\Engine\Optimization\DeferJS\DeferJS::get_excluded()' );

	$exclude_defer_js = [
		'gist.github.com',
		'content.jwplatform.com',
		'js.hsforms.net',
		'www.uplaunch.com',
		'google.com/recaptcha',
		'widget.reviews.co.uk',
		'verify.authorize.net/anetseal',
		'lib/admin/assets/lib/webfont/webfont.min.js',
		'app.mailerlite.com',
		'widget.reviews.io',
		'simplybook.(.*)/v2/widget/widget.js',
		'/wp-includes/js/dist/i18n.min.js',
		'/wp-content/plugins/wpfront-notification-bar/js/wpfront-notification-bar(.*).js',
		'/wp-content/plugins/oxygen/component-framework/vendor/aos/aos.js',
		'static.mailerlite.com/data/(.*).js',
		'cdn.voxpow.com/static/libs/v1/(.*).js',
		'cdn.voxpow.com/media/trackers/js/(.*).js',
	];

	if ( get_rocket_option( 'defer_all_js', 0 ) && get_rocket_option( 'defer_all_js_safe', 0 ) ) {
		$jquery            = site_url( wp_scripts()->registered['jquery-core']->src );
		$jetpack_jquery    = 'c0.wp.com/c/(?:.+)/wp-includes/js/jquery/jquery.js';
		$googleapis_jquery = 'ajax.googleapis.com/ajax/libs/jquery/(?:.+)/jquery(?:\.min)?.js';
		$cdnjs_jquery      = 'cdnjs.cloudflare.com/ajax/libs/jquery/(?:.+)/jquery(?:\.min)?.js';
		$code_jquery       = 'code.jquery.com/jquery-.*(?:\.min|slim)?.js';

		$exclude_defer_js[] = rocket_clean_exclude_file( $jquery );
		$exclude_defer_js[] = $jetpack_jquery;
		$exclude_defer_js[] = $googleapis_jquery;
		$exclude_defer_js[] = $cdnjs_jquery;
		$exclude_defer_js[] = $code_jquery;
	}

	/**
	 * Filter list of Deferred JavaScript files
	 *
	 * @since 2.10
	 * @author Remy Perona
	 *
	 * @param array $exclude_defer_js An array of URLs for the JS files to be excluded.
	 */
	$exclude_defer_js = apply_filters( 'rocket_exclude_defer_js', $exclude_defer_js );

	foreach ( $exclude_defer_js as $i => $exclude ) {
		$exclude_defer_js[ $i ] = str_replace( '#', '\#', $exclude );
	}

	return $exclude_defer_js;
}

/**
 * Add width and height attributes on all images
 *
 * @since 2.2.2 This feature is enabled by a hook
 * @since 1.3.0 This process is called via the new filter rocket_buffer
 * @since 1.3.0 It's possible to not specify dimensions of an image with data-no-image-dimensions attribute
 * @since 1.1.2 Fix Bug : No conflit with Photon Plugin (Jetpack)
 * @since 1.1.0
 *
 * @param string $buffer HTML content.
 * @return string Modified HTML content
 */
function rocket_specify_image_dimensions( $buffer ) {
	_deprecated_function( __FUNCTION__ . '()', '3.8', 'WP_Rocket\Engine\Media\ImagesSubscriber::specify_image_dimensions()' );
	/**
	 * Filter images dimensions attributes
	 *
	 * @since 2.2
	 *
	 * @param bool Do the job or not.
	 */
	if ( ! apply_filters( 'rocket_specify_image_dimensions', false ) ) {
		return $buffer;
	}

	// Get all images without width or height attribute.
	preg_match_all( '/<img(?:[^>](?!(height|width)=))*+>/i', $buffer, $images_match );

	foreach ( $images_match[0] as $image ) {

		// Don't touch lazy-load file (no conflit with Photon (Jetpack)).
		if ( strpos( $image, 'data-lazy-original' ) || strpos( $image, 'data-no-image-dimensions' ) ) {
			continue;
		}

		$tmp = $image;

		// Get link of the file.
		preg_match( '/src=[\'"]([^\'"]+)/', $image, $src_match );

		// Get infos of the URL.
		$image_url = wp_parse_url( $src_match[1] );

		// Check if the link isn't external.
		if ( empty( $image_url['host'] ) || rocket_remove_url_protocol( home_url() ) === $image_url['host'] ) {
			// Get image attributes.
			$sizes = getimagesize( ABSPATH . $image_url['path'] );
		} else {
			/**
			 * Filter distant images dimensions attributes
			 *
			 * @since 2.2
			 *
			 * @param bool Do the job or not
			 */
			if ( ini_get( 'allow_url_fopen' ) && apply_filters( 'rocket_specify_image_dimensions_for_distant', false ) ) {
				// Get image attributes.
				$sizes = getimagesize( $image_url['scheme'] . '://' . $image_url['host'] . $image_url['path'] );
			}
		}

		if ( ! empty( $sizes ) ) {
			// Add width and width attribute.
			$image = str_replace( '<img', '<img ' . $sizes[3], $image );

			// Replace image with new attributes.
			$buffer = str_replace( $tmp, $image, $buffer );
		}
	}

	return $buffer;
}

/**
 * Conflict with LayerSlider: don't add width and height attributes on all images
 *
 * @since 2.1
 */
function rocket_deactivate_specify_image_dimensions_with_layerslider() {
	_deprecated_function( __FUNCTION__ . '()', '3.8', 'WP_Rocket\ThirdParty\Plugins\Slider\LayerSlider::get_subscribed_events()' );
	remove_filter( 'rocket_buffer', 'rocket_specify_image_dimensions' );
}
