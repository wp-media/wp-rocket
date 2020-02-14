<?php

defined( 'ABSPATH' ) || exit;

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

	return wp_parse_url( $file, PHP_URL_PATH );
}

/**
 * Clean Never Cache URL(s) bad wildcards
 *
 * @since 3.4.2
 * @author Soponar Cristina
 *
 * @param  string $path URL which needs to be cleaned.
 * @return bool\string  false if $path is empty or cleaned URL
 */
function rocket_clean_wildcards( $path ) {
	if ( ! $path ) {
		return false;
	}

	$path_components = explode( '/', $path );
	$arr             = [
		'.*'   => '(.*)',
		'*'    => '(.*)',
		'(*)'  => '(.*)',
		'(.*)' => '(.*)',
	];

	foreach ( $path_components as &$path_component ) {
		$path_component = strtr( $path_component, $arr );
	}
	$path = implode( '/', $path_components );

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
 * Sanitize and validate JS files to exclude from the minification.
 *
 * @since  3.3.7
 * @author Remy Perona
 * @author Grégory Viguier
 *
 * @param  string $file filepath to sanitize.
 * @return string
 */
function rocket_validate_js( $file ) {
	if ( rocket_is_internal_file( $file ) ) {
		$file = trim( $file );
		$file = rocket_clean_exclude_file( $file );
		$file = rocket_sanitize_js( $file );

		return $file;
	}

	return sanitize_text_field( \rocket_remove_url_protocol( strtok( $file, '?' ) ) );
}

/**
 * Check if the passed value is an internal URL (default domain or CDN/Multilingual).
 *
 * @since  3.3.7
 * @author Remy Perona
 * @author Grégory Viguier
 *
 * @param  string $file string to test.
 * @return bool
 */
function rocket_is_internal_file( $file ) {
	$file_host = wp_parse_url( $file, PHP_URL_HOST );

	if ( ! $file_host ) {
		return false;
	}

	/**
	 * Filters the allowed hosts for optimization
	 *
	 * @since  3.4
	 * @author Remy Perona
	 *
	 * @param array $hosts Allowed hosts.
	 * @param array $zones Zones to check available hosts.
	 */
	$hosts   = apply_filters( 'rocket_cdn_hosts', [], [ 'all', 'css_and_js', 'css', 'js' ] );
	$hosts[] = wp_parse_url( WP_CONTENT_URL, PHP_URL_HOST );
	$langs   = get_rocket_i18n_uri();

	// Get host for all langs.
	if ( $langs ) {
		foreach ( $langs as $lang ) {
			$hosts[] = wp_parse_url( $lang, PHP_URL_HOST );
		}
	}

	$hosts_index = array_flip( array_unique( $hosts ) );

	return isset( $hosts_index[ $file_host ] );
}

/**
 * Sanitize a setting value meant for a textarea.
 *
 * @since  3.3.7
 * @author Grégory Viguier
 *
 * @param  string       $field The field’s name. Can be one of the following:
 *                             'exclude_css', 'exclude_inline_js', 'exclude_js', 'cache_reject_uri',
 *                             'cache_reject_ua', 'cache_purge_pages', 'cdn_reject_files'.
 * @param  array|string $value The value to sanitize.
 * @return array|null
 */
function rocket_sanitize_textarea_field( $field, $value ) {
	$fields = [
		'cache_purge_pages'    => [ 'esc_url', 'rocket_clean_exclude_file', 'rocket_clean_wildcards' ], // Pattern.
		'cache_reject_cookies' => [ 'rocket_sanitize_key' ],
		'cache_reject_ua'      => [ 'rocket_sanitize_ua', 'rocket_clean_wildcards' ], // Pattern.
		'cache_reject_uri'     => [ 'esc_url', 'rocket_clean_exclude_file', 'rocket_clean_wildcards' ], // Pattern.
		'cache_query_strings'  => [ 'rocket_sanitize_key' ],
		'cdn_reject_files'     => [ 'rocket_clean_exclude_file', 'rocket_clean_wildcards' ], // Pattern.
		'dns_prefetch'         => [ 'esc_url' ],
		'exclude_css'          => [ 'rocket_clean_exclude_file', 'rocket_sanitize_css', 'rocket_clean_wildcards' ], // Pattern.
		'exclude_inline_js'    => [ 'sanitize_text_field' ], // Pattern.
		'exclude_js'           => [ 'rocket_validate_js', 'rocket_clean_wildcards' ], // Pattern.
	];

	if ( ! isset( $fields[ $field ] ) ) {
		return null;
	}

	$sanitizations = $fields[ $field ];

	if ( ! is_array( $value ) ) {
		$value = explode( "\n", $value );
	}

	$value = array_map( 'trim', $value );
	$value = array_filter( $value );

	if ( ! $value ) {
		return [];
	}

	// Sanitize.
	foreach ( $sanitizations as $sanitization ) {
		$value = array_map( $sanitization, $value );
	}

	return array_unique( $value );
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
	$url = str_replace( [ 'http://', 'https://' ], '', $url );

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
function get_rocket_parse_url( $url ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
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

	$url      = wp_parse_url( $encoded_url );
	$host     = isset( $url['host'] ) ? strtolower( urldecode( $url['host'] ) ) : '';
	$path     = isset( $url['path'] ) ? urldecode( $url['path'] ) : '';
	$scheme   = isset( $url['scheme'] ) ? urldecode( $url['scheme'] ) : '';
	$query    = isset( $url['query'] ) ? urldecode( $url['query'] ) : '';
	$fragment = isset( $url['fragment'] ) ? urldecode( $url['fragment'] ) : '';

	/**
	 * Filter components of an URL
	 *
	 * @since 2.2
	 *
	 * @param array Components of an URL
	*/
	return apply_filters(
		'rocket_parse_url',
		[
			'host'     => $host,
			'path'     => $path,
			'scheme'   => $scheme,
			'query'    => $query,
			'fragment' => $fragment,
		]
	);
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
	$filename               = rocket_realpath( rtrim( str_replace( [ ' ', '%20' ], '-', $filename ) ) );
	$cache_busting_filepath = $cache_busting_path . $filename;
	$cache_busting_url      = WP_ROCKET_CACHE_BUSTING_URL . $blog_id . $filename;

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

	return [
		'bustingpath' => $cache_busting_path,
		'filepath'    => $cache_busting_filepath,
		'url'         => $cache_busting_url,
	];
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
	$path = [];

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
 * @return string|bool
 */
function rocket_url_to_path( $url, $hosts = '' ) {
	$root_dir = trailingslashit( dirname( WP_CONTENT_DIR ) );
	$root_url = str_replace( wp_basename( WP_CONTENT_DIR ), '', content_url() );
	$url_host = wp_parse_url( $url, PHP_URL_HOST );

	// relative path.
	if ( null === $url_host ) {
		$subdir_levels = substr_count( preg_replace( '/https?:\/\//', '', site_url() ), '/' );
		$url           = trailingslashit( site_url() . str_repeat( '/..', $subdir_levels ) ) . ltrim( $url, '/' );
	}

	// CDN.
	if ( get_rocket_option( 'cdn' ) && isset( $hosts[ $url_host ] ) && 'home' !== $hosts[ $url_host ] ) {
		$url = str_replace( $url_host, wp_parse_url( site_url(), PHP_URL_HOST ), $url );
	}

	$root_url = preg_replace( '/^https?:/', '', $root_url );
	$url      = preg_replace( '/^https?:/', '', $url );
	$file     = str_replace( $root_url, $root_dir, $url );
	$file     = rocket_realpath( $file );
	/**
	 * Filters the absolute path to the asset file
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param string $file Absolute path to the file.
	 * @param string $url  URL of the asset.
	 */
	$file = apply_filters( 'rocket_url_to_path', $file, $url );

	if ( ! rocket_direct_filesystem()->is_readable( $file ) ) {
		return false;
	}

	return $file;
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
function rocket_get_external_url( $target, $query_args = [] ) {
	$site_url = WP_ROCKET_WEB_MAIN;

	switch ( $target ) {
		case 'support':
			$locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$paths  = [
				'default' => 'support',
				'fr_FR'   => 'fr/support',
				'fr_CA'   => 'fr/support',
				'it_IT'   => 'it/supporto',
				'de_DE'   => 'de/support',
				'es_ES'   => 'es/soporte',
			];

			$url = isset( $paths[ $locale ] ) ? $paths[ $locale ] : $paths['default'];
			$url = $site_url . $url . '/';
			break;
		case 'account':
			$locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$paths  = [
				'default' => 'account',
				'fr_FR'   => 'fr/compte',
				'fr_CA'   => 'fr/compte',
				'it_IT'   => 'it/account/',
				'de_DE'   => 'de/konto/',
				'es_ES'   => 'es/cuenta/',
			];

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
