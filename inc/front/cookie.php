<?php
defined( 'ABSPATH' ) or	die( __( 'Cheatin&#8217; uh?', 'rocket' ) );

/**
 * By default, the lifetime of the cookie comment is one year.
 * Life is reduced to 3 minutes so that the visitor can enjoy the cached site.
 *
 * since 1.0
 *
 */

add_filter( 'comment_cookie_lifetime', 'rocket_comment_cookie_lifetime' );
function rocket_comment_cookie_lifetime()
{
	return 3 * MINUTE_IN_SECONDS;
}