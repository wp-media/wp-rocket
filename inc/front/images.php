<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/*
 * Add width and height attributes on all images
 *
 * @since 1.3.0 This process is called via the new filter rocket_buffer
 * @since 1.3.0 It's possible to not specify dimensions of an image with data-no-image-dimensions attribute
 * @since 1.1.2 Fix Bug : No conflit with Photon Plugin (Jetpack)
 * @since 1.1.0
 *
 */

add_filter( 'rocket_buffer', 'rocket_specify_image_dimensions', 10 );
function rocket_specify_image_dimensions( $buffer )
{

	// Get all images without width or height attribute
	preg_match_all( '/<img(?:[^>](?!(height|width)=))*+>/i' , $buffer, $image_match );

	foreach( $image_match[0] as $image )
	{

		// Don't touch lazy-load file (no conflit with Photon (Jetpack))
		if ( strpos( $image, 'data-lazy-original' ) || strpos( $image, 'data-no-image-dimensions' ) )
			continue;
			
		$tmp = $image;

		// Get link of the file
        preg_match( '/src=[\'"]([^\'"]+)/', $image, $src_match );
		
		// Check if the link isn't external
		$image_url = parse_url( $src_match[1] );
	
		if( empty( $image_url['host'] ) || $image_url['host'] == rocket_remove_url_protocol( home_url() ) )
		{
			
			// Get image attributes
			$sizes = getimagesize( ABSPATH . $image_url['path'] );

			// Add width and width attribute
			$image = str_replace( '<img', '<img ' . $sizes[3], $image );

			// Replace image with new attributes
	        $buffer = str_replace( $tmp, $image, $buffer );

		}

	}

	return $buffer;
}