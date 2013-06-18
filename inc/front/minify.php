<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function rocket_minify_process( $buffer ) 
{

	$options = get_option( 'wp_rocket_settings' );
	$enable_js = isset( $options['minify_js'] ) && $options['minify_js'] == '1' ? true : false;
	$enable_css = isset( $options['minify_css'] ) && $options['minify_css'] == '1' ? true : false;

	if( $enable_css || $enable_js )
	{

		list( $buffer, $conditionals ) = rocket_extract_ie_conditionals( $buffer );


	    // Minify JavaScript
	    if( $enable_js )
	    	$buffer = rocket_minify_js( $buffer );

	    // Minify CSS
	    if( $enable_css )
	    	$buffer = rocket_minify_css( $buffer );


	    $buffer = rocket_inject_ie_conditionals( $buffer, $conditionals );

	}

	// Minify HTML
    require( WP_ROCKET_PATH . 'min/lib/Minify/HTML.php' );
	return Minify_HTML::minify( $buffer );

}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
 
function rocket_minify_css( $buffer ) 
{

	$options = get_option( 'wp_rocket_settings' );

    $internals_css = array();
    $externals_css = array();

    // Get all css files with this regex
    preg_match_all( '/<link.+href=.+(\.css).+>/i', $buffer, $link_tags_match );

    foreach ( $link_tags_match[0] as $link_tag ) {

        // Check css media type
        if ( !strpos( strtolower( $link_tag ), 'media=' )
                || preg_match('/media=["\'](?:["\']|[^"\']*?(all|screen)[^"\']*?["\'])/', $link_tag )
        ) {


            // Get link of the file
            preg_match( '/href=[\'"]([^\'"]+)/', $link_tag, $href_match );


            // Get relative url
            $url = str_replace( 'http://' . $_SERVER['HTTP_HOST'] . '/', '', $href_match[1] );
			$url = preg_replace( '#\?.*$#', '', $url );

            // Check if the link isn't external
            // Insert the relative path to the array without query string
			substr( $url, 0, 4 ) != 'http' && !in_array( $url, $options['exclude_css'] )
	            ? $internals_css[] = $url
				: $externals_css[] = $link_tag;


            // Delete the link tag
            $buffer = str_replace( $link_tag, '', $buffer );

        }

    }
	
	// Get the internal CSS Files
	// To avoid conflicts with file URLs are too long for browsers, 
	// cut into several parts concatenated files
	$internals_link_tags = '';
	if( count( $internals_css )>=1 ) 
		foreach( array_chunk( $internals_css, apply_filters( 'rocket_chuck_minify_css_count', 5 ) ) as $css )
			$internals_link_tags .= '<link rel="stylesheet" href="' . WP_ROCKET_URL . 'min/f=' . implode( ',', $css ) . '" />';
    
	// Get all external link tags
	$externals_link_tags = count( $externals_css )>=1 ? implode( "\n" , $externals_css ) : '';

	// Insert the minify css file below <head>
	return preg_replace( '/<head(.*)>/', '<head$1>' . $externals_link_tags . $internals_link_tags, $buffer, 1 );

}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
 
function rocket_minify_js( $buffer ) 
{

	$options = get_option( 'wp_rocket_settings' );

    $internals_js = array();
    $externals_js = array();

    // Get all JS files with this regex
    preg_match_all( '/<script.+src=.+(\.js).+><\/script>/i', $buffer, $script_tags_match );

    foreach ( $script_tags_match[0] as $script_tag ) {

        preg_match('/src=[\'"]([^\'"]+)/', $script_tag, $src_match );


        // Get relative url
        $url = str_replace( 'http://' . $_SERVER['HTTP_HOST'] . '/', '', $src_match[1] );
		$url = preg_replace( '#\?.*$#', '', $url );


        // Check if the link isn't external
        // Insert the relative path to the array without query string
        substr( $url, 0, 4 ) != 'http' && !in_array( $url, $options['exclude_js'] )
            ? $internals_js[] = $url
			: $externals_js[] = $script_tag;


		// Delete the script tag
        $buffer = str_replace( $script_tag, '', $buffer );

    }
	
	// Get the internal JavaScript Files
	// To avoid conflicts with file URLs are too long for browsers, 
	// cut into several parts concatenated files
	$internals_script_tags = '';
	if( count( $internals_js )>=1 ) 
		foreach( array_chunk( $internals_js, apply_filters( 'rocket_chuck_minify_js_count', 5 ) ) as $js )
			$internals_script_tags .= '<script src="' . WP_ROCKET_URL . 'min/f=' . implode( ',', $js ) . '"></script>';
	
	// Get all external script tags
	$externals_script_tags = count( $externals_js )>=1 ? implode( "\n" , $externals_js ) : '';

    // Insert the minify JS file
    return preg_replace( '/<head(.*)>/', '<head$1>' . $externals_script_tags . $internals_script_tags, $buffer, 1 );
}



/**
 * TO DO - Description
 *
 * since 1.0
 * source : WP Minify
 *
 */

function rocket_extract_ie_conditionals( $buffer ) 
{

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
 
function rocket_inject_ie_conditionals( $buffer, $conditionals ) 
{

    while ( count( $conditionals ) > 0 && strpos( $buffer, '{{WP_ROCKET_CONDITIONAL}}' ) ) {
      $conditional = array_shift( $conditionals );
      $buffer = preg_replace( '/{{WP_ROCKET_CONDITIONAL}}/' , $conditional, $buffer, 1 );
    }

    return $buffer;
}