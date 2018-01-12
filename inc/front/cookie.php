<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * By default, the lifetime of the cookie comment is one year.
 * Life is reduced to 3 minutes so that the visitor can enjoy the cached site.
 *
 * @since 1.0
 */
function rocket_comment_cookie_lifetime() {
	/**
	 * Filter the lifetime of the cookie comment
	 *
	 * @since 2.2
	 *
	 * @param int The lifetime of the cookie in seconds
	 */
	$cookie_lifetime = apply_filters( 'rocket_comment_cookie_lifetime', 3 * MINUTE_IN_SECONDS );

	return $cookie_lifetime;
}
add_filter( 'comment_cookie_lifetime', 'rocket_comment_cookie_lifetime' );
