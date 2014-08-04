<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/*
 * Add width and height attributes on all images
 *
 * @since 2.2.2 This feature is enabled by a hook
 * @since 1.3.0 This process is called via the new filter rocket_buffer
 * @since 1.3.0 It's possible to not specify dimensions of an image with data-no-image-dimensions attribute
 * @since 1.1.2 Fix Bug : No conflit with Photon Plugin (Jetpack)
 * @since 1.1.0
 */
add_filter( 'rocket_buffer', 'rocket_specify_image_dimensions', 10 );
function rocket_specify_image_dimensions( $buffer )
{
	/**
	 * Filter images dimensions attributes
	 *
	 * @since 2.2
	 *
	 * @param bool Do the job or not
	 */
	if ( ! apply_filters( 'rocket_specify_image_dimensions', false ) ) {
		return $buffer;
	}
	
	// Get all images without width or height attribute
	preg_match_all( '/<img(?:[^>](?!(height|width)=))*+>/i' , $buffer, $images_match );

	foreach ( $images_match[0] as $image ) {

		// Don't touch lazy-load file (no conflit with Photon (Jetpack))
		if ( strpos( $image, 'data-lazy-original' ) || strpos( $image, 'data-no-image-dimensions' ) ) {
			continue;
		}

		$tmp = $image;

		// Get link of the file
        preg_match( '/src=[\'"]([^\'"]+)/', $image, $src_match );

		// Get infos of the URL
		$image_url = parse_url( $src_match[1] );

		// Check if the link isn't external
		if ( empty( $image_url['host'] ) || $image_url['host'] == rocket_remove_url_protocol( home_url() ) ) {
			// Get image attributes
			$sizes = getimagesize( ABSPATH . $image_url['path'] );
		} else {
			/**
			 * Filter distant images dimensions attributes
			 *
			 * @since 2.2
			 *
			 * @param bool Do the job or not
			 */
			if ( ini_get('allow_url_fopen') && apply_filters( 'rocket_specify_image_dimensions_for_distant', false ) ) {
				// Get image attributes
				$sizes = getimagesize( $image_url['scheme'] . '://' . $image_url['host'] . $image_url['path'] );
			}
		}

		if ( ! empty( $sizes ) ) {
			// Add width and width attribute
			$image = str_replace( '<img', '<img ' . $sizes[3], $image );

			// Replace image with new attributes
	        $buffer = str_replace( $tmp, $image, $buffer );
		}
	}

	return $buffer;
}