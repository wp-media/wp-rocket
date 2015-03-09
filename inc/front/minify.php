<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/**
 * Launch WP Rocket minification process (CSS and JavaScript)
 *
 * @since 1.3.0 This process is called via the new filter rocket_buffer
 * @since 1.1.6 Minify inline CSS and JavaScript
 * @since 1.0
 */
add_filter( 'rocket_buffer', 'rocket_minify_process', 13 );
function rocket_minify_process( $buffer )
{
	$enable_js  = get_rocket_option( 'minify_js' );
	$enable_css = get_rocket_option( 'minify_css' );
	$enable_google_fonts = get_rocket_option( 'minify_google_fonts' );

	if ( $enable_css || $enable_js || $enable_google_fonts ) {

		$css = '';
		$js  = '';
		$google_fonts = '';

		list( $buffer, $conditionals ) = rocket_extract_ie_conditionals( $buffer );

		// Minify CSS
	    if ( $enable_css && ( ! defined( 'DONOTMINIFYCSS' ) || ! DONOTMINIFYCSS ) && ! is_rocket_post_excluded_option( 'minify_css' ) ) {
	    	list( $buffer, $css ) = rocket_minify_css( $buffer );
		}

	    // Minify JavaScript
	    if ( $enable_js && ( ! defined( 'DONOTMINIFYJS' ) || ! DONOTMINIFYJS ) && ! is_rocket_post_excluded_option( 'minify_js' ) ) {
	    	list( $buffer, $js ) = rocket_minify_js( $buffer );
		}

		// Concatenate Google Fonts
	    if ( $enable_google_fonts ) {
	    	list( $buffer, $google_fonts ) = rocket_concatenate_google_fonts( $buffer );
		}

	    $buffer = rocket_inject_ie_conditionals( $buffer, $conditionals );

		// Insert all CSS and JS files in head
		$buffer = preg_replace( '/<head(.*)>/', '<head$1>' . $google_fonts . $css . $js, $buffer, 1 );

	}

	// Minify HTML
	if ( get_rocket_option( 'minify_html' ) && ! is_rocket_post_excluded_option( 'minify_html' ) ) {
	    $buffer = rocket_minify_html( $buffer );
	}

	return $buffer;
}

/**
 * Insert JS minify files in footer
 *
 * @since 2.2
 */
add_action( 'wp_footer', '__rocket_insert_minify_js_in_footer', PHP_INT_MAX );
function __rocket_insert_minify_js_in_footer() {
	if ( ! empty( $_GET )
		&& ( ! isset( $_GET['utm_source'], $_GET['utm_medium'], $_GET['utm_campaign'] ) )
		&& ( ! isset( $_GET['fb_action_ids'], $_GET['fb_action_types'], $_GET['fb_source'] ) )
		&& ( ! isset( $_GET['permalink_name'] ) )
		&& ( ! isset( $_GET['lp-variation-id'] ) )
		&& ( ! isset( $_GET['lang'] ) )
	) {
		return;
	}

	if ( get_rocket_option( 'minify_js' ) && ( ! defined( 'DONOTMINIFYJS' ) || ! DONOTMINIFYJS ) && ! is_rocket_post_excluded_option( 'minify_js' ) && ! is_404() ) {
		// Don't apply for logged users if the option is turned off.
		if ( is_user_logged_in() && ! get_rocket_option( 'cache_logged_user' ) ) {
			return;
		}
		
		// Don't apply on excluded pages.
		if ( in_array( $_SERVER['REQUEST_URI'] , get_rocket_option( 'cache_reject_uri' , array() ) ) ) {
			return;
		}

		$home_host     = parse_url( home_url(), PHP_URL_HOST );
		$files         = get_rocket_option( 'minify_js_in_footer', array() );
		$ordered_files = array();

		// Get host of CNAMES
		$cnames_host = get_rocket_cnames_host( array( 'all', 'css_and_js', 'js' ) );

		$i=0;
		foreach( $files as $file ) {
			$file_host = parse_url( $file, PHP_URL_HOST );

			// Check if its an external file
			if( $home_host != $file_host && ! in_array( $file_host, $cnames_host ) ) {

				if( isset( $ordered_files[$i] ) ) {
					$i++;
					$ordered_files[$i++] = $file;
				} else {
					$ordered_files[$i] = $file;
					$i++;
				}

			} else {
				$ordered_files[$i][] = $file;
			}
		}

		// Print tags
		foreach( $ordered_files as $files ) {
			// Check if its an external file
			if ( is_string( $files ) ) {
				echo '<script src="' . $files . '" data-minify="1"></script>';
			} else {
				echo get_rocket_minify_files( $files );
			}
		}
	}
}

