<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

// TO DO - Modification du cookie des commentaires
add_filter( 'comment_cookie_lifetime', 'rocket_comment_cookie_lifetime' );
function comment_cookie_lifetime()
{
	return 300;
}