<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( ! function_exists( 'do_rocket_callback' ) ) :
	/**
	 * The famous callback, it puts contents in a cache file if buffer length > 255 (IE do not read pages under 255 c. )
	 *
	 * @since 1.0
	 * @since 1.3.0 Add filter rocket_buffer
	 * @since 3.3.0 Deprecated
	 * @deprecated
	 *
	 * @param  string $buffer The buffer content.
	 * @return string         The buffered content.
	 */
	function do_rocket_callback( $buffer ) {
		_deprecated_function( __FUNCTION__ . '()', '3.3', '\WP_Rocket\Buffer\Cache->maybe_process_buffer()' );

		/**
		 * Allow to cache search results
		 *
		 * @since 2.3.8
		 *
		 * @param bool true will force caching search results.
		 */
		$rocket_cache_search = apply_filters( 'rocket_cache_search', false );

		/**
		 * Allow to override the DONOTCACHEPAGE behavior.
		 * To warn conflict with some plugins like Thrive Leads.
		 *
		 * @since 2.5
		 *
		 * @param bool true will force the override.
		 */
		$rocket_override_donotcachepage = apply_filters( 'rocket_override_donotcachepage', false );

		if ( strlen( $buffer ) > 255
			&& ( http_response_code() === 200 ) // only cache 200.
			&& ( function_exists( 'is_404' ) && ! is_404() ) // Don't cache 404.
			&& ( function_exists( 'is_search' ) && ! is_search() || $rocket_cache_search ) // Don't cache search results.
			&& ( ! defined( 'DONOTCACHEPAGE' ) || ! DONOTCACHEPAGE || $rocket_override_donotcachepage ) // Don't cache template that use this constant.
			&& function_exists( 'rocket_mkdir_p' )
		) {
			global $request_uri_path, $rocket_cache_filepath, $is_nginx;

			$footprint = '';
			$is_html   = false;

			if ( preg_match( '/(<\/html>)/i', $buffer ) ) {
				/**
				 * This hook is used for:
				 * - Add width and height attributes on images
				 * - Deferred JavaScript files
				 * - DNS Prefechting
				 * - Minification HTML/CSS/JavaScript
				 * - CDN
				 * - LazyLoad
				 */
				$buffer = apply_filters( 'rocket_buffer', $buffer );

				$is_html = true;
			}

			/**
			 * Allow to the generate the caching file
			 *
			 * @since 2.5
			 *
			 * @param bool true will force the caching file generation.
			 */
			if ( apply_filters( 'do_rocket_generate_caching_files', true ) ) {
				// Create cache folders of the request uri.
				rocket_mkdir_p( $request_uri_path );

				if ( $is_html ) {
					$footprint = get_rocket_footprint();
				}

				// Save the cache file.
				rocket_put_content( $rocket_cache_filepath, $buffer . $footprint );

				if ( get_rocket_option( 'do_caching_mobile_files' ) ) {
					if ( $is_nginx ) {
						// Create a hidden empty file for mobile detection on NGINX with the Rocket NGINX configuration.
						$nginx_mobile_detect_file = $request_uri_path . '/.mobile-active';

						if ( ! rocket_direct_filesystem()->exists( $nginx_mobile_detect_file ) ) {
							rocket_direct_filesystem()->touch( $nginx_mobile_detect_file );
						}
					}
				}

				if ( function_exists( 'gzencode' ) ) {
					rocket_put_content( $rocket_cache_filepath . '_gzip', gzencode( $buffer . $footprint, apply_filters( 'rocket_gzencode_level_compression', 3 ) ) );
				}

				// Send headers with the last modified time of the cache file.
				if ( file_exists( $rocket_cache_filepath ) ) {
					header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $rocket_cache_filepath ) ) . ' GMT' );
				}
			}

			if ( $is_html ) {
				$footprint = get_rocket_footprint( false );
			}

			$buffer = $buffer . $footprint;
		}

		return $buffer;
	}
endif;

if ( ! function_exists( 'rocket_serve_cache_file' ) ) :
	/**
	 * Serve the cache file if exist.
	 *
	 * @since 2.0
	 * @since 2.11 Serve the gzipped cache file if possible.
	 * @since 3.3.0 Deprecated
	 * @deprecated
	 *
	 * @param string $rocket_cache_filepath Path to the cache file.
	 */
	function rocket_serve_cache_file( $rocket_cache_filepath ) {
		_deprecated_function( __FUNCTION__ . '()', '3.3' );

		$rocket_cache_filepath_gzip = $rocket_cache_filepath . '_gzip';

		// Check if cache file exist.
		if ( isset( $_SERVER['HTTP_ACCEPT_ENCODING'] ) && false !== strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) && file_exists( $rocket_cache_filepath_gzip ) && is_readable( $rocket_cache_filepath_gzip ) ) {
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $rocket_cache_filepath_gzip ) ) . ' GMT' );

			// Getting If-Modified-Since headers sent by the client.
			if ( function_exists( 'apache_request_headers' ) ) {
				$headers                = apache_request_headers();
				$http_if_modified_since = ( isset( $headers['If-Modified-Since'] ) ) ? $headers['If-Modified-Since'] : '';
			} else {
				$http_if_modified_since = ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : '';
			}

			// Checking if the client is validating his cache and if it is current.
			if ( $http_if_modified_since && ( strtotime( $http_if_modified_since ) === @filemtime( $rocket_cache_filepath_gzip ) ) ) {
				// Client's cache is current, so we just respond '304 Not Modified'.
				header( $_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304 );
				header( 'Expires: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
				header( 'Cache-Control: no-cache, must-revalidate' );
				exit;
			}

			// Serve the cache if file isn't store in the client browser cache.
			readgzfile( $rocket_cache_filepath_gzip );
			exit;
		}

		if ( file_exists( $rocket_cache_filepath ) && is_readable( $rocket_cache_filepath ) ) {
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $rocket_cache_filepath ) ) . ' GMT' );

			// Getting If-Modified-Since headers sent by the client.
			if ( function_exists( 'apache_request_headers' ) ) {
				$headers                = apache_request_headers();
				$http_if_modified_since = ( isset( $headers['If-Modified-Since'] ) ) ? $headers['If-Modified-Since'] : '';
			} else {
				$http_if_modified_since = ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : '';
			}

			// Checking if the client is validating his cache and if it is current.
			if ( $http_if_modified_since && ( strtotime( $http_if_modified_since ) === @filemtime( $rocket_cache_filepath ) ) ) {
				// Client's cache is current, so we just respond '304 Not Modified'.
				header( $_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304 );
				header( 'Expires: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
				header( 'Cache-Control: no-cache, must-revalidate' );
				exit;
			}

			// Serve the cache if file isn't store in the client browser cache.
			readfile( $rocket_cache_filepath );
			exit;
		}
	}
endif;

if ( ! function_exists( 'rocket_define_donotoptimize_constant' ) ) :
	/**
	 * Declares and sets value of constant preventing Optimizations
	 *
	 * @since  2.11
	 * @since  3.3.0 Deprecated
	 * @author Remy Perona
	 * @deprecated
	 *
	 * @param bool $value true or false.
	 */
	function rocket_define_donotoptimize_constant( $value ) {
		_deprecated_function( __FUNCTION__ . '()', '3.3', '\WP_Rocket\Buffer\Abstract_Buffer->define_donotoptimize()' );

		if ( ! defined( 'DONOTROCKETOPTIMIZE' ) ) {
			define( 'DONOTROCKETOPTIMIZE', (bool) $value );
		}
	}
endif;

if ( ! function_exists( 'rocket_urlencode_lowercase' ) ) :
	/**
	 * Force lowercase on encoded url strings from different alphabets to prevent issues on some hostings.
	 *
	 * @since 2.7
	 * @since 3.3.0 Deprecated
	 * @deprecated
	 *
	 * @param string $matches Cache path.
	 * @return string cache path in lowercase
	 */
	function rocket_urlencode_lowercase( $matches ) {
		_deprecated_function( __FUNCTION__ . '()', '3.3', '\WP_Rocket\Buffer\Cache->reset_lowercase()' );

		return strtolower( $matches[0] );
	}
endif;

if ( ! function_exists( 'rocket_get_ip' ) ) :
	/**
	 * Get the IP address from which the user is viewing the current page.
	 *
	 * @since 2.7.3
	 * @since 3.3.0 Deprecated
	 * @deprecated
	 */
	function rocket_get_ip() {
		_deprecated_function( __FUNCTION__ . '()', '3.3', '\WP_Rocket\Buffer\Tests->get_ip()' );

		$keys = array(
			'HTTP_CF_CONNECTING_IP', // CF = CloudFlare.
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_X_REAL_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) ) {
				$ip = explode( ',', $_SERVER[ $key ] );
				$ip = end( $ip );

				if ( false !== filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}
endif;
