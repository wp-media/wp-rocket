<?php

defined( 'ABSPATH' ) || exit;

class_alias( '\WP_Rocket\Engine\Cache\PurgeExpired\PurgeExpiredCache', '\WP_Rocket\Cache\Expired_Cache_Purge');
class_alias( '\WP_Rocket\Engine\Cache\PurgeExpired\Subscriber', '\WP_Rocket\Subscriber\Cache\Expired_Cache_Purge_Subscriber');
class_alias( '\WP_Rocket\Engine\Media\Lazyload\Subscriber', '\WP_Rocket\Engine\Media\LazyloadSubscriber');

if ( ! class_exists( 'WP_Rocket\Subscriber\Optimization\Dequeue_JQuery_Migrate_Subscriber' ) ) {
	require_once __DIR__ . '/subscriber/Optimization/class-dequeue-jquery-migrate-subscriber.php';
}

/**
 * Deactivate WP Rocket lazyload if Avada lazyload is enabled
 *
 * @since 3.8.1 deprecated
 * @since 3.3.4
 *
 *  @param string $old_value Previous Avada option value.
 * @param string $value New Avada option value.
 * @return void
 */
function rocket_avada_maybe_deactivate_lazyload( $old_value, $value ) {
	_deprecated_function( __FUNCTION__ . '()', '3.8.1', 'WP_Rocket\ThirdParty\Themes\Avada::maybe_deactivate_lazyload()' );

	if (
		empty( $old_value['lazy_load'] )
		||
		( ! empty( $value['lazy_load'] ) && 'avada' === $value['lazy_load'] )
	) {
		update_rocket_option( 'lazyload', 0 );
	}
}

/**
 * Disable WP Rocket lazyload field if Avada lazyload is enabled
 *
 * @since 3.8.1 deprecated
 * @since 3.3.4
 *
 * @return bool
 */
function rocket_avada_maybe_disable_lazyload() {
	_deprecated_function( __FUNCTION__ . '()', '3.8.1', 'WP_Rocket\ThirdParty\Themes\Avada::maybe_disable_lazyload()' );

	$avada_options = get_option( 'fusion_options' );
	$current_theme = wp_get_theme();

	if ( 'Avada' !== $current_theme->get( 'Name' ) ) {
		return false;
	}

	if ( empty( $avada_options['lazy_load'] ) ) {
		return false;
	}

	if ( ! empty( $avada_options['lazy_load'] && 'avada' !== $avada_options['lazy_load'] ) ) {
		return false;
	}

	return true;
}

/**
 * Clears WP Rocket's cache after Avada's Fusion Patcher flushes their caches
 *
 * @since 3.8.1 deprecated
 * @since 3.3.5
 *
 * @return void
 */
function rocket_avada_clear_cache_fusion_patcher() {
	_deprecated_function( __FUNCTION__ . '()', '3.8.1', 'WP_Rocket\ThirdParty\Themes\Avada::clear_cache_fusion_patcher()' );

	rocket_clean_domain();
}

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
 * @since 3.8 deprecated
 * @since 2.2.2 This feature is enabled by a hook
 * @since 1.3.0 This process is called via the new filter rocket_buffer
 * @since 1.3.0 It's possible to not specify dimensions of an image with data-no-image-dimensions attribute
 * @since 1.1.2 Fix Bug : No conflict with Photon Plugin (Jetpack)
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

		// Don't touch lazy-load file (no conflict with Photon (Jetpack)).
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
 * @since 3.8 deprecated
 * @since 2.1
 */
function rocket_deactivate_specify_image_dimensions_with_layerslider() {
	_deprecated_function( __FUNCTION__ . '()', '3.8', 'WP_Rocket\ThirdParty\Plugins\Slider\LayerSlider::get_subscribed_events()' );
	remove_filter( 'rocket_buffer', 'rocket_specify_image_dimensions' );
}

/**
 * Add age-verified to the list of mandatory cookies
 *
 * @since 3.8.6 deprecated
 * @since 2.7
 *
 * @param Array $cookies Array of mandatory cookies.
 * @return Array Updated array of mandatory cookies
 */
function rocket_add_cache_mandatory_cookie_for_age_verify( $cookies ) {
	_deprecated_function( __FUNCTION__ . '()', '3.8.6' );

	$cookies[] = 'age-verified';
	return $cookies;
}

/**
 * Add age-verified cookie when we activate the plugin
 *
 * @since 3.8.6 deprecated
 * @since 2.7
 */
function rocket_activate_age_verify() {
	_deprecated_function( __FUNCTION__ . '()', '3.8.6' );
	add_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 18 );
	add_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_cache_mandatory_cookie_for_age_verify' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}

/**
 * Remove age-verified cookie when we deactivate the plugin
 *
 * @since 3.8.6 deprecated
 * @since 2.7
 */
function rocket_deactivate_age_verify() {
	_deprecated_function( __FUNCTION__ . '()', '3.8.6' );
	remove_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_cache_mandatory_cookie_for_age_verify' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
