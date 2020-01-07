<?php

defined( 'ABSPATH' ) || exit;

/**
 * Conflict with LayerSlider: don't add width and height attributes on all images
 *
 * @since 2.1
 */
function rocket_deactivate_specify_image_dimensions_with_layerslider() {
	remove_filter( 'rocket_buffer', 'rocket_specify_image_dimensions' );
}
add_action( 'layerslider_ready', 'rocket_deactivate_specify_image_dimensions_with_layerslider' );