/**
 * Used for concatenate Google Fonts tags (http://fonts.googleapis.com/css?...)
 *
 * @since 2.3
 */
function rocket_concatenate_google_fonts( $buffer ) {
	// Get all Google Fonts CSS files
	$buffer_without_comments = preg_replace('/<!--(.*)-->/Uis', '', $buffer );
	preg_match_all( '/<link\s*.+href=[\'|"](.+fonts\.googleapis\.com.+)(\'|").+>/iU', $buffer_without_comments, $matches );
	
	$i = 0;
	$fonts   = array();
	$subsets = array();

	if ( ! $matches[1] ) {
		return array( $buffer, '' );
	}
	
	foreach ( $matches[1] as $font ) {
		if ( ! preg_match('/rel=["\']dns-prefetch["\']/', $matches[0][$i] ) ) {
			// Get fonts name
			$font = explode( 'family=', $font );
			$font = explode( '&', $font[1] );
			
			// Add font to the collection
		    $fonts[] = reset( $font );
	
		    // Add subset to collection
			$subset = end( $font );
		    if ( false !== strpos( $subset, 'subset=' ) ) {
				$subset  = explode( 'subset=', $subset );
				$subsets = array_merge( $subsets, explode( ',' , $subset[1] ) );   
		    }
	
		    // Delete the Google Fonts tag
		    $buffer = str_replace( $matches[0][$i], '', $buffer );	
		}
		
	    $i++;
	}

	// Concatenate fonts tag
	$subsets = ( $subsets ) ? '&subset=' . implode( ',', array_filter( array_unique( $subsets ) ) ) : '';
	$fonts   = trim( implode( '|' , $fonts ), '|' );
	
	if( ! empty( $fonts ) ) {
		$fonts   = '<link rel="stylesheet" href="//fonts.googleapis.com/css?family=' . $fonts . $subsets . '" />';	
	}
	
	return array( $buffer, $fonts );
}

/**
 * Used for minify inline HTML
 *
 * @since 1.1.12
 */
function rocket_minify_html( $buffer )
{
	// Check if Minify_HTML is enable
    if ( ! class_exists( 'Minify_HTML' ) ) {

	    $html_options = array();

	    require( WP_ROCKET_PATH . 'min/lib/Minify/HTML.php' );

		// Check if Minify_CSS_Compressor is enable
		if ( ! class_exists( 'Minify_CSS_Compressor' ) ) {
			require( WP_ROCKET_PATH . 'min/lib/Minify/CSS/Compressor.php' );
			$html_options['cssMinifier'] = 'rocket_minify_inline_css';
		}

		// Check if JSMin is enable
		if ( ! class_exists( 'JSMin' ) ) {
			require( WP_ROCKET_PATH . 'min/lib/JSMin.php' );
			$html_options['jsMinifier'] = 'rocket_minify_inline_js';
		}

		/**
		 * Filter options of minify inline HTML
		 *
		 * @since 1.1.12
		 *
		 * @param array $html_options Options of minify inline HTML
		 */
		$html_options = apply_filters( 'rocket_minify_html_options', $html_options );
		$buffer = Minify_HTML::minify( $buffer, $html_options );
    }

    return $buffer;
}

/**
 * Used for minify inline CSS
 *
 * @since 1.1.6
 */
function rocket_minify_inline_css( $css )
{
	return Minify_CSS_Compressor::process( $css );
}

