<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

// WP <3.5 defines
if ( ! defined( 'SECOND_IN_SECONDS' ) ) {
    define( 'SECOND_IN_SECONDS', 1 );
}
if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
    define( 'MINUTE_IN_SECONDS', SECOND_IN_SECONDS*60 );
}
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
    define( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
}
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
    define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS);
}
if ( ! defined( 'WEEK_IN_SECONDS' ) ) {
    define( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
}
if ( ! defined( 'YEAR_IN_SECONDS' ) ) {
    define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );
}

/**
 // copied from core to reduct backcompat to 3.1 and not 3.5
 * Send a JSON response back to an Ajax request.
 *
 * @since WordPress 3.5.0
 *
 * @param mixed $response Variable (usually an array or object) to encode as JSON, then print and die.
 */
if ( ! function_exists( 'wp_send_json' ) ) {
	function wp_send_json( $response ) {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo json_encode( $response );
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_die();
		} else {
			die();
		}
	}
}

/**
 // copied from core to reduct backcompat to 3.1 and not 3.6
 * Remove slashes from a string or array of strings.
 *
 * This should be used to remove slashes from data passed to core API that
 * expects data to be unslashed.
 *
 * @since 3.6.0
 *
 * @param string|array $value String or array of strings to unslash.
 * @return string|array Unslashed $value
 */
if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		return stripslashes_deep( $value );
	}
}

/**
 * Copied from core for compatibility with WP < 4.6
 * Removed the error triggering because it relies on another WP 4.6 function
 *
 * Fires functions attached to a deprecated filter hook.
 *
 * When a filter hook is deprecated, the apply_filters() call is replaced with
 * apply_filters_deprecated(), which triggers a deprecation notice and then fires
 * the original filter hook.
 *
 * @since 4.6.0
 *
 * @see _deprecated_hook()
 *
 * @param string $tag         The name of the filter hook.
 * @param array  $args        Array of additional function arguments to be passed to apply_filters().
 * @param string $version     The version of WordPress that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used.
 * @param string $message     Optional. A message regarding the change.
 */
if ( ! function_exists( 'apply_filters_deprecated' ) ) {
    function apply_filters_deprecated( $tag, $args, $version, $replacement = false, $message = null ) {
	    if ( ! has_filter( $tag ) ) {
	    	return $args[0];
	    }
        
	    return apply_filters_ref_array( $tag, $args );
    }
}