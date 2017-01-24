<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with Meta Slider (Nivo Slider): don't apply LazyLoad on all images
 *
 * @since 2.4
 */
add_filter( 'metaslider_nivo_slider_image_attributes', '__deactivate_rocket_lazyload_on_metaslider' );
function __deactivate_rocket_lazyload_on_metaslider( $slide ) {
	$slide['data-no-lazy'] = 1;
	return $slide;
}