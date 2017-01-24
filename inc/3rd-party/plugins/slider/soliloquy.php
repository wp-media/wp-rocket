<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with Soliloquy: don't apply LazyLoad on all images
 *
 * @since 2.4.2
 */
add_filter( 'soliloquy_output_image_attr', '__deactivate_rocket_lazyload_on_soliloquy', PHP_INT_MAX );
function __deactivate_rocket_lazyload_on_soliloquy( $attr ) {
	return $attr . ' data-no-lazy="1" ';
}

add_filter( 'soliloquy_indexable_images', '__deactivate_rocket_lazyload_on_soliloquy_indexable_images', PHP_INT_MAX );
function __deactivate_rocket_lazyload_on_soliloquy_indexable_images( $images ) {
	$images = str_replace( '<img' , '<img data-no-lazy="1" ', $images );
	
	return $images;
}