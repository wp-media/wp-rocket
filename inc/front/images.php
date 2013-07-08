<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/*
 * Add width and height attributes on all images
 *
 * since 1.1.2 Fix Bug : No conflit with Photon Plugin (Jetpack)
 * since 1.1.0
 *
 */

function rocket_specify_image_dimensions( $buffer )
{

	// Get all images without width or height attribute
	preg_match_all( '/<img(?:[^>](?!(height|width)=))*+>/i' , $buffer, $image_match );

	foreach( $image_match[0] as $image )
	{

		// Don't touch lazy-load file (no conflit with Photon (Jetpack))
		if ( strpos( $image, 'data-lazy-original' ) )
			continue;
		
		$tmp = $image;

		// Get link of the file
        preg_match( '/src=[\'"]([^\'"]+)/', $image, $src_match );

		// Get relative src
        $src = str_replace( home_url( '/' ), '', $src_match[1] );

		// Check if the link isn't external
		if( substr( $src, 0, 4 ) != 'http' )
		{
			
			// Get image attributes
			$sizes = getimagesize( str_replace( home_url(), ABSPATH, $src_match[1] ) );

			// Add width and width attribute
			$image = str_replace( '<img', '<img ' . $sizes[3], $image );

			// Replace image with new attributes
	        $buffer = str_replace( $tmp, $image, $buffer );

		}

	}

	return $buffer;
}

// COMING SOON - CDN Fonctionnality
//add_filter( 'wp_get_attachment_image_attributes', 'rocket_cdn_thumbnail_src' );
function rocket_cdn_thumbnail_src( $attr )
{
	
	$image_url_parts = parse_url( $attr['src'] );
	$image_host_path = $image_url_parts['host'] . $image_url_parts['path'];
	$subdomain = (abs(crc32($image_host_path)) % 3 + 1);
	$attr['src']  = "http://i{$subdomain}.wp-rocket.me/$image_host_path";
	
	return $attr;
}