/**
 * Used for minify inline JavaScript
 *
 * @since 1.1.6
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
    $home_host            = parse_url( home_url(), PHP_URL_HOST );
    $internal_files       = array();
    $external_tags        = '';
    $excluded_tags        = '';
    $fonts_tags           = '';
    $excluded_css		  = implode( '|' , get_rocket_option( 'exclude_css', array() ) );
    $excluded_css 		  = str_replace( '//' . $home_host , '', $excluded_css );
	
    // Get all css files with this regex
    preg_match_all( apply_filters( 'rocket_minify_css_regex_pattern', '/<link\s*.+href=[\'|"]([^\'|"]+\.css?.+)[\'|"]?(.+)>/iU' ), $buffer, $tags_match );

	$i=0;
    foreach ( $tags_match[0] as $tag ) {
        // Check css media type
        // or the file is already minify by get_rocket_minify_files
        // or the file is rejected to the process
        if ( ( false === strpos( $tag, 'media=' ) || preg_match('/media=["\'](?:["\']|[^"\']*?(all|screen)[^"\']*?["\'])/', $tag ) ) && false === strpos( $tag, 'data-minify=' ) && false === strpos( $tag, 'data-no-minify=' ) ) {

			// To check if a tag is to exclude of the minify process
            $excluded_tag = false;

            // Get URLs infos
			$css_url  = parse_url( rocket_add_url_protocol( $tags_match[1][$i] ) );

			// Get host for all langs
			$langs_host = array();
			if ( $langs = get_rocket_i18n_uri() ) {
				foreach ( $langs as $lang ) {
					$langs_host[] = parse_url( $lang, PHP_URL_HOST );
				}
			}

			// Get host of CNAMES
			$cnames_host = get_rocket_cnames_host( array( 'all', 'css_and_js', 'css' ) );

            // Check if the file isn't external
            // Insert the relative path to the array without query string
			if ( isset( $css_url['host'] ) && ( $css_url['host'] == $home_host || in_array( $css_url['host'], $cnames_host ) || in_array( $css_url['host'], $langs_host ) ) ) {

				// Check if it isn't a file to exclude
				if( ! preg_match( '#^(' . $excluded_css . ')$#', $css_url['path'] ) && pathinfo( $css_url['path'], PATHINFO_EXTENSION ) == 'css' ) {
					$internal_files[] = $css_url['path'];
				} else {
					$excluded_tag = true;
				}
			
			// If it is an external file
			} else {
				$external_tags .= $tag;
			}

            // Remove the tag
            if ( ! $excluded_tag ) {
            	$buffer = str_replace( $tag, '', $buffer );
            }

        }
		$i++;
    }

	// Insert the minify css file below <head>
	return array( $buffer, $external_tags . get_rocket_minify_files( $internal_files ) );
}

/**
 * Used to minify and concat JavaScript files
 *
 * @since 1.1.0 Fix Bug with externals URLs like //ajax.google.com
 * @since 1.0.2 Remove the filter, remove the array_chunk, add an automatic way to cut strings to 255c max
 * @since 1.0
 */
