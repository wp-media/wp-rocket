<?php

defined( 'ABSPATH' ) || exit;

/**
 * Conflict with Meta Slider (Nivo Slider): don't apply LazyLoad on all images
 *
 * @since 2.4
 *
 * @param array $slide Slide attributes.
 * @return array Updated slide attributes
 */
function rocket_deactivate_lazyload_on_metaslider( $slide ) {
	$slide['data-no-lazy'] = 1;
	return $slide;
}
add_filter( 'metaslider_nivo_slider_image_attributes', 'rocket_deactivate_lazyload_on_metaslider' );
