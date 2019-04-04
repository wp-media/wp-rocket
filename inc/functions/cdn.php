<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Get CNAMES hosts
 *
 * @since 2.3
 *
 * @param  string $zones CNAMES zones.
 * @return array $hosts CNAMES hosts
 */
function get_rocket_cnames_host( $zones = array( 'all' ) ) {
	$hosts = array();

	$cnames = get_rocket_cdn_cnames( $zones );
	if ( $cnames ) {
		foreach ( $cnames as $cname ) {
			$cname   = rocket_add_url_protocol( $cname );
			$hosts[] = rocket_extract_url_component( $cname, PHP_URL_HOST );
		}
	}

	return $hosts;
}

/**
 * Get an URL with one of CNAMES added in options
 *
 * @since 2.1
 *
 * @param  string $url The URL to parse.
 * @param  array  $zone (default: array( 'all' )).
 * @return string $url The URL with one of CNAMES
 */
function get_rocket_cdn_url( $url, $zone = array( 'all' ) ) {
	$cnames             = get_rocket_cdn_cnames( $zone );
	$wp_content_dirname = ltrim( str_replace( home_url(), '', WP_CONTENT_URL ), '/' ) . '/';
	$home               = home_url();

	if ( ( defined( 'DONOTCDN' ) && DONOTCDN ) || (int) get_rocket_option( 'cdn' ) === 0 || empty( $cnames ) || is_rocket_post_excluded_option( 'cdn' ) ) {
		return $url;
	}

	$parse_url          = get_rocket_parse_url( $url );
	$parse_url['query'] = ! empty( $parse_url['query'] ) ? '?' . $parse_url['query'] : '';

	// Exclude rejected & external files from CDN.
	$rejected_files = get_rocket_cdn_reject_files();
	if ( ( ! empty( $rejected_files ) && preg_match( '#(' . $rejected_files . ')#', $parse_url['path'] ) ) || ( ! empty( $parse_url['scheme'] ) && rocket_extract_url_component( home_url(), PHP_URL_HOST ) !== $parse_url['host'] && ! in_array( $parse_url['host'], get_rocket_i18n_host(), true ) ) ) {
		return $url;
	}

	if ( empty( $parse_url['scheme'] ) ) {
		// Check if the URL is external.
		if ( strpos( $parse_url['path'], $home ) === false && ! preg_match( '#(' . $wp_content_dirname . '|wp-includes)#', $parse_url['path'] ) ) {
			return $url;
		} else {
			$parse_url['path'] = str_replace( $home, '', ltrim( $parse_url['path'], '//' ) );
		}
	}

	$url = untrailingslashit( $cnames[ ( abs( crc32( $parse_url['path'] ) ) % count( $cnames ) ) ] ) . '/' . ltrim( $parse_url['path'], '/' ) . $parse_url['query'];
	$url = rocket_add_url_protocol( $url );
	return $url;
}

/**
 * Wrapper of get_rocket_cdn_url() and print result
 *
 * @since 2.1
 *
 * @param string $url The URL to parse.
 * @param array  $zone (default: array( 'all' )).
 */
function rocket_cdn_url( $url, $zone = array( 'all' ) ) {
	echo get_rocket_cdn_url( $url, $zone );
}

/**
 * Apply CDN on CSS properties (background, background-image, @import, src:url (fonts))
 *
 * @since 2.6
 *
 * @param  string $buffer file content.
 * @return string modified file content
 */
function rocket_cdn_css_properties( $buffer ) {
	$zone   = array(
		'all',
		'images',
		'css_and_js',
		'css',
	);
	$cnames = get_rocket_cdn_cnames( $zone );

	/**
	 * Filters the application of the CDN on CSS properties
	 *
	 * @since 2.6
	 *
	 * @param bool true to apply CDN to properties, false otherwise
	 */
	$do_rocket_cdn_css_properties = apply_filters( 'do_rocket_cdn_css_properties', true );

	if ( ! get_rocket_option( 'cdn' ) || ! $cnames || ! $do_rocket_cdn_css_properties ) {
		return $buffer;
	}

	preg_match_all( '/url\((?![\'"]?data)([^\)]+)\)/i', $buffer, $matches );

	if ( is_array( $matches ) ) {
		$i = 0;
		foreach ( $matches[1] as $url ) {
			$url = trim( $url, " \t\n\r\0\x0B\"'" );
			/**
			 * Filters the URL of the CSS property
			 *
			 * @since 2.8
			 *
			 * @param string $url URL of the CSS property
			 */
			$url      = get_rocket_cdn_url( apply_filters( 'rocket_cdn_css_properties_url', $url ), $zone );
			$property = str_replace( $matches[1][ $i ], $url, $matches[0][ $i ] );
			$buffer   = str_replace( $matches[0][ $i ], $property, $buffer );

			$i++;
		}
	}

	return $buffer;
}

/**
 * Apply CDN on custom data attributes.
 *
 * @since 2.5.5
 *
 * @param   string $html Original Output.
 * @return  string $html Output that will be printed
 */
function rocket_add_cdn_on_custom_attr( $html ) {
	if ( preg_match( '/(data-lazy-src|data-lazyload|data-src|data-retina)=[\'"]?([^\'"\s>]+)[\'"]/i', $html, $matches ) ) {
		$html = str_replace( $matches[2], get_rocket_cdn_url( $matches[2], array( 'all', 'images' ) ), $html );
	}

	return $html;
}
