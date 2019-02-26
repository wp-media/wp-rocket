<?php
namespace WP_Rocket\Buffer;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Handle page cache.
 *
 * @since  3.3
 * @author Grégory Viguier
 */
class Cache extends Abstract_Buffer {

	/**
	 * Process identifier used by the logger.
	 *
	 * @var    string
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $process_id = 'caching process';

	/**
	 * List of the tests to do.
	 *
	 * @var    array
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $tests_array = [
		'query_string',
		'ssl',
		'uri',
		'rejected_cookie',
		'mandatory_cookie',
		'user_agent',
		'mobile',
		'donotcachepage',
		'wp_404',
		'search',
	];

	/**
	 * Path to the directory containing the cache files.
	 *
	 * @var    string
	 * @since  3.3
	 * @access private
	 * @author Grégory Viguier
	 */
	private $cache_dir_path;

	/**
	 * Constructor.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param array $args  {
	 *     An array of arguments.
	 *
	 *     @type string $cache_dir_path  Path to the directory containing the cache files.
	 *     @type string $config_dir_path Path to the directory containing the config files.
	 * }
	 */
	public function __construct( array $args ) {
		$this->cache_dir_path = rtrim( $args['cache_dir_path'], '/' ) . '/';

		parent::__construct( $args );

		$this->log( 'CACHING PROCESS STARTED.', [], 'info' );
	}

	/** ----------------------------------------------------------------------------------------- */
	/** CACHE =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Serve the cache file if it exists. If not, init the buffer.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 */
	public function maybe_init_process() {
		if ( ! $this->tests->can_init_process() ) {
			$this->log_last_test_error();
			return;
		}

		/**
		 * Serve the cache file if it exists.
		 */
		$cache_filepath = $this->get_cache_path();

		$this->log(
			'Looking for cache file.',
			[
				'path' => $cache_filepath,
			]
		);

		$cache_filepath_gzip = $cache_filepath . '_gzip';
		$accept_encoding     = $this->tests->get_server_input( 'HTTP_ACCEPT_ENCODING' );

		// Check if cache file exist.
		if ( $accept_encoding && false !== strpos( $accept_encoding, 'gzip' ) && is_readable( $cache_filepath_gzip ) ) {
			$this->serve_cache_file_type( $cache_filepath_gzip, true );
		}

		if ( is_readable( $cache_filepath ) ) {
			$this->serve_cache_file_type( $cache_filepath, false );
		}

		/**
		 * No cache file yet: launch caching process.
		 */
		$this->log(
			'Start buffer.',
			[
				'path' => $cache_filepath,
			]
		);

		ob_start( [ $this, 'maybe_process_buffer' ] );
	}

