<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );


/**
 * Launch WP Rocket minification process (CSS and JavaScript)
 *
 * @since 1.3.0 This process is called via the new filter rocket_buffer
 * @since 1.2.3 Add <!--[if IE]><![endif]--> in head to optimize IE conditionals tags
 * @since 1.1.6 Minify inline CSS and JavaScript
 * @since 1.0
 *
 */

add_filter( 'rocket_buffer', 'rocket_minify_process', 12 );
function rocket_minify_process( $buffer )
{

	$enable_js 		 = get_rocket_option( 'minify_js' );
	$enable_css 	 = get_rocket_option( 'minify_css' );
	$enable_html 	 = get_rocket_option( 'minify_html' );
	$all_link_tags 	 = '';
	$all_script_tags = '';

	if( $enable_css || $enable_js )
	{
		list( $buffer, $conditionals ) = rocket_extract_ie_conditionals( $buffer );

		// Minify CSS
	    if( $enable_css )
	    	list( $buffer, $all_link_tags ) = rocket_minify_css( $buffer );

	    // Minify JavaScript
	    if( $enable_js )
	    	list( $buffer, $all_script_tags ) = rocket_minify_js( $buffer );

	    $buffer = rocket_inject_ie_conditionals( $buffer, $conditionals );

	}

	// Insert all CSS and JS files in head
	$buffer = preg_replace( '/<head(.*)>/', '<head$1><!--[if IE]><![endif]-->' . $all_link_tags . $all_script_tags, $buffer, 1 );

	// Minify HTML
	if( $enable_html )
	    $buffer = rocket_minify_html( $buffer );

	return $buffer;
}



/**
 * Used for minify inline HTML
 *
 * @since 1.1.12
 *
 */

function rocket_minify_html( $buffer )
{

	// Check if Minify_HTML is enable
    if( !class_exists( 'Minify_HTML' ) )
    {

	    $html_options = array( 'ignoredComments' => array( 'google_ad_', 'RSPEAK_' ), 'stripCrlf' => true );
	    require( WP_ROCKET_PATH . 'min/lib/Minify/HTML.php' );


		// Check if Minify_CSS_Compressor is enable
		if( !class_exists( 'Minify_CSS_Compressor' ) )
		{
			require( WP_ROCKET_PATH . 'min/lib/Minify/CSS/Compressor.php' );
			$html_options['cssMinifier'] = 'rocket_minify_inline_css';
		}

		// Check if JSMin is enable
		if( !class_exists( 'JSMin' ) )
		{
			require( WP_ROCKET_PATH . 'min/lib/JSMin.php' );
			$html_options['jsMinifier'] = 	'rocket_minify_inline_js';
		}

		$html_options = apply_filters( 'rocket_minify_html_options', $html_options );
		$buffer = Minify_HTML::minify( $buffer, $html_options );
    }

    return $buffer;

}



/**
 * Used for minify inline CSS
 *
 * @since 1.1.6
 *
 */

function rocket_minify_inline_css( $css )
{
	return Minify_CSS_Compressor::process( $css );
}



/**
 * Used for minify inline JavaScript
 *
 * @since 1.1.6
 *
 */

function rocket_minify_inline_js( $js )
{
	return JSMin::minify( $js );
}



/**
 * Used to minify and concat CSS files
 *
 * @since 1.1.0 Fix Bug with externals URLs like //ajax.google.com
 * @since 1.0.2 Remove the filter, remove the array_chunk, add an automatic way to cut strings to 255c max
 * @since 1.0
 *
 */

