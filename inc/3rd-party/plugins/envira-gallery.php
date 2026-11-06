<?php

defined( 'ABSPATH' ) || exit;

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
	$images = str_replace( '<img', '<img data-no-lazy="1" ', $images );

	return $images;
}
add_filter( 'envira_gallery_indexable_images', 'rocket_deactivate_lazyload_on_envira_gallery_indexable_images', PHP_INT_MAX );
