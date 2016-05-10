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
	return ( 'css' === $ext || 'php' === $ext ) ? trim( $file ) : false;
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
	return ( 'js' === $ext || 'php' === $ext ) ? trim( $file ) : false;
}

/**
 * Used with array_filter to remove files without .xml extension
 *
 * @since 2.8
 * @author Remy Perona
 *
 * @param string $file filename
 * @return string|boolean filename or false if not xml
 */
function rocket_sanitize_xml( $file )
{
	$file = preg_replace( '#\?.*$#', '', $file );
	$ext  = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
	return ( 'xml' === $ext ) ? trim( $file ) : false;
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
    	if ( substr( $url, 0, 2 ) !== '//' ) {
        	$url = '//' . $url;
    	}
		$url = set_url_scheme( $url );
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
 * Get the domain of an URL without subdomain
 * (ex: rocket_get_domain( 'http://www.geekpress.fr' ) return geekpress.fr
 *
 * @source : http://stackoverflow.com/a/15498686
 * @since 2.7.3 undeprecated & updated
 * @since 1.0
 *
 * @param $url URL to parse
 * @return string|bool Domain or false
 */
function rocket_get_domain( $url ) {
    // Add URL protocol if the $url doesn't have one to prevent issue with parse_url
    $url = rocket_add_url_protocol( trim( $url ) );

    $url_array = parse_url( $url );
    $host = $url_array['host'];
    /**
     * Filters the tld max range for edge cases
     *
     * @since 2.7.3
     *
     * @param string Max range number
     */
    $match = '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,' . apply_filters( 'rocket_get_domain_preg', '6' ) . '})$/i';

	if ( preg_match( $match, $host, $regs ) ) {
        return $regs['domain'];
	}

	return false;
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
	$host   = isset( $url['host'] ) ? strtolower( $url['host'] ) : '';
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