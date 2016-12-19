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
	global $pagenow;
	
	if ( get_rocket_option( 'minify_js' ) && ! in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ) ) && ( ! defined( 'DONOTMINIFYJS' ) || ! DONOTMINIFYJS ) && ( ! defined( 'DONOTCACHEPAGE' ) || ! DONOTCACHEPAGE ) && ! is_rocket_post_excluded_option( 'minify_js' ) && ! is_404() ) {
		// Don't apply for logged users if the option is turned off.
		if ( is_user_logged_in() && ! get_rocket_option( 'cache_logged_user' ) ) {
			return;
		}
		
		/** This filter is documented in inc/front/process.php */
		$rocket_cache_search = apply_filters( 'rocket_cache_search', false );
		
		// Don't apply on search page
		if( is_search() && ! $rocket_cache_search ) {
			return;
		}

		// Don't apply on excluded pages.
		if ( in_array( $_SERVER['REQUEST_URI'] , get_rocket_option( 'cache_reject_uri' , array() ) ) ) {
			return;
		}
		
		global $rocket_enqueue_js_in_footer;
		$home_host      = parse_url( home_url(), PHP_URL_HOST );
		$files          = get_rocket_minify_js_in_footer();
		$ordered_files  = array();
		
		// Get host of CNAMES
		$cnames_host = get_rocket_cnames_host( array( 'all', 'css_and_js', 'js' ) );

		$i = 0;
		foreach( $files as $file ) {
    		/** This filter is documented in wp-includes/class.wp-scripts.php */
    		$file = apply_filters( 'script_loader_src', $file, '' );
			list( $file_host, $file_path ) = get_rocket_parse_url( $file );

			// Check if its an external file
			if( $home_host != $file_host && ! in_array( $file_host, $cnames_host ) && ! in_array( $file_path, $rocket_enqueue_js_in_footer ) ) {
				if( isset( $ordered_files[ $i ] ) ) {
					$i++;
					$ordered_files[ $i++ ] = $file;
				} else {
					$ordered_files[ $i++ ] = $file;
				}
			} else {
				$ordered_files[ $i ][] = $file;
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
		if ( ! preg_match('/rel=["\']dns-prefetch["\']/', $matches[0][ $i ] ) ) {
			// Get fonts name
			$font = str_replace( array( '%7C', '%7c' ) , '|', $font );
			$font = explode( 'family=', $font );
			$font = ( isset( $font[1] ) ) ? explode( '&', $font[1] ) : array();

			// Add font to the collection
		    $fonts = array_merge( $fonts, explode( '|', reset( $font ) ) );

		    // Add subset to collection
			$subset = ( is_array( $font ) ) ? end( $font ) : '';
		    if ( false !== strpos( $subset, 'subset=' ) ) {
				$subset  = explode( 'subset=', $subset );
				$subsets = array_merge( $subsets, explode( ',', $subset[1] ) );
		    }

		    // Delete the Google Fonts tag
		    $buffer = str_replace( $matches[0][ $i ], '', $buffer );
		}

	    $i++;
	}

	// Concatenate fonts tag
	$subsets = ( $subsets ) ? '&subset=' . implode( ',', array_filter( array_unique( $subsets ) ) ) : '';
	$fonts   = trim( implode( '|' , array_filter( array_unique( $fonts ) ) ), '|' );
	$fonts	 = str_replace( '|', '%7C', $fonts );
	
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
		if ( ! class_exists( 'Minify_CSS_Compressor' ) && get_rocket_option( 'minify_html_inline_css', false ) ) {
			require( WP_ROCKET_PATH . 'min/lib/Minify/CSS/Compressor.php' );
			$html_options['cssMinifier'] = 'rocket_minify_inline_css';
		}

		// Check if JSMin is enable
		if ( ! class_exists( 'JSMin' ) && get_rocket_option( 'minify_html_inline_js', false ) ) {
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
    $excluded_css		  = implode( '|' , get_rocket_exclude_css() );
    $excluded_css 		  = str_replace( '//' . $home_host , '', $excluded_css );
    $wp_content_dirname   = ltrim( str_replace( home_url(), '', WP_CONTENT_URL ), '/' ) . '/';

    // Get all css files with this regex
    preg_match_all( apply_filters( 'rocket_minify_css_regex_pattern', '/<link\s*.+href=[\'|"]([^\'|"]+\.css?.+)[\'|"](.+)>/iU' ), $buffer, $tags_match );

	$i=0;
    foreach ( $tags_match[0] as $tag ) {
        // Check css media type
        // or the file is already minify by get_rocket_minify_files
        // or the file is rejected to the process
        if ( ( false === strpos( $tag, 'media=' ) || preg_match('/media=["\'](?:["\']|[^"\']*?(all|screen)[^"\']*?["\'])/', $tag ) ) && false === strpos( $tag, 'data-minify=' ) && false === strpos( $tag, 'data-no-minify=' ) ) {

			// To check if a tag is to exclude of the minify process
            $excluded_tag = false;

            // Get URLs infos
			$css_url  = parse_url( set_url_scheme( $tags_match[1][ $i ] ) );

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
			if ( ( isset( $css_url['host'] ) && ( $css_url['host'] == $home_host || in_array( $css_url['host'], $cnames_host ) || in_array( $css_url['host'], $langs_host ) ) ) || ( ! isset( $css_url['host'] ) && preg_match( '#(' . $wp_content_dirname . '|wp-includes)#', $css_url['path'] ) ) ) {

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

            if ( $excluded_tag && get_rocket_option( 'remove_query_strings' ) ) {
                $tag_cache_busting = str_replace( $tags_match[1][ $i ], rocket_browser_cache_busting( $tags_match[1][ $i ], 'style_loader_src' ), $tag );
                $buffer = str_replace( $tag, $tag_cache_busting, $buffer );
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
    $home_host            = parse_url( home_url(), PHP_URL_HOST );
    $internal_files       = array();
    $external_tags        = array();
    $excluded_tags        = '';
    $excluded_js		  = implode( '|', get_rocket_exclude_js() );
    $excluded_js 		  = str_replace( '//' . $home_host , '', $excluded_js );
    $excluded_js          = str_replace( '+', '\+', $excluded_js );
    $js_in_footer		  = get_rocket_minify_js_in_footer();
    $wp_content_dirname   = ltrim( str_replace( home_url(), '', WP_CONTENT_URL ), '/' ) . '/';
	$excluded_external_js = get_rocket_minify_excluded_external_js();

    // Get all JS files with this regex
    preg_match_all( apply_filters( 'rocket_minify_js_regex_pattern', '#<script[^>]+?src=[\'|"]([^\'|"]+\.js?.+)[\'|"].*>(?:<\/script>)#i' ), $buffer, $tags_match );

	$i=0;
    foreach ( $tags_match[0] as $tag ) {

        // Check if the file is already minify by get_rocket_minify_files
        // or the file is rejected to the process
        if ( ! strpos( $tag, 'data-minify=' ) && ! strpos( $tag, 'data-no-minify=' ) ) {

			// To check if a tag is to exclude of the minify process
            $excluded_tag = false;
			
			// Get JS URL with scheme
			$js_url_with_scheme = set_url_scheme( $tags_match[1][ $i ] );
			
	        // Get URL infos
	        $js_url = parse_url( $js_url_with_scheme );

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
	        if ( ( isset( $js_url['host'] ) && ( $js_url['host'] == $home_host || in_array( $js_url['host'], $cnames_host ) || in_array( $js_url['host'], $langs_host ) ) ) || ( ! isset( $js_url['host'] ) && preg_match( '#(' . $wp_content_dirname . '|wp-includes)#', $js_url['path'] ) ) ) {

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
				if ( ! in_array( $tags_match[1][ $i ], $js_in_footer ) && ! in_array( $js_url_with_scheme, $js_in_footer ) ) {
					$external_tags[] = $tag;
				}
			}

			// Remove the tag
            if ( ! $excluded_tag ) {
            	$buffer = str_replace( $tag, '', $buffer );
            }

            if ( $excluded_tag && get_rocket_option( 'remove_query_strings' ) ) {
                $tag_cache_busting = str_replace( $tags_match[1][ $i ], rocket_browser_cache_busting( $tags_match[1][ $i ], 'script_loader_src' ), $tag );
                $buffer = str_replace( $tag, $tag_cache_busting, $buffer );
            }
		}
		$i++;
	}

	// Get external JS tags and remove duplicate scripts
	$external_tags = implode( '', array_unique( $external_tags ) );

	// Remove domain on all JS in footer
	$js_in_footer = array_map( 'rocket_clean_exclude_file', $js_in_footer );
	
	// Exclude JS files to insert in footer
	foreach( $internal_files as $k=>$url ) {
		if ( in_array( $url, $js_in_footer ) ) {
			unset( $internal_files[ $k ] );
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
      if ( false !== strpos( $buffer, '{{WP_ROCKET_CONDITIONAL}}' ) ) {
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

/**
 * Force the minification to create only 1 file.
 *
 * @since 2.6
 */
add_filter( 'rocket_minify_filename_length', '__rocket_force_minify_combine_all', 10, 2 );
function __rocket_force_minify_combine_all( $length, $ext )  {
	if( $ext == 'css' && get_rocket_option( 'minify_css_combine_all', false ) ) {
		$length = PHP_INT_MAX;
	}

	if( $ext == 'js' && get_rocket_option( 'minify_js_combine_all', false ) ) {
		$length = PHP_INT_MAX;
	}

	return $length;
}

/**
 * Extract all enqueued CSS files which should be exclude to the minification
 *
 * @since 2.6
 */
add_action( 'wp_print_styles', '__rocket_extract_excluded_css_files' );
function __rocket_extract_excluded_css_files() {
	global $rocket_excluded_enqueue_css, $wp_styles, $pagenow;

	if( ! isset( $wp_styles->queue ) || ! is_array( $wp_styles->queue ) || ! get_rocket_option( 'minify_css', false ) || in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ) ) || ( defined( 'DONOTMINIFYCSS' ) && DONOTMINIFYCSS ) || ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) || is_rocket_post_excluded_option( 'minify_css' ) || is_404() ) {
		return;
	}

	$excluded_handle = array(
		// None for the moment
	);
		
	foreach( $wp_styles->queue as $handle ) {
		if ( in_array( $handle, $excluded_handle ) || ( isset( $wp_styles->registered[ $handle ] ) && strstr( $wp_styles->registered[ $handle ]->args, 'only screen and' ) ) ) {
			$rocket_excluded_enqueue_css[] = rocket_clean_exclude_file( rocket_set_internal_url_scheme( $wp_styles->registered[ $handle ]->src ) );
		}
	}
}

/**
 * Extract all enqueued JS files which should be exclude to the minification
 *
 * @since 2.6.1
 */
add_action( 'wp_print_scripts', '__rocket_extract_excluded_js_files' );
function __rocket_extract_excluded_js_files() {
	global $rocket_excluded_enqueue_js, $wp_scripts, $pagenow;
	
	if( ! isset( $wp_scripts->queue ) || ! is_array( $wp_scripts->queue ) || ! get_rocket_option( 'minify_js', false ) || in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ) ) || ( defined( 'DONOTMINIFYJS' ) && DONOTMINIFYJS ) || ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) || is_rocket_post_excluded_option( 'minify_js' ) || is_404() ) {
		return;
	}

	$excluded_handle = array(
		'admin-bar'
	);
	
	/**
	 * Filter JS enqueued files to exclude to the minification process.
	 *
	 * @since 2.6.1
	 *
	 * @param array List of script's name.
	 */
	$excluded_handle = apply_filters( 'rocket_excluded_handle_js', $excluded_handle );

	foreach( $wp_scripts->queue as $handle ) {
		if ( in_array( $handle, $excluded_handle ) ) {
			$rocket_excluded_enqueue_js[] = rocket_clean_exclude_file( rocket_set_internal_url_scheme( $wp_scripts->registered[ $handle ]->src ) );
		}
	}
}

/**
 * Extract all enqueued JS files which should be insert in the footer
 *
 * @since 2.6
 */
add_action( 'wp_footer', '__rocket_extract_js_files_from_footer', 1 );
function __rocket_extract_js_files_from_footer() {
	global $rocket_enqueue_js_in_footer, $wp_scripts, $pagenow;
	
	$rocket_enqueue_js_in_footer = array();
	
	/** This filter is documented in inc/front/process.php */
	$rocket_cache_search = apply_filters( 'rocket_cache_search', false );
	
	if( empty( $wp_scripts->in_footer ) || ! get_rocket_option( 'minify_js', false ) || in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ) ) || ( defined( 'DONOTMINIFYJS' ) && DONOTMINIFYJS ) || ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) || is_rocket_post_excluded_option( 'minify_js' ) || is_404() || ( is_search() && ! $rocket_cache_search ) ) {
		return;
	}
	
	// Digg Digg (https://wordpress.org/plugins/digg-digg/)
	if ( defined( 'DD_PLUGIN_URL' ) ) {
		$rocket_enqueue_js_in_footer[] = DD_PLUGIN_URL . '/js/diggdigg-floating-bar.js';
	}

	// nrelate Flyout (https://wordpress.org/plugins/nrelate-flyout/)
	if ( defined( 'NRELATE_PLUGIN_VERSION' ) ) {
		$rocket_enqueue_js_in_footer[] = ( NRELATE_JS_DEBUG ) ? 'http://staticrepo.nrelate.com/common_wp/'. NRELATE_PLUGIN_VERSION . '/nrelate_js.js' : NRELATE_ADMIN_URL . '/nrelate_js.min.js';
	}
	
	$home_host            = parse_url( home_url(), PHP_URL_HOST );
	$deferred_js_files    = get_rocket_deferred_js_files();
	$excluded_js 		  = get_rocket_exclude_js();
	$excluded_external_js = get_rocket_minify_excluded_external_js();

	foreach( $wp_scripts->in_footer as $handle ) {
		$script_src  = $wp_scripts->registered[ $handle ]->src;
		$script_src  = ( strstr( $script_src, '/wp-includes/js/') ) ? $wp_scripts->base_url . $script_src : $script_src;
		$script_src_cleaned = str_replace( array( 'http:', 'https:', '//' . $home_host ), '', $script_src );

		if( in_array( $handle, $wp_scripts->queue ) && ! in_array( parse_url( $script_src, PHP_URL_HOST ), $excluded_external_js ) && ! in_array( $script_src, $deferred_js_files ) && ! in_array( parse_url( $script_src, PHP_URL_PATH ), $excluded_js ) && ! in_array( parse_url( $script_src_cleaned, PHP_URL_PATH ), $excluded_js ) ) {			
			
			// Dequeue JS files without extension
			if( pathinfo( $script_src, PATHINFO_EXTENSION ) == '' ) {
				wp_dequeue_script( $handle );
			}
			
			// Add protocol on external JS to prevent conflict
			if( $home_host != parse_url( $script_src, PHP_URL_HOST ) && strpos( $script_src, 'http://' ) === false && strpos( $script_src, 'https://' ) === false ) {
				$script_src = set_url_scheme( $script_src );
			}
			
			// Add dependency enqueued in the footer
			foreach( $wp_scripts->registered[ $handle ]->deps as $handle_dep ) {
				if( in_array( $handle_dep, $wp_scripts->in_footer ) ) {
					$src = $wp_scripts->registered[ $handle_dep ]->src;
					$src = ( strstr( $src, '/wp-includes/js/') ) ? $wp_scripts->base_url . $src : $src;
					$rocket_enqueue_js_in_footer[ $handle_dep ] = rocket_set_internal_url_scheme( $src );
				}
			}
			
			$rocket_enqueue_js_in_footer[ $handle ] = rocket_set_internal_url_scheme( $script_src );
		}
	}
}

/**
 * Compatibility with WordPress multisite with subfolders websites
 *
 * @since 2.6.5
 */
add_filter( 'rocket_pre_minify_path', '__rocket_fix_minify_multisite_path_issue' );
function __rocket_fix_minify_multisite_path_issue( $url ) {
	if ( ! is_multisite() || is_main_site() ) {
		return $url;
	}
	
	// Current blog infos
	$blog_id  = get_current_blog_id();
	$bloginfo = get_blog_details( $blog_id, false );
	
	// Main blog infos
	$main_blog_id = 1;
	
	if ( ! empty( $GLOBALS['current_site']->blog_id ) ) {
		$main_blog_id = absint( $GLOBALS['current_site']->blog_id );
	}
	elseif ( defined( 'BLOG_ID_CURRENT_SITE' ) ) {
		$main_blog_id = absint( BLOG_ID_CURRENT_SITE );
	}
	elseif ( defined( 'BLOGID_CURRENT_SITE' ) ) { // deprecated.
		$main_blog_id = absint( BLOGID_CURRENT_SITE );
	}
	
	$main_bloginfo = get_blog_details( $main_blog_id, false );
	
	if ( $bloginfo->path != '/' ) {
    	$first_path_pos = strpos( $url, $bloginfo->path );
    	if ( $first_path_pos !== false ) {
		    $url = substr_replace( $url, $main_bloginfo->path, $first_path_pos, strlen( $bloginfo->path ) );
        }
	}
	
	return $url;
}

/**
 * Compatibility with multilingual plugins & multidomain configuration
 *
 * @since 2.6.13 Regression Fix: Apply CDN on minified CSS and JS files by checking the CNAME host
 * @since 2.6.8
 */
add_filter( 'rocket_css_url', '__rocket_minify_i18n_multidomain' );
add_filter( 'rocket_js_url'	, '__rocket_minify_i18n_multidomain' );
function __rocket_minify_i18n_multidomain( $url ) {
	if ( ! rocket_has_i18n() ) {
		return $url;
	}
	
	$url_host = parse_url( $url, PHP_URL_HOST );
	$zone     = array( 'all', 'css_and_js' );
	
	// Add only CSS zone
	if ( current_filter() == 'rocket_css_url' ) {
		$zone[] = 'css';
	}

	// Add only JS zone
	if ( current_filter() == 'rocket_js_url' ) {
		$zone[] = 'js';
	}
	
	$cnames = get_rocket_cdn_cnames( $zone );
	$cnames = array_map( 'rocket_remove_url_protocol' , $cnames );
	
	if ( $url_host != $_SERVER['HTTP_HOST'] && in_array( $_SERVER['HTTP_HOST'], get_rocket_i18n_host() ) && ! in_array( $url_host, $cnames ) ) {
		$url = str_replace( $url_host, $_SERVER['HTTP_HOST'], $url );
	}
	
	return $url;
}