	/**
	 * Serve a cache file.
	 *
	 * @since  3.3
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param string $cache_filepath Path to the cache file.
	 * @param bool   $is_gzip        True for gzip. False otherwise.
	 */
	private function serve_cache_file_type( $cache_filepath, $is_gzip ) {
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $cache_filepath ) ) . ' GMT' );

		if ( $is_gzip ) {
			header( 'Content-Encoding: gzip' );
		}

		// Getting If-Modified-Since headers sent by the client.
		if ( function_exists( 'apache_request_headers' ) ) {
			$headers                = apache_request_headers();
			$http_if_modified_since = isset( $headers['If-Modified-Since'] ) ? $headers['If-Modified-Since'] : '';
		} else {
			$http_if_modified_since = $this->tests->get_server_input( 'HTTP_IF_MODIFIED_SINCE', '' );
		}

		// Checking if the client is validating his cache and if it is current.
		if ( $http_if_modified_since && ( strtotime( $http_if_modified_since ) === @filemtime( $cache_filepath ) ) ) {
			// Client's cache is current, so we just respond '304 Not Modified'.
			header( $this->tests->get_server_input( 'SERVER_PROTOCOL', '' ) . ' 304 Not Modified', true, 304 );

			$this->log(
				'Serving `304` ' . ( $is_gzip ? ' gzip' : '' ) . 'cache file.',
				[
					'path'     => $cache_filepath,
					'modified' => $http_if_modified_since,
				],
				'info'
			);
			exit;
		}

		// Serve the cache if file isn't store in the client browser cache.
		readfile( $cache_filepath );

		$this->log(
			'Serving ' . ( $is_gzip ? ' gzip' : '' ) . 'cache file.',
			[
				'path'     => $cache_filepath,
				'modified' => $http_if_modified_since,
			],
			'info'
		);
		exit;
	}

	/**
	 * Maybe cache the page content.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $buffer The buffer content.
	 * @return string         The buffered content.
	 */
	public function maybe_process_buffer( $buffer ) {
		global $is_nginx;

		if ( ! $this->tests->can_process_buffer( $buffer ) ) {
			$this->log_last_test_error();
			return $buffer;
		}

		$footprint = '';
		$is_html   = $this->is_html( $buffer );

		if ( ! static::can_generate_caching_files() ) {
			// Not allowed to generate cache files.
			if ( $is_html ) {
				$footprint = get_rocket_footprint( false );
			}

			$this->log(
				'Page not cached by filter.',
				[
					'filter' => 'do_rocket_generate_caching_files',
				]
			);
			return $buffer . $footprint;
		}

		$cache_filepath = $this->get_cache_path();
		$cache_dir_path = dirname( $cache_filepath );

		// Create cache folders.
		rocket_mkdir_p( $cache_dir_path );

		if ( $is_html ) {
			$footprint = get_rocket_footprint();
		}

		// Save the cache file.
		rocket_put_content( $cache_filepath, $buffer . $footprint );

		if ( get_rocket_option( 'do_caching_mobile_files' ) ) {
			if ( $is_nginx ) {
				// Create a hidden empty file for mobile detection on NGINX with the Rocket NGINX configuration.
				$nginx_mobile_detect_file = $cache_dir_path . '/.mobile-active';

				if ( ! rocket_direct_filesystem()->exists( $nginx_mobile_detect_file ) ) {
					rocket_direct_filesystem()->touch( $nginx_mobile_detect_file );
				}
			}
		}

		if ( function_exists( 'gzencode' ) ) {
			rocket_put_content( $cache_filepath . '_gzip', gzencode( $buffer . $footprint, apply_filters( 'rocket_gzencode_level_compression', 3 ) ) );
		}

		// Send headers with the last modified time of the cache file.
		if ( file_exists( $cache_filepath ) ) {
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $cache_filepath ) ) . ' GMT' );
		}

		if ( $is_html ) {
			$footprint = get_rocket_footprint( false );
		}

		$this->log(
			'Page cached.',
			[
				'path' => $cache_filepath,
			],
			'info'
		);

		return $buffer . $footprint;
	}

	/**
	 * Get the path to the cache file.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_cache_path() {
		static $request_uri_path;

		if ( isset( $request_uri_path ) ) {
			return $request_uri_path;
		}

		$host = $this->tests->get_host();

		if ( $this->tests->get_config( 'url_no_dots' ) ) {
			$host = str_replace( '.', '_', $host );
		}

		$request_uri              = $this->tests->get_clean_request_uri();
		$cookie_hash              = $this->tests->get_config( 'cookie_hash' );
		$logged_in_cookie         = $this->tests->get_config( 'logged_in_cookie' );
		$cookies                  = $this->tests->get_cookies();
		$logged_in_cookie_no_hash = str_replace( $cookie_hash, '', $logged_in_cookie );

		// Get cache folder of host name.
		if ( $logged_in_cookie && isset( $cookies[ $logged_in_cookie ] ) && ! $this->tests->has_rejected_cookie( $logged_in_cookie_no_hash ) ) {
			if ( $this->tests->get_config( 'common_cache_logged_users' ) ) {
				$request_uri_path = $this->cache_dir_path . $host . '-loggedin' . rtrim( $request_uri, '/' );
			} else {
				$user_key = explode( '|', $cookies[ $logged_in_cookie ] );
				$user_key = reset( $user_key );
				$user_key = $user_key . '-' . $this->tests->get_config( 'secret_cache_key' );

				// Get cache folder of host name.
				$request_uri_path = $this->cache_dir_path . $host . '-' . $user_key . rtrim( $request_uri, '/' );
			}
		}
		else {
			$request_uri_path = $this->cache_dir_path . $host . rtrim( $request_uri, '/' );
		}

		$filename = 'index';

		$cache_mobile              = $this->tests->get_config( 'cache_mobile' );
		$do_caching_mobile_files   = $this->tests->get_config( 'do_caching_mobile_files' );
		$cache_mobile_files_tablet = $this->tests->get_config( 'cache_mobile_files_tablet' );

		// Rename the caching filename for mobile.
		if ( $cache_mobile && $do_caching_mobile_files && $cache_mobile_files_tablet && class_exists( 'Rocket_Mobile_Detect' ) ) {
			$detect = new \Rocket_Mobile_Detect();

			if ( $detect->isMobile() && ! $detect->isTablet() && 'desktop' === $cache_mobile_files_tablet || ( $detect->isMobile() || $detect->isTablet() ) && 'mobile' === $cache_mobile_files_tablet ) {
				$filename .= '-mobile';
			}
		}

		// Rename the caching filename for SSL URLs.
		if ( is_ssl() && $this->tests->get_config( 'cache_ssl' ) ) {
			$filename .= '-https';
		}

		// Rename the caching filename depending to dynamic cookies.
		$cache_dynamic_cookies = $this->tests->get_config( 'cache_dynamic_cookies' );

		if ( $cache_dynamic_cookies ) {
			foreach ( $cache_dynamic_cookies as $key => $cookie_name ) {
				if ( is_array( $cookie_name ) && isset( $cookies[ $key ] ) ) {
					foreach ( $cookie_name as $cookie_key ) {
						if ( '' !== $cookies[ $key ][ $cookie_key ] ) {
							$cache_key = $cookies[ $key ][ $cookie_key ];
							$cache_key = preg_replace( '/[^a-z0-9_\-]/i', '-', $cache_key );
							$filename .= '-' . $cache_key;
						}
					}
					continue;
				}

				if ( isset( $cookies[ $cookie_name ] ) && '' !== $cookies[ $cookie_name ] ) {
					$cache_key = $cookies[ $cookie_name ];
					$cache_key = preg_replace( '/[^a-z0-9_\-]/i', '-', $cache_key );
					$filename .= '-' . $cache_key;
				}
			}
		}

		// Caching file path.
		$request_uri_path = preg_replace_callback( '/%[0-9A-F]{2}/', [ $this, 'reset_lowercase' ], $request_uri_path );
		// Directories in Windows can't contain question marks.
		$request_uri_path  = str_replace( '?', '_', $request_uri_path );
		$request_uri_path .= '/' . $filename . '.html';

		return $request_uri_path;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** VARIOUS TOOLS =========================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Tell if generating cache files is allowed.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public static function can_generate_caching_files() {
		/**
		 * Allow to the generate the caching file.
		 *
		 * @since 2.5
		 *
		 * @param bool True will force the cache file generation.
		 */
		return (bool) apply_filters( 'do_rocket_generate_caching_files', true );
	}

	/**
	 * Force lowercase on encoded url strings from different alphabets to prevent issues on some hostings.
	 *
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @param  array $matches Cache path.
	 * @return string         Cache path in lowercase.
	 */
	protected function reset_lowercase( $matches ) {
		return strtolower( $matches[0] );
	}
}
