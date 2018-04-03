<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

// Copied from core for compatibility with WP < 4.6.
// Removed the error triggering because it relies on another WP 4.6 function.
if ( ! function_exists( 'apply_filters_deprecated' ) ) {
	/**
	 * Fires functions attached to a deprecated filter hook.
	 *
	 * When a filter hook is deprecated, the apply_filters() call is replaced with
	 * apply_filters_deprecated(), which triggers a deprecation notice and then fires
	 * the original filter hook.
	 *
	 * @since 4.6.0
	 *
	 * @see _deprecated_hook()
	 *
	 * @param string $tag         The name of the filter hook.
	 * @param array  $args        Array of additional function arguments to be passed to apply_filters().
	 * @param string $version     The version of WordPress that deprecated the hook.
	 * @param string $replacement Optional. The hook that should have been used.
	 * @param string $message     Optional. A message regarding the change.
	 */
	function apply_filters_deprecated( $tag, $args, $version, $replacement = false, $message = null ) {
		if ( ! has_filter( $tag ) ) {
			return $args[0];
		}

		return apply_filters_ref_array( $tag, $args );
	}
}

if ( ! function_exists( 'wp_parse_url' ) ) {
	/**
	 * Copied from core for compatibility with WP < 4.4
	 * A wrapper for PHP's parse_url() function that handles consistency in the return
	 * values across PHP versions.
	 *
	 * PHP 5.4.7 expanded parse_url()'s ability to handle non-absolute url's, including
	 * schemeless and relative url's with :// in the path. This function works around
	 * those limitations providing a standard output on PHP 5.2~5.4+.
	 *
	 * Secondly, across various PHP versions, schemeless URLs starting containing a ":"
	 * in the query are being handled inconsistently. This function works around those
	 * differences as well.
	 *
	 * Error suppression is used as prior to PHP 5.3.3, an E_WARNING would be generated
	 * when URL parsing failed.
	 *
	 * @since 4.4.0
	 * @since 4.7.0 The $component parameter was added for parity with PHP's parse_url().
	 *
	 * @param string $url       The URL to parse.
	 * @param int    $component The specific component to retrieve. Use one of the PHP
	 *                          predefined constants to specify which one.
	 *                          Defaults to -1 (= return all parts as an array).
	 *                          @see http://php.net/manual/en/function.parse-url.php
	 * @return mixed False on parse failure; Array of URL components on success;
	 *               When a specific component has been requested: null if the component
	 *               doesn't exist in the given URL; a sting or - in the case of
	 *               PHP_URL_PORT - integer when it does. See parse_url()'s return values.
	 */
	function wp_parse_url( $url, $component = -1 ) {
		$to_unset = array();
		$url      = strval( $url );

		if ( '//' === substr( $url, 0, 2 ) ) {
			$to_unset[] = 'scheme';
			$url        = 'placeholder:' . $url;
		} elseif ( '/' === substr( $url, 0, 1 ) ) {
			$to_unset[] = 'scheme';
			$to_unset[] = 'host';
			$url        = 'placeholder://placeholder' . $url;
		}

		$parts = @parse_url( $url );

		if ( false === $parts ) {
			// Parsing failure.
			return $parts;
		}

		// Remove the placeholder values.
		foreach ( $to_unset as $key ) {
			unset( $parts[ $key ] );
		}

		return _get_component_from_parsed_url_array( $parts, $component );
	}
}

if ( ! function_exists( '_get_component_from_parsed_url_array' ) ) {
	/**
	 * Copied from core for compatibility with WP < 4.7
	 * Retrieve a specific component from a parsed URL array.
	 *
	 * @internal
	 *
	 * @since 4.7.0
	 *
	 * @param array|false $url_parts The parsed URL. Can be false if the URL failed to parse.
	 * @param int         $component The specific component to retrieve. Use one of the PHP
	 *                               predefined constants to specify which one.
	 *                               Defaults to -1 (= return all parts as an array).
	 *                          @see http://php.net/manual/en/function.parse-url.php
	 * @return mixed False on parse failure; Array of URL components on success;
	 *               When a specific component has been requested: null if the component
	 *               doesn't exist in the given URL; a sting or - in the case of
	 *               PHP_URL_PORT - integer when it does. See parse_url()'s return values.
	 */
	function _get_component_from_parsed_url_array( $url_parts, $component = -1 ) {
		if ( -1 === $component ) {
				return $url_parts;
		}

		$key = _wp_translate_php_url_constant_to_key( $component );
		if ( false !== $key && is_array( $url_parts ) && isset( $url_parts[ $key ] ) ) {
				return $url_parts[ $key ];
		} else {
				return null;
		}
	}
}

if ( ! function_exists( '_wp_translate_php_url_constant_to_key' ) ) {
	/**
	 * Copied from core for compatibility with WP < 4.7
	 * Translate a PHP_URL_* constant to the named array keys PHP uses.
	 *
	 * @internal
	 *
	 * @since 4.7.0
	 *
	 * @see   http://php.net/manual/en/url.constants.php
	 *
	 * @param int $constant PHP_URL_* constant.
	 * @return string|bool The named key or false.
	 */
	function _wp_translate_php_url_constant_to_key( $constant ) {
		$translation = array(
			PHP_URL_SCHEME   => 'scheme',
			PHP_URL_HOST     => 'host',
			PHP_URL_PORT     => 'port',
			PHP_URL_USER     => 'user',
			PHP_URL_PASS     => 'pass',
			PHP_URL_PATH     => 'path',
			PHP_URL_QUERY    => 'query',
			PHP_URL_FRAGMENT => 'fragment',
		);

		if ( isset( $translation[ $constant ] ) ) {
				return $translation[ $constant ];
		} else {
				return false;
		}
	}
}


