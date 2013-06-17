<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

/**
 * TO DO - Description
 *
 * since 1.0
 *
 */

add_filter( 'comment_cookie_lifetime', 'rocket_comment_cookie_lifetime' );
function comment_cookie_lifetime()
{
	return 300;
}