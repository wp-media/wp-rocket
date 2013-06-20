<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Launch WP Rocket minification process (CSS and JavaScript)
 *
 * since 1.0
 *
 */
 
function rocket_minify_process( $buffer )
{

	$options = get_option( 'wp_rocket_settings' );
	$enable_js = isset( $options['minify_js'] ) && $options['minify_js'] == '1';
	$enable_css = isset( $options['minify_css'] ) && $options['minify_css'] == '1';

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
 * Used to minify and concat CSS files
 *
 * since 1.0.2 Remove the filter, remove the array_chunk, add an automatic way to cut strings to 255c max
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
	$i=0;
	$internals_links = array($i=>'');
	$internals_link_tags = '';
	$_base = WP_ROCKET_MINIFY_URL;
	if( count( $internals_css ) ) {
		foreach( $internals_css as $css ){
			if( strlen( $internals_links[$i].$_base.$css )+1>=255 ) // +1 : we count the extra comma
				$i++;
			$internals_links[$i] .= $css.',';
		}
		foreach( $internals_links as $tags )
			$internals_link_tags .= '<link rel="stylesheet" href="' . $_base . rtrim( $tags, ',' ) . '" />'."\n";
	}
	// Get all external link tags
	$externals_link_tags = count( $externals_css ) ? implode( "\n" , $externals_css ) : '';

	// Insert the minify css file below <head>
	return preg_replace( '/<head(.*)>/', '<head$1>' . $externals_link_tags . $internals_link_tags, $buffer, 1 );

}



/**
 * Used to minify and concat JavaScript files
 *
 * since 1.0.2 Remove the filter, remove the array_chunk, add an automatic way to cut strings to 255c max
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
	$i=0;
	$internals_scripts = array($i=>'');
	$internals_script_tags = '';
	$_base = WP_ROCKET_MINIFY_URL;
	if( count( $internals_js ) ) {
		foreach( $internals_js as $js ){
			if( strlen( $internals_scripts[$i].$_base.$js )+1>=255 ) // +1 : we count the extra comma
				$i++;
			$internals_scripts[$i] .= $js.',';
		}
		foreach( $internals_scripts as $tags )
			$internals_script_tags .= '<script src="' . $_base . rtrim( $tags, ',' ) . '"></script>'."\n";
	}
	// Get all external script tags
	$externals_script_tags = count( $externals_js ) ? implode( "\n" , $externals_js ) : '';

    // Insert the minify JS file
    return preg_replace( '/<head(.*)>/', '<head$1>' . $externals_script_tags . $internals_script_tags, $buffer, 1 );
}



/**
 * Get all CSS ans JS files of IE conditionals tags
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
 * Replace WP Rocket IE conditionals tags
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