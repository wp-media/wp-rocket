<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Get relative url
 * Clean URL file to get only the equivalent of REQUEST_URI
 * ex: rocket_clean_exclude_file( 'http://www.geekpress.fr/referencement-wordpress/') return /referencement-wordpress/
 *
 * @since 1.3.5 Redo the function
 * @since 1.0
 *
 * @param string $file URL we want to parse.
 * @return bool\string false if $file is empty or false, relative path otherwise
 */
function rocket_clean_exclude_file( $file ) {
	if ( ! $file ) {
		return false;
	}

	$path = rocket_extract_url_component( $file, PHP_URL_PATH );
	return $path;
}

/**
 * Used with array_filter to remove files without .css extension
 *
 * @since 1.0
 *
 * @param string $file filepath to sanitize.
 * @return bool\string false if not a css file, filepath otherwise
 */
function rocket_sanitize_css( $file ) {
	$file = preg_replace( '#\?.*$#', '', $file );
	$ext  = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
	return ( 'css' === $ext || 'php' === $ext ) ? trim( $file ) : false;
}

/**
 * Used with array_filter to remove files without .js extension
 *
 * @since 1.0
 *
 * @param string $file filepath to sanitize.
 * @return bool\string false if not a js file, filepath otherwise
 */
function rocket_sanitize_js( $file ) {
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
 * @param string $file filepath to sanitize.
 * @return string|boolean filename or false if not xml
 */
function rocket_sanitize_xml( $file ) {
	$file = preg_replace( '#\?.*$#', '', $file );
	$ext  = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
	return ( 'xml' === $ext ) ? trim( $file ) : false;
}

/**
 * Sanitizes a string key like the sanitize_key() WordPress function without forcing lowercase.
 *
 * @since 2.7
 *
 * @param string $key Key string to sanitize.
 * @return string
 */
function rocket_sanitize_key( $key ) {
	$key = preg_replace( '/[^a-z0-9_\-]/i', '', $key );
	return $key;
}

/**
 * Used to sanitize values of the "Never send cache pages for these user agents" option.
 *
 * @since 2.6.4
 *
 * @param string $user_agent User Agent string.
 * @return string
 */
function rocket_sanitize_ua( $user_agent ) {
	$user_agent = preg_replace( '/[^a-z0-9._\(\)\*\-\/\s\x5c]/i', '', $user_agent );
	return $user_agent;
}

/**
 * Get an url without HTTP protocol
 *
 * @since 1.3.0
 *
 * @param string $url The URL to parse.
 * @param bool   $no_dots (default: false).
 * @return string $url The URL without protocol
 */
function rocket_remove_url_protocol( $url, $no_dots = false ) {
	$url = str_replace( array( 'http://', 'https://' ), '', $url );

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
 * @param string $url The URL to parse.
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
 * @param   string $url Absolute url that includes a scheme.
 * @return  string $url URL with a scheme.
 */
function rocket_set_internal_url_scheme( $url ) {
	$tmp_url = set_url_scheme( $url );

	if ( rocket_extract_url_component( $tmp_url, PHP_URL_HOST ) === rocket_extract_url_component( home_url(), PHP_URL_HOST ) ) {
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
 * @param string $url URL to parse.
 * @return string|bool Domain or false
 */
function rocket_get_domain( $url ) {
	// Add URL protocol if the $url doesn't have one to prevent issue with parse_url.
	$url = rocket_add_url_protocol( trim( $url ) );

	$url_array = wp_parse_url( $url );
	$host      = $url_array['host'];
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
 * @since 2.11.5 Supports UTF-8 URLs
 * @since 2.1 Add $query variable
 * @since 2.0
 *
 * @param string $url The URL to parse.
 * @return array Components of an URL
 */
function get_rocket_parse_url( $url ) {
	if ( ! is_string( $url ) ) {
		return;
	}

	$encoded_url = preg_replace_callback(
		'%[^:/@?&=#]+%usD',
		function ( $matches ) {
			return rawurlencode( $matches[0] );
		},
		$url
	);

	$url    = wp_parse_url( $encoded_url );
	$host   = isset( $url['host'] ) ? strtolower( urldecode( $url['host'] ) ) : '';
	$path   = isset( $url['path'] ) ? urldecode( $url['path'] ) : '';
	$scheme = isset( $url['scheme'] ) ? urldecode( $url['scheme'] ) : '';
	$query  = isset( $url['query'] ) ? urldecode( $url['query'] ) : '';

	/**
	 * Filter components of an URL
	 *
	 * @since 2.2
	 *
	 * @param array Components of an URL
	*/
	return apply_filters( 'rocket_parse_url', array(
		'host'   => $host,
		'path'   => $path,
		'scheme' => $scheme,
		'query'  => $query,
	) );
}


/**
 * Extract a component from an URL.
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @param string $url URL to parse and extract component of.
 * @param string $component URL component to extract using constant as in parse_url().
 * @return string extracted component
 */
function rocket_extract_url_component( $url, $component ) {
	return _get_component_from_parsed_url_array( wp_parse_url( $url ), $component );
}

/**
 * Returns paths used for cache busting
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @param string $filename name of the cache busting file.
 * @param string $extension file extension.
 * @return array Array of paths used for cache busting
 */
function rocket_get_cache_busting_paths( $filename, $extension ) {
	$blog_id                = get_current_blog_id();
	$cache_busting_path     = WP_ROCKET_CACHE_BUSTING_PATH . $blog_id;
	$filename               = rocket_realpath( rtrim( str_replace( array( ' ', '%20' ), '-', $filename ) ) );
	$cache_busting_filepath = $cache_busting_path . $filename;
	$cache_busting_url      = get_rocket_cdn_url( WP_ROCKET_CACHE_BUSTING_URL . $blog_id . $filename, array( 'all', 'css_and_js', $extension ) );

	switch ( $extension ) {
		case 'css':
			/** This filter is documented in inc/functions/minify.php */
			$cache_busting_url = apply_filters( 'rocket_css_url', $cache_busting_url );
			break;
		case 'js':
			/** This filter is documented in inc/functions/minify.php */
			$cache_busting_url = apply_filters( 'rocket_js_url', $cache_busting_url );
			break;
	}

	return array(
		'bustingpath' => $cache_busting_path,
		'filepath'    => $cache_busting_filepath,
		'url'         => $cache_busting_url,
	);
}

/**
 * Returns realpath to file (used for relative path with /../ in it or not-yet existing file)
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @param string $file File to determine realpath for.
 * @return string Resolved file path
 */
function rocket_realpath( $file ) {
	$path = array();

	foreach ( explode( '/', $file ) as $part ) {
		if ( '' === $part || '.' === $part ) {
			continue;
		}

		if ( '..' !== $part ) {
			array_push( $path, $part );
		}
		elseif ( count( $path ) > 0 ) {
			array_pop( $path );
		}
	}

	$prefix = 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) ) ? '' : '/';

	return $prefix . join( '/', $path );
}

/**
 * Converts an URL to an absolute path.
 *
 * @since 2.11.7
 * @author Remy Perona
 *
 * @param string $url   URL to convert.
 * @param array  $hosts An array of possible hosts for the URL.
 * @return string
 */
function rocket_url_to_path( $url, $hosts = '' ) {
	$url_components = get_rocket_parse_url( $url );
	$site_url       = trailingslashit( set_url_scheme( site_url() ) );
	$home_path      = ABSPATH;

	if ( false !== strpos( $url_components['path'], rocket_extract_url_component( WP_CONTENT_URL, PHP_URL_PATH ) ) ) {
		$site_url  = trailingslashit( set_url_scheme( WP_CONTENT_URL ) );
		$home_path = trailingslashit( WP_CONTENT_DIR );
	}

	$site_components = get_rocket_parse_url( $site_url );

	if ( '' === $url_components['host'] ) {
		$url_components['scheme'] = $site_components['scheme'];
		$url_components['host']   = $site_components['host'];
		$url                      = trailingslashit( set_url_scheme( '//' . $url_components['host'], $url_components['scheme'] ) ) . ltrim( $url, '/' );
	}

	if ( isset( $hosts[ $url_components['host'] ] ) && 'home' !== $hosts[ $url_components['host'] ] ) {
		$site_url = trailingslashit( set_url_scheme( '//' . $url_components['host'], $url_components['scheme'] ) );

		if ( $url_components['path'] !== $site_components['path'] ) {
			$site_url .= ltrim( $site_components['path'], '/' );
		}
	}

	$file = str_replace( $site_url, $home_path, rocket_set_internal_url_scheme( $url ) );

	return rocket_realpath( $file );
}

/**
 * Simple helper to get some external URLs.
 *
 * @since  2.10.10
 * @author Grégory Viguier
 *
 * @param  string $target     What we want.
 * @param  array  $query_args An array of query arguments.
 * @return string The URL.
 */
function rocket_get_external_url( $target, $query_args = array() ) {
	$site_url = WP_ROCKET_WEB_MAIN;

	switch ( $target ) {
		case 'support':
			$locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$paths  = array(
				'default' => 'support',
				'fr_FR'   => 'fr/support',
				'fr_CA'   => 'fr/support',
				'it_IT'   => 'it/supporto',
				'de_DE'   => 'de/support',
				'es_ES'   => 'es/soporte',
			);

			$url = isset( $paths[ $locale ] ) ? $paths[ $locale ] : $paths['default'];
			$url = $site_url . $url . '/';
			break;
		case 'account':
			$locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$paths  = array(
				'default' => 'account',
				'fr_FR'   => 'fr/compte',
				'fr_CA'   => 'fr/compte',
				'it_IT'   => 'it/account/',
				'de_DE'   => 'de/konto/',
				'es_ES'   => 'es/cuenta/',
			);

			$url = isset( $paths[ $locale ] ) ? $paths[ $locale ] : $paths['default'];
			$url = $site_url . $url . '/';
			break;
		default:
			$url = $site_url;
	}

	if ( $query_args ) {
		$url = add_query_arg( $query_args, $url );
	}

	return $url;
}