function rocket_minify_js( $buffer )
{
    list( $home_host, $home_path, $home_scheme ) = get_rocket_parse_url( home_url() );
    $internal_files       = array();
    $external_tags        = array();
    $excluded_tags        = '';
    $excluded_js		  = implode( '|' , get_rocket_option( 'exclude_js', array() ) );
    $excluded_js 		  = str_replace( '//' . $home_host , '', $excluded_js );
    $js_in_footer         = get_rocket_option( 'minify_js_in_footer', array() );
    $wp_content_dirname   = ltrim( str_replace( home_url(), '', WP_CONTENT_URL ), '/' ) . '/';

	/**
	 * Filter JS externals files to exclude of the minification process (do not move into the header)
	 *
	 * @since 2.2
	 *
	 * @param array Hostname of JS files to exclude
	 */
	$excluded_external_js = apply_filters( 'rocket_minify_excluded_external_js', array( 
		'forms.aweber.com', 
		'video.unrulymedia.com', 
		'gist.github.com', 
		'stats.wp.com', 
		'stats.wordpress.com', 
		'www.statcounter.com', 
		'widget.rafflecopter.com', 
		'widget-prime.rafflecopter.com', 
		'widget.supercounters.com', 
		'releases.flowplayer.org', 
		'tools.meetaffiliate.com', 
		'c.ad6media.fr', 
		'cdn.stickyadstv.com', 
		'www.smava.de', 
		'contextual.media.net', 
		'app.getresponse.com', 
		'ap.lijit.com', 
		'adserver.reklamstore.com', 
		's0.wp.com', 
		'wprp.zemanta.com', 
		'files.bannersnack.com', 
		'smarticon.geotrust.com',
		'js.gleam.io'
	) );
	
    // Get all JS files with this regex
    preg_match_all( apply_filters( 'rocket_minify_js_regex_pattern', '#<script\s*.+src=[\'|"]([^\'|"]+\.js?.+)[\'|"]?(.+)></script>#iU' ), $buffer, $tags_match );

	$i=0;
    foreach ( $tags_match[0] as $tag ) {

        // Chek if the file is already minify by get_rocket_minify_files
        // or the file is rejected to the process
        if ( ! strpos( $tag, 'data-minify=' ) && ! strpos( $tag, 'data-no-minify=' ) ) {

			// To check if a tag is to exclude of the minify process
            $excluded_tag = false;

	        // Get URLs infos
	        $js_url = parse_url( rocket_add_url_protocol( $tags_match[1][$i] ) );

			// Get host for all langs
			$langs_host = array();
			if ( $langs = get_rocket_i18n_uri() ) {
				foreach ( $langs as $lang ) {
					$langs_host[] = parse_url( $lang, PHP_URL_HOST );
				}
			}

			// Get host of CNAMES
			$cnames_host = get_rocket_cnames_host( array( 'all', 'css_and_js', 'js' ) );

	        // Check if the link isn't external
	        // Insert the relative path to the array without query string
	        if ( isset( $js_url['host'] ) && ( $js_url['host'] == $home_host || in_array( $js_url['host'], $cnames_host ) || in_array( $js_url['host'], $langs_host ) ) ) {

		        // Check if it isn't a file to exclude
		        if ( ! preg_match( '#^(' . $excluded_js . ')$#', $js_url['path'] ) && pathinfo( $js_url['path'], PATHINFO_EXTENSION ) == 'js' ) {
			        $internal_files[] = $js_url['path'];
		        } else {
			        $excluded_tag = true;
		        }
			// If it's an excluded external file
			} else if ( isset( $js_url['host'] ) && in_array( $js_url['host'], $excluded_external_js ) ) {

				$excluded_tag = true;

			// If it's an external file
			} else {
				if ( ! in_array( $tags_match[1][$i], $js_in_footer ) ) {
					$external_tags[] = $tag;
				}
			}

			// Remove the tag
            if ( ! $excluded_tag ) {
            	$buffer = str_replace( $tag, '', $buffer );
            }
		}
		$i++;
	}
	
	// Get external JS tags and remove duplicate scripts
	$external_tags = implode( '', array_unique( $external_tags ) );
	
	// Exclude JS files to insert in footer
	foreach( $internal_files as $k=>$url ) {
		if ( in_array( $home_scheme . '://' . $home_host . $url , $js_in_footer ) ) {
			unset( $internal_files[$k] );
		}
	}

    // Insert the minify JS file
    return array( $buffer, $external_tags . get_rocket_minify_files( $internal_files ) );
}

/**
 * Get all CSS ans JS files of IE conditionals tags
 *
 * @since 1.0
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
 * @since 1.0
 */
function rocket_inject_ie_conditionals( $buffer, $conditionals )
{
    foreach( $conditionals as $conditional ) {
      if ( strpos( $buffer, '{{WP_ROCKET_CONDITIONAL}}' ) ) {
        $buffer = preg_replace( '/{{WP_ROCKET_CONDITIONAL}}/' , $conditional, $buffer, 1 );
      } else {
      	break;
      }
	}
    return $buffer;
}

/**
 * Fix issue with SSL and minification
 *
 * @since 2.3
 */
add_filter( 'rocket_css_url', '__rocket_fix_ssl_minify' );
add_filter( 'rocket_js_url', '__rocket_fix_ssl_minify' );
function __rocket_fix_ssl_minify( $url ) {
	if ( is_ssl() && false === strpos( $url, 'https://' ) && ! in_array( parse_url( $url, PHP_URL_HOST ), get_rocket_cnames_host( array( 'all', 'css_js', 'css', 'js' ) ) ) ) {
		$url = str_replace( 'http://', 'https://', $url );
	}
	
	return $url;
}