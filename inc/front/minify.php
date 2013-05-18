<?php

/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function rocket_minyfy_css( $buffer, $paths )  {

	$internal_css = array();


	// Get all css files with this regex
	preg_match_all( '/<link.+href=.+(\.css).+>/i', $buffer, $link_tags_match );


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
				    $buffer = str_replace( $link_tag, '', $buffer );


				    // Insert the relative path to the array without query string
				    $internal_css[] = ltrim( preg_replace( '#\?.*$#', '', $url ), '/' );

				}

			}

        }

    }

    if( count( $internal_css ) >= 1 ) {

	    // Get the minify Google code link
		$minify_css = $paths['WP_ROCKET_URL'] . 'min/f=' . implode( ',', $internal_css );


	    // Create the new unique css filename
	    $css_path = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . 'style' . time() . '.css';


	    // Create and save the minify file
	    file_put_contents( $paths['CACHE_DIR'] . $css_path, file_get_contents( $minify_css ) );



		// Insert the minify css file below <head>
		$buffer = preg_replace('/<head>/', '\\0<link rel="stylesheet" href="' . $paths['WP_ROCKET_CACHE_URL'] . $css_path . '" />', $buffer, 1);


    }

	return $buffer;

}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function rocket_minyfy_inline_css( $buffer, $paths ) {


	$inline_css = '';

    // Get all inline css with this regex
	preg_match_all( '#<style?.+>(.*)</style>#isUe', $buffer, $style_tags_match );


	// Delete the style tags
	foreach( $style_tags_match[0] as $style_tag )
		$buffer = str_replace( $style_tag, '', $buffer );


	foreach( $style_tags_match[1] as $style_tag_content )
    	$inline_css .= $style_tag_content;


    if( !empty( $inline_css ) ) {

	    // Create the new unique css filename
    	$inline_css_path = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . 'style-inline' . time() . '.css';


	 	require $paths['WP_ROCKET_PATH'] . 'min/lib/Minify/CSS/Compressor.php';
	 	require $paths['WP_ROCKET_PATH'] . 'min/lib/Minify/CSS/UriRewriter.php';


	 	// Minify the code
	 	$inline_css = Minify_CSS_Compressor::process( $inline_css );


	 	// Rewrite URI for background propriety
	 	$inline_css = Minify_CSS_UriRewriter::rewrite( $inline_css, $_SERVER['DOCUMENT_ROOT'] );


	 	// Save the minify file
	 	file_put_contents( $paths['CACHE_DIR'] . '/' . $inline_css_path, $inline_css );


	 	// Insert the minify css file below <head>
		$buffer = preg_replace('/<head>/', '\\0<link rel="stylesheet" href="' . $paths['WP_ROCKET_CACHE_URL'] . $inline_css_path . '" />', $buffer, 1);


    }

	return $buffer;

}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function rocket_minify_js( $buffer, $paths ) {

	$internal_js = array();

	preg_match_all( '/<script.+src=.+(\.js).+><\/script>/i', $buffer, $script_tags_match );

    foreach ( $script_tags_match[0] as $script_tag ) {

		preg_match('/src=[\'"]([^\'"]+)/', $script_tag, $href_match);

		if ( $href_match[1] ) {

			$url = str_replace( 'http://' . $_SERVER['HTTP_HOST'], '', $href_match[1]);

			if( substr($url, 0, 4 ) != 'http' ) {

			    $buffer = str_replace($script_tag, '', $buffer);
			    $internal_js[] = ltrim( preg_replace( '#\?.*$#', '', $url ), '/' );
			}

		}

    }

    $minify_js = 'http://' . $_SERVER['HTTP_HOST'] . '/wp-content/plugins/wp-rocket/min/f=' . implode( ',', $internal_js );

	// Insert the minify css file
	$buffer = preg_replace('/<\/head>/', '<script src="'. $minify_js .'"></script>\\0', $buffer, 1);


}



/**
 * TO DO - Description
 *
 * since 1.0
 * source : WP Minify
 *
 */
function rocket_extract_ie_conditionals( $buffer ) {
    
    preg_match_all('/<!--\[if[^\]]*?\]>.*?<!\[endif\]-->/is', $buffer, $conditionals_match );
    $buffer = preg_replace( '/<!--\[if[^\]]*?\]>.*?<!\[endif\]-->/is', '{{WP_ROCKET_CONDITIONAL}}', $buffer );

    $conditionals = array();
    foreach ($conditionals_match[0] as $conditional) {
      $conditionals[] = $conditional;
    }

    return array( $buffer, $conditionals );
}



/**
 * TO DO - Description
 *
 * since 1.0
 * source : WP Minify
 *
 */
function rocket_inject_ie_conditionals( $buffer, $conditionals ) {

    while ( count( $conditionals ) > 0 && strpos( $buffer, '{{WP_ROCKET_CONDITIONAL}}' ) ) {
      $conditional = array_shift( $conditionals );
      $buffer = preg_replace( '/{{WP_ROCKET_CONDITIONAL}}/' , $conditional, $buffer, 1 );
    }

    return $buffer;
}