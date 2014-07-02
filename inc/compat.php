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