<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with Envira Gallery: don't apply LazyLoad on all images
 *
 * @since 2.3.10
 *
 * @param string $attr Envira gallery image attributes.
 * @return string Updated attributes
 */
function rocket_deactivate_lazyload_on_envira_gallery( $attr ) {
	return $attr . ' data-no-lazy="1" ';
}
add_filter( 'envira_gallery_output_image_attr', 'rocket_deactivate_lazyload_on_envira_gallery', PHP_INT_MAX );

/**
 * Conflict with Envira Gallery: don't apply LazyLoad on all images
 *
 * @since 2.3.10
 *
 * @param string $images Envira gallery images HTML code.
 * @return string Updated HTML code
 */
function rocket_deactivate_lazyload_on_envira_gallery_indexable_images( $images ) {
	$images = str_replace( '<img' , '<img data-no-lazy="1" ', $images );

	return $images;
}
add_filter( 'envira_gallery_indexable_images', 'rocket_deactivate_lazyload_on_envira_gallery_indexable_images', PHP_INT_MAX );

/**
 * Conflict with Envira Gallery: changes the URL argument if using WP Rocket CDN and Envira
 *
 * @since 2.6.5
 *
 * @param array $args An array of arguments.
 * @return array Updated array of arguments
 */
function rocket_cdn_resize_image_args_on_envira_gallery( $args ) {
	if ( ! isset( $args['url'] ) || (int) get_rocket_option( 'cdn' ) === 0 ) {
		return $args;
	}

	$cnames_host = array_flip( get_rocket_cnames_host() );
	$url_host    = rocket_extract_url_component( $args['url'], PHP_URL_HOST );
	$home_host   = rocket_extract_url_component( home_url(), PHP_URL_HOST );

	if ( isset( $cnames_host[ $url_host ] ) ) {
		$args['url'] = str_replace( $url_host, $home_host , $args['url'] );
	}

	return $args;
}
add_filter( 'envira_gallery_resize_image_args', 'rocket_cdn_resize_image_args_on_envira_gallery' );

/**
 * Conflict with Envira Gallery: changes the resized URL if using WP Rocket CDN and Envira
 *
 * @since 2.6.5
 *
 * @param string $url Resized image URL.
 * @return string Resized image URL using the CDN URL
 */
function rocket_cdn_resized_url_on_envira_gallery( $url ) {
	if ( (int) get_rocket_option( 'cdn' ) === 0 ) {
		return $url;
	}

	$url = get_rocket_cdn_url( $url, array( 'all', 'images' ) );
	return $url;
}
add_filter( 'envira_gallery_resize_image_resized_url', 'rocket_cdn_resized_url_on_envira_gallery' );

