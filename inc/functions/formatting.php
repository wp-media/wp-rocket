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