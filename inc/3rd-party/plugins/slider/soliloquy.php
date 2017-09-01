<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with Soliloquy: don't apply LazyLoad on all images
 *
 * @since 2.4.2
 *
 * @param string $attr Image attributes.
 * @return string Updated attributes
 */
function rocket_deactivate_lazyload_on_soliloquy( $attr ) {
	return $attr . ' data-no-lazy="1" ';
}
add_filter( 'soliloquy_output_image_attr', 'rocket_deactivate_lazyload_on_soliloquy', PHP_INT_MAX );

/**
 * Conflict with Soliloquy: don't apply LazyLoad on all images
 *
 * @since 2.4.2
 *
 * @param string $images Image HTML code.
 * @return string Updated image HTML code
 */
function rocket_deactivate_lazyload_on_soliloquy_indexable_images( $images ) {
	$images = str_replace( '<img' , '<img data-no-lazy="1" ', $images );

	return $images;
}
add_filter( 'soliloquy_indexable_images', 'rocket_deactivate_lazyload_on_soliloquy_indexable_images', PHP_INT_MAX );