function rocket_minify_css( $buffer )
{

    $internals_css = array();
    $externals_css = array();
    $excludes_css = array();
    $google_fonts = array();

    // Get all css files with this regex
    preg_match_all( '/<link.+href=.+(\.css).+>/iU', $buffer, $link_tags_match );

    foreach ( $link_tags_match[0] as $tag ) 
    {

        // Check css media type
        if ( !strpos( strtolower( $tag ), 'media=' )
             || preg_match('/media=["\'](?:["\']|[^"\']*?(all|screen)[^"\']*?["\'])/', $tag )
        ) {

            // Get link of the file
            preg_match( '/href=[\'"]([^\'"]+)/', $tag, $href_match );

            // Get URLs infos
			$css_url = parse_url( $href_match[1] );
			$home_url = parse_url( home_url() );

            // Check if the link isn't external
            // Insert the relative path to the array without query string
			if( $css_url['host'] == $home_url['host'] )
			{
				if( !in_array( $css_url['path'], get_rocket_option( 'exclude_css', array() ) ) && pathinfo( $css_url['path'], PATHINFO_EXTENSION ) == 'css' )
					$internals_css[] = $css_url['path'];
				else
					$excludes_css[] = $tag;
			}
			else
			{
				$externals_css[] = $tag;
			}

            // Delete the link tag
            $buffer = str_replace( $tag, '', $buffer );

        }

    }

	// Get the internal CSS Files
	// To avoid conflicts with file URLs are too long for browsers,
	// cut into several parts concatenated files
	$i=0;
	$internals_links = array($i=>'');
	$internals_link_tags = '';
	$_base = WP_ROCKET_URL . 'min/?f=';

	if( count( $internals_css ) )
	{
		foreach( $internals_css as $css )
		{
			if( strlen( $internals_links[$i].$_base.$css )+1>=255 ) // +1 : we count the extra comma
				$i++;
			$internals_links[$i] .= $css.',';
		}

		foreach( $internals_links as $tag )
			$internals_link_tags .= '<link rel="stylesheet" href="' . $_base . rtrim( $tag, ',' ) . '" />';
	}

	// Get all external link tags
    $externals_link_tags = count( $externals_css ) ? implode( "\n" , $externals_css ) : '';// Get all external link tags

    // Get all exclude link tags
    $excludes_link_tags = count( $excludes_css ) ? implode( "\n" , $excludes_css ) : '';
	
	// Get all Google Fonts CSS files
	preg_match_all( '/<link.+href=.+(fonts\.googleapis\.com\/css).+>/iU', $buffer, $google_fonts_tags_match );
	
	foreach ( $google_fonts_tags_match[0] as $tag ) 
	{
		
		// Get link of the file
        preg_match( '/href=[\'"]([^\'"]+)/', $tag, $href_match );
        
        $google_fonts[] = $tag;
        
        // Delete the link tag
        $buffer = str_replace( $tag, '', $buffer ); 
	}
	
	// Get all exclude link tags
    $google_fonts_tags = count( $google_fonts ) ? implode( "\n" , $google_fonts ) : '';
	
	// Insert the minify css file below <head>
	return array( $buffer, $google_fonts_tags . $externals_link_tags . $internals_link_tags . $excludes_link_tags );
}



/**
 * Used to minify and concat JavaScript files
 *
 * @since 1.1.0 Fix Bug with externals URLs like //ajax.google.com
 * @since 1.0.2 Remove the filter, remove the array_chunk, add an automatic way to cut strings to 255c max
 * @since 1.0
 *
 */

function rocket_minify_js( $buffer )
{

    $internals_js = array();
    $externals_js = array();
    $excludes_js  = array();

    // Get all JS files with this regex
    preg_match_all( '/<script.+src=.+(\.js).+><\/script>/iU', $buffer, $tags_match );

    foreach ( $tags_match[0] as $tag ) {

		// Get link of the file
        preg_match('/src=[\'"]([^\'"]+)/', $tag, $src_match );

        // Get URLs infos
        $js_url = parse_url( $src_match[1] );
		$home_url = parse_url( home_url() );

        // Check if the link isn't external
        // Insert the relative path to the array without query string
        if( $js_url['host'] == $home_url['host'] )
        {
	        if( !in_array( $js_url['path'], get_rocket_option( 'exclude_js', array() ) ) && pathinfo( $js_url['path'], PATHINFO_EXTENSION ) == 'js' )
	        	$internals_js[] = $js_url['path'];
	        else
	        	$excludes_js[] = $tag;
        }
        else
        {
	        $externals_js[] = $tag;
        }

		// Delete the script tag
        $buffer = str_replace( $tag, '', $buffer );

    }

	// Get the internal JavaScript Files
	// To avoid conflicts with file URLs are too long for browsers,
	// cut into several parts concatenated files
	$i=0;
	$internals_scripts = array($i=>'');
	$internals_script_tags = '';
	$_base = WP_ROCKET_URL . 'min/?f=';

	if( count( $internals_js ) )
	{
		foreach( $internals_js as $js )
		{
			if( strlen( $internals_scripts[$i].$_base.$js )+1>=255 ) // +1 : we count the extra comma
				$i++;
			$internals_scripts[$i] .= $js.',';
		}

		foreach( $internals_scripts as $tag )
			$internals_script_tags .= '<script src="' . $_base . rtrim( $tag, ',' ) . '"></script>';
	}

	// Get all external script tags
    $externals_script_tags = count( $externals_js ) ? implode( "\n" , $externals_js ) : '';

    // Get all excludes script tags
    $excludes_script_tags = count( $excludes_js ) ? implode( "\n" , $excludes_js ) : '';

    // Insert the minify JS file
    return array( $buffer,  $externals_script_tags . $internals_script_tags . $excludes_script_tags );
}



/**
 * Get all CSS ans JS files of IE conditionals tags
 *
 * @since 1.0
 * @source : WP Minify
 *
 */

function rocket_extract_ie_conditionals( $buffer )
{

    preg_match_all('/<!--\[if[^\]]*?\]>.*?<!\[endif\]-->/is', $buffer, $conditionals_match );
    $buffer = preg_replace( '/<!--\[if[^\]]*?\]>.*?<!\[endif\]-->/is', '{{WP_ROCKET_CONDITIONAL}}', $buffer );

    $conditionals = array();
    foreach ($conditionals_match[0] as $conditional)
      $conditionals[] = $conditional;

    return array( $buffer, $conditionals );
}



/**
 * Replace WP Rocket IE conditionals tags
 *
 * @since 1.0
 * @source : WP Minify
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