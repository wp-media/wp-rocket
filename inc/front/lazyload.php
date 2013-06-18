<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Add Lazy Load JavaScript file in the footer
 * No jQuery or other library is required !!
 *
 * since 1.0
 *
 */

add_action( 'wp_enqueue_scripts', 'rocket_enqueue_lazyload' );
function rocket_enqueue_lazyload() {

	if( is_feed() || is_preview() )
		return false;

	wp_enqueue_script( 'rocket-lazyload', WP_ROCKET_FRONT_JS_URL . 'lazyload.js', array(), null, true );

}



/**
 * Replace Gravatar, thumbnails, images in post content and in widget text by Lazy Load
 *
 * since 0.1
 *
 */

add_filter( 'get_avatar', 'rocket_lazyload_images', PHP_INT_MAX );
add_filter( 'post_thumbnail_html', 'rocket_lazyload_images', PHP_INT_MAX );
add_filter( 'the_content', 'rocket_lazyload_images', PHP_INT_MAX );
add_filter( 'widget_text', 'rocket_lazyload_images', PHP_INT_MAX );
function rocket_lazyload_images( $html )
{
	if( is_feed() || is_preview() || empty( $html ) )
		return $html;

	$html = preg_replace( '#<img([^>]+?)src=[\'"]?([^\'"\s>]+)[\'"]?([^>]*)>#', '<img${1}src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-lazy-original="${2}"${3}><noscript><img${1}src="${2}"${3}></noscript>', $html );
	return $html;

}



/**
 * Replace WordPress smilies by Lazy Load
 *
 * since 0.1
 *
 */

add_filter('smilies_src', 'rocket_lazyload_smilies', PHP_INT_MAX );
function rocket_lazyload_smilies( $src )
{
	return "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==' data-lazy-original='" . $src;
}