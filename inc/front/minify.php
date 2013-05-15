<?php

/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function rocket_minyfy_css( $data, $paths )  {

	$internal_css = array();


	// Get all css files with this regex
	preg_match_all( '/<link.+href=.+(\.css).+>/i', $data, $link_tags_match );


    foreach ( $link_tags_match[0] as $link_tag ) {

        // Check css media type
        if ( !strpos( strtolower( $link_tag ), 'media=' )
        	|| preg_match('/media=["\'](?:["\']|[^"\']*?(all|screen)[^"\']*?["\'])/', $link_tag )
        ) {


	        // Get link of the file
			preg_match( '/href=[\'"]([^\'"]+)/', $link_tag, $href_match );


			if ( $href_match[1] ) {


				// Get relative url
				$url = str_replace( 'http://' . $_SERVER['HTTP_HOST'], '', $href_match[1] );


				// Check if the link isn't external
				if( substr( $url, 0, 4 ) != 'http' ) {


					// Delete the link tag
				    $data = str_replace( $link_tag, '', $data );


				    // Insert the relative path to the array without query string
				    $internal_css[] = ltrim( preg_replace( '#\?.*$#', '', $url ), '/' );

				}

			}

        }

    }

    if( count( $internal_css ) >= 1 ) {

	    // Get the minify Google code link
		$minify_css = $paths['WP_ROCKET_URL'] . '/min/f=' . implode( ',', $internal_css );


	    // Create the new unique css filename
	    $css_path = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . 'style' . time() . '.css';


	    // Create and save the minify file
	    file_put_contents( $paths['CACHE_DIR'] . '/' . $css_path, file_get_contents( $minify_css ) );


		// Insert the minify css file below <head>
		$data = preg_replace('/<head>/', '\\0<link rel="stylesheet" href="' . $paths['WP_ROCKET_CACHE_URL'] . $css_path . '" />', $data, 1);


    }

	return $data;

}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function rocket_minyfy_inline_css( $data, $paths ) {


	$inline_css = '';

    // Get all inline css with this regex
	preg_match_all( '#<style.+>(.*)</style>#isUe', $data, $style_tags_match );


	// Delete the style tags
	foreach( $style_tags_match[0] as $style_tag )
		$data = str_replace( $style_tag, '', $data );


	foreach( $style_tags_match[1] as $style_tag_content )
		// TO DO - Minification du code
    	$inline_css .= $style_tag_content;


    if( !empty( $inline_css ) ) {

	    // Create the new unique css filename
    	$inline_css_path = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . 'style-inline' . time() . '.css';


	 	// Create and save the minify file
	 	file_put_contents( $paths['CACHE_DIR'] . '/' . $inline_css_path, $inline_css );


	 	// Insert the minify css file below <head>
		$data = preg_replace('/<head>/', '\\0<link rel="stylesheet" href="' . $paths['WP_ROCKET_CACHE_URL'] . '/' . $inline_css_path . '" />', $data, 1);


    }

	return $data;

}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function rocket_minify_js( $data, $paths ) {

	$internal_js = array();

	preg_match_all( '/<script.+src=.+(\.js).+><\/script>/i', $data, $script_tags_match );

    foreach ( $script_tags_match[0] as $script_tag ) {

		preg_match('/src=[\'"]([^\'"]+)/', $script_tag, $href_match);

		if ( $href_match[1] ) {

			$url = str_replace( 'http://' . $_SERVER['HTTP_HOST'], '', $href_match[1]);

			if( substr($url, 0, 4 ) != 'http' ) {

			    $data = str_replace($script_tag, '', $data);
			    $internal_js[] = ltrim( preg_replace( '#\?.*$#', '', $url ), '/' );
			}

		}

    }

    $minify_js = 'http://' . $_SERVER['HTTP_HOST'] . '/wp-content/plugins/wp-rocket/min/f=' . implode(',', $internal_js);

	// Insert the minify css file
	$data = preg_replace('/<\/head>/', '<script src="'.$minify_js.'"></script>\\0', $data, 1);


}