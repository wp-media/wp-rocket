<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Get relative url
 * Clean URL file to get only the equivalent of REQUEST_URI
 * ex: rocket_clean_exclude_file( 'http://www.geekpress.fr/referencement-wordpress/') return /referencement-wordpress/
 *
 * @since 1.3.5 Redo the function
 * @since 1.0
 */
function rocket_clean_exclude_file( $file )
{
	if ( ! $file ) {
		return false;
	}

	$path = parse_url( $file, PHP_URL_PATH );
    return $path;
}

/**
 * Used with array_filter to remove files without .css extension
 *
 * @since 1.0
 */
function rocket_sanitize_css( $file )
{
	$file = preg_replace( '#\?.*$#', '', $file );
	$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
	return $ext=='css' ? trim( $file ) : false;
}

/**
 * Used with array_filter to remove files without .js extension
 *
 * @since 1.0
 */
function rocket_sanitize_js( $file )
{
	$file = preg_replace( '#\?.*$#', '', $file );
	$ext  = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
	return $ext == 'js' ? trim( $file ) : false;
}

/**
 * Get an url without HTTP protocol
 *
 * @since 1.3.0
 *
 * @param string $url The URL to parse
 * @param bool 	 $no_dots (default: false)
 * @return string $url The URL without protocol
 */
function rocket_remove_url_protocol( $url, $no_dots=false )
{
	$url = str_replace( array( 'http://', 'https://' ) , '', $url );

	/** This filter is documented in inc/front/htaccess.php */
	if ( apply_filters( 'rocket_url_no_dots', $no_dots ) ) {
		$url = str_replace( '.', '_', $url );
	}
	return $url;
}

/**
 * Add HTTP protocol to an url that does not have
 *
 * @since 2.2.1
 *
 * @param string $url The URL to parse
 * @return string $url The URL with protocol
 */
function rocket_add_url_protocol( $url ) {
	if ( strpos( $url, 'http://' ) === false && strpos( $url, 'https://' ) === false ) {
		$url = 'http://' . ltrim( $url, '//' );
	}
	return $url;
}

/**
 * Set the scheme for a internal URL
 *
 * @since 2.6
 *
 * @param 	string $url Absolute url that includes a scheme
 * @return 	string $url URL with a scheme.
 */
function rocket_set_internal_url_scheme( $url ) {
	$tmp_url = set_url_scheme( $url );

    if( parse_url( $tmp_url, PHP_URL_HOST ) == parse_url( home_url(), PHP_URL_HOST ) ) {
            $url = $tmp_url;
    }

    return $url;
}

/**
 * Extract and return host, path, query and scheme of an URL
 *
 * @since 2.1 Add $query variable
 * @since 2.0
 *
 * @param string $url The URL to parse
 * @return array Components of an URL
 */
function get_rocket_parse_url( $url )
{
	if ( ! is_string( $url ) ) {
		return;
	}

	$url    = parse_url( $url );
	$host   = isset( $url['host'] ) ? $url['host'] : '';
	$path   = isset( $url['path'] ) ? $url['path'] : '';
	$scheme = isset( $url['scheme'] ) ? $url['scheme'] : '';
	$query  = isset( $url['query'] ) ? $url['query'] : '';

	/**
	 * Filter components of an URL
	 *
	 * @since 2.2
	 *
	 * @param array Components of an URL
	*/
	return apply_filters( 'rocket_parse_url', array( $host, $path, $scheme, $query ) );
}

/**
 * Get CNAMES hosts
 *
 * @since 2.3
 *
 * @param string $zones CNAMES zones
 * @return array $hosts CNAMES hosts
 */
function get_rocket_cnames_host( $zones = array( 'all' ) ) {
	$hosts = array();

	if ( $cnames = get_rocket_cdn_cnames( $zones ) ) {
		foreach ( $cnames as $cname ) {
			$cname = rocket_add_url_protocol( $cname );
			$hosts[] = parse_url( $cname, PHP_URL_HOST );
		}
	}

	return $hosts;
}

/*
 * Get an URL with one of CNAMES added in options
 *
 * @since 2.1
 *
 * @param string $url The URL to parse
 * @param array  $zone (default: array( 'all' ))
 * @return string $url The URL with one of CNAMES
 */
function get_rocket_cdn_url( $url, $zone = array( 'all' ) )
{
	$cnames             = get_rocket_cdn_cnames( $zone );
	$wp_content_dirname = ltrim( str_replace( home_url(), '', WP_CONTENT_URL ), '/' ) . '/';

	if ( ( defined( 'DONOTCDN' ) && DONOTCDN ) || (int) get_rocket_option('cdn') == 0 || empty( $cnames ) || ! is_rocket_cdn_on_ssl() || is_rocket_post_excluded_option( 'cdn' ) ) {
		return $url;
	}

	list( $host, $path, $scheme, $query ) = get_rocket_parse_url( $url );
	$query = ! empty( $query ) ? '?' . $query : '';

	// Exclude rejected files from CDN
	$rejected_files = get_rocket_cdn_reject_files();
	if( ! empty( $rejected_files ) && preg_match( '#(' . $rejected_files . ')#', $path ) ) {
		return $url;
	}

	if ( empty( $scheme ) ) {
		$home = rocket_remove_url_protocol( home_url() );

		// Check if URL is external
		if ( strpos( $path, $home ) === false && ! preg_match( '#(' . $wp_content_dirname . '|wp-includes)#', $path ) ) {
			return $url;
		} else {
			$path = str_replace( $home, '', ltrim( $path, '//' ) );
		}
	}

	$url = rtrim( $cnames[(abs(crc32($path))%count($cnames))], '/' ) . '/' . ltrim( $path, '/' ) . $query;
	$url = rocket_add_url_protocol( $url );
	return $url;
}

/*
 * Wrapper of get_rocket_cdn_url() and print result
 *
 * @since 2.1
 */
function rocket_cdn_url( $url, $zone = array( 'all' ) )
{
	echo get_rocket_cdn_url( $url, $zone );
}

/*
 * Apply CDN on CSS properties (background, background-image, @import, src:url (fonts))
 *
 * @since 2.6
 */
function rocket_cdn_css_properties( $buffer ) {
	$zone   = array( 'all', 'css_and_js', 'css' );
	$cnames = get_rocket_cdn_cnames( $zone );
	
	/**
	  * Allow a "force deactivation" link to be printed, use at your own risks
	  *
	  * @since 2.0.0
	  *
	  * @param bool true will print the link
	 */
	$do_rocket_cdn_css_properties = apply_filters( 'do_rocket_cdn_css_properties', true );
	
	if ( ! get_rocket_option( 'cdn' ) || ! $cnames || ! $do_rocket_cdn_css_properties ) {
		return $buffer;
	}

	preg_match_all( '/url\(([^)]+)\)/i', $buffer, $matches );

	if( is_array( $matches ) ) {
		$i=0;
		foreach( $matches[1] as $url ) {
			$url      = trim( $url," \t\n\r\0\x0B\"'" );
			$url      = get_rocket_cdn_url( $url, $zone );
			$property = str_replace( $matches[1][$i], $url, $matches[0][$i] );
			$buffer   = str_replace( $matches[0][$i], $property, $buffer );
			
			$i++;
		}
	}

	return $buffer;
}

/*
 * Apply CDN on custom data attributes.
 *
 * @since 2.5.5
 *
 * @param 	string $html Original Output
 * @return 	string $html Output that will be printed
 */
function rocket_add_cdn_on_custom_attr( $html ) {
	if( preg_match( '/(data-lazy-src|data-lazyload|data-src|data-retina)=[\'"]?([^\'"\s>]+)[\'"]/i', $html, $matches ) ) {
		$html = str_replace( $matches[2], get_rocket_cdn_url( $matches[2], array( 'all', 'images' ) ), $html );
	}

	return $html;
}