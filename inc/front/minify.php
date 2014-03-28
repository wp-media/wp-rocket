<?php
defined( 'ABSPATH' ) or die( __( 'Cheatin&#8217; uh?', 'rocket' ) );


/**
 * Launch WP Rocket minification process (CSS and JavaScript)
 *
 * @since 1.3.0 This process is called via the new filter rocket_buffer
 * @since 1.1.6 Minify inline CSS and JavaScript
 * @since 1.0
 *
 */

add_filter( 'rocket_buffer', 'rocket_minify_process', 13 );
function rocket_minify_process( $buffer )
{

	$enable_js 		 = get_rocket_option( 'minify_js' );
	$enable_css 	 = get_rocket_option( 'minify_css' );

	if( $enable_css || $enable_js ) {

		$css = '';
		$js  = '';

		list( $buffer, $conditionals ) = rocket_extract_ie_conditionals( $buffer );

		// Minify CSS
	    if( $enable_css ) {
	    	list( $buffer, $css ) = rocket_minify_css( $buffer );
		}

	    // Minify JavaScript
	    if( $enable_js ) {
	    	list( $buffer, $js ) = rocket_minify_js( $buffer );
		}

	    $buffer = rocket_inject_ie_conditionals( $buffer, $conditionals );

		// Insert all CSS and JS files in head
		$buffer = preg_replace( '/<head(.*)>/', '<head$1>' . $css . $js, $buffer, 1 );

	}

	// Minify HTML
	if( get_rocket_option( 'minify_html' ) ) {
	    $buffer = rocket_minify_html( $buffer );
	}

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
    if( !class_exists( 'Minify_HTML' ) ) {

	    $html_options = array();

	    require( WP_ROCKET_PATH . 'min/lib/Minify/HTML.php' );

		// Check if Minify_CSS_Compressor is enable
		if( !class_exists( 'Minify_CSS_Compressor' ) ) {
			require( WP_ROCKET_PATH . 'min/lib/Minify/CSS/Compressor.php' );
			$html_options['cssMinifier'] = 'rocket_minify_inline_css';
		}

		// Check if JSMin is enable
		if( !class_exists( 'JSMin' ) ) {
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

    $internal_files      = array();
    $external_tags       = '';
    $excluded_tags       = '';
    $fonts_tags 		 = '';

    // Get all css files with this regex
    preg_match_all( '/<link.+href=[\'|"]([^\'|"]+\.css?.+)[\'|"].+>/iU', $buffer, $tags_match );

	$i=0;
    foreach ( $tags_match[0] as $tag ) {

        // Check css media type
        // or the file is already minify by get_rocket_minify_files
        // or the file is rejected to the process
        if ( ( !strpos( $tag, 'media=' ) || preg_match('/media=["\'](?:["\']|[^"\']*?(all|screen)[^"\']*?["\'])/', $tag ) )
             && !strpos( $tag, 'data-minify=' )
             && !strpos( $tag, 'data-no-minify=' )
        ) {

			// To check if a tag is to exclude of the minify process
            $excluded_tag = false;

            // Get URLs infos
			$css_url  = parse_url( $tags_match[1][$i] );

			// Get host for all langs
			$langs_host = array();
			if ( $langs = get_rocket_all_active_langs_uri() ) {

				foreach ( $langs as $lang ) {
					$langs_host[] = parse_url( $lang, PHP_URL_HOST );
				}

			}

			// Get host of CNAMES
			$cnames_host = array();
			if ( $cnames = get_rocket_cdn_cnames( array( 'all', 'css_and_js' ) ) ) {

				foreach ( $cnames as $cname ) {
					$cnames_host[] = parse_url( $cname, PHP_URL_HOST );
				}

			}

            // Check if the file isn't external
            // Insert the relative path to the array without query string
			if ( isset( $css_url['host'] ) && ( $css_url['host'] == parse_url( home_url(), PHP_URL_HOST ) || in_array( $css_url['host'], $cnames_host ) || in_array( $css_url['host'], $langs_host ) ) ) {

				// Check if it isn't a file to exclude
				if( !in_array( $css_url['path'], get_rocket_option( 'exclude_css', array() ) )
					&& pathinfo( $css_url['path'], PATHINFO_EXTENSION ) == 'css'
				) {
					$internal_files[] = $css_url['path'];
				}
				else {
					$excluded_tag = true;
				}

			}
			else {
				$external_tags .= $tag;
			}

            // Remove the tag
            if( !$excluded_tag ) {
            	$buffer = str_replace( $tag, '', $buffer );
            }

        }

		$i++;
    }

	// Get all Google Fonts CSS files
	preg_match_all( '/<link.+href=.+(fonts\.googleapis\.com\/css).+>/iU', $buffer, $matches );
	foreach ( $matches[0] as $tag ) {

        $fonts_tags .= $tag;

        // Delete the link tag
        $buffer = str_replace( $tag, '', $buffer );

	}

	// Insert the minify css file below <head>
	return array( $buffer, $fonts_tags . $external_tags . get_rocket_minify_files( $internal_files ) );

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

    $internal_files      = array();
    $external_tags       = '';
    $excluded_tags       = '';

    // Get all JS files with this regex
    preg_match_all( '#<script.*src=[\'|"]([^\'|"]+\.js?.+)[\'|"].*></script>#iU', $buffer, $tags_match );

	$i=0;
    foreach ( $tags_match[0] as $tag ) {

        // Chek if the file is already minify by get_rocket_minify_files
        // or the file is rejected to the process
        if ( !strpos( $tag, 'data-minify=' ) && !strpos( $tag, 'data-no-minify=' ) ) {

			// To check if a tag is to exclude of the minify process
            $excluded_tag = false;

	        // Get URLs infos
	        $js_url = parse_url( $tags_match[1][$i] );

			// Get host for all langs
			$langs_host = array();
			if ( $langs = get_rocket_all_active_langs_uri() ) {

				foreach ( $langs as $lang ) {
					$langs_host[] = parse_url( $lang, PHP_URL_HOST );
				}

			}

			// Get host of CNAMES
			$cnames_host = array();
			if ( $cnames = get_rocket_cdn_cnames( array( 'all', 'css_and_js' ) ) ) {

				foreach ( $cnames as $cname ) {
					$cnames_host[] = parse_url( $cname, PHP_URL_HOST );
				}

			}

	        // Check if the link isn't external
	        // Insert the relative path to the array without query string
	        if ( isset( $js_url['host'] ) && ( $js_url['host'] == parse_url( home_url(), PHP_URL_HOST ) || in_array( $js_url['host'], $cnames_host ) || in_array( $js_url['host'], $langs_host ) ) ) {

		        // Check if it isn't a file to exclude
		        if( !in_array( $js_url['path'], get_rocket_option( 'exclude_js', array() ) )
		        	&& pathinfo( $js_url['path'], PATHINFO_EXTENSION ) == 'js'
		        ) {
			        $internal_files[] = $js_url['path'];
		        }
		        else {
			        $excluded_tag = true;
		        }

	        }
	        else {
		        $external_tags .= $tag;
	        }

			// Remove the tag
            if( !$excluded_tag ) {
            	$buffer = str_replace( $tag, '', $buffer );
            }

			$i++;

		}

	}
    // Insert the minify JS file
    return array( $buffer, $external_tags . get_rocket_minify_files( $internal_files ) );

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

    foreach( $conditionals as $conditional )
      if( strpos( $buffer, '{{WP_ROCKET_CONDITIONAL}}' ) )
        $buffer = preg_replace( '/{{WP_ROCKET_CONDITIONAL}}/' , $conditional, $buffer, 1 );
      else break;

    return $buffer;

}