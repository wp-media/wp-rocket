<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with Envira Gallery: don't apply LazyLoad on all images
 *
 * @since 2.3.10
 */
add_filter( 'envira_gallery_output_image_attr', '__deactivate_rocket_lazyload_on_envira_gallery', PHP_INT_MAX );
function __deactivate_rocket_lazyload_on_envira_gallery( $attr ) {
	return $attr . ' data-no-lazy="1" ';
}

add_filter( 'envira_gallery_indexable_images', '__deactivate_rocket_lazyload_on_envira_gallery_indexable_images', PHP_INT_MAX );
function __deactivate_rocket_lazyload_on_envira_gallery_indexable_images( $images ) {
	$images = str_replace( '<img' , '<img data-no-lazy="1" ', $images );
	
	return $images;
}

/**
* Conflict with Envira Gallery: changes the URL argument if using WP Rocket CDN and Envira
*
* @since 2.6.5
*/
add_filter( 'envira_gallery_resize_image_args', '__rocket_cdn_resize_image_args_on_envira_gallery' );
function __rocket_cdn_resize_image_args_on_envira_gallery( $args ) {
	if ( ! isset( $args['url'] ) || (int) get_rocket_option('cdn') == 0 ) {
		return $args;
	}
	
	$cnames_host = get_rocket_cnames_host();
    $url_host    = parse_url( $args['url'], PHP_URL_HOST );
    $home_host   = parse_url( home_url(), PHP_URL_HOST );
       
    if ( in_array( $url_host, $cnames_host ) ) {
    	$args['url'] = str_replace( $url_host, $home_host , $args['url'] );    
    }
    
    return $args;
}

/**
* Conflict with Envira Gallery: changes the resized URL if using WP Rocket CDN and Envira
*
* @since 2.6.5
*/
add_filter( 'envira_gallery_resize_image_resized_url', '__rocket_cdn_resized_url_on_envira_gallery' );
function __rocket_cdn_resized_url_on_envira_gallery( $url ) {
    if ( (int) get_rocket_option('cdn') == 0 ) {
		return $url;
	}
	
    $url = get_rocket_cdn_url( $url, array( 'all', 'images' ) );
    return $url;
}
