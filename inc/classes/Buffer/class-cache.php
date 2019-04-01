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
	 * Tests instance
	 *
	 * @var Tests
	 */
	protected $tests;

	/**
	 * Config instance
	 *
	 * @var Config
	 */
	private $config;

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
	 * @param Tests  $tests Tests instance.
	 * @param Config $config Config instance.
	 * @param array  $args  {
	 *     An array of arguments.
	 *
	 *     @type string $cache_dir_path  Path to the directory containing the cache files.
	 * }
	 */
	public function __construct( Tests $tests, Config $config, array $args ) {
		$this->config         = $config;
		$this->cache_dir_path = rtrim( $args['cache_dir_path'], '/' ) . '/';

		parent::__construct( $tests );

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
			$this->define_donotoptimize_true();
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
		$accept_encoding     = $this->config->get_server_input( 'HTTP_ACCEPT_ENCODING' );

		// Check if cache file exist.
		if ( $accept_encoding && false !== strpos( $accept_encoding, 'gzip' ) && is_readable( $cache_filepath_gzip ) ) {
			$this->serve_gzip_cache_file( $cache_filepath_gzip );
		}

		if ( is_readable( $cache_filepath ) ) {
			$this->serve_cache_file( $cache_filepath );
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
	 */
	private function serve_cache_file( $cache_filepath ) {
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $cache_filepath ) ) . ' GMT' );

		$if_modified_since = $this->get_if_modified_since();

		// Checking if the client is validating his cache and if it is current.
		if ( $if_modified_since && ( strtotime( $if_modified_since ) === @filemtime( $cache_filepath ) ) ) {
			// Client's cache is current, so we just respond '304 Not Modified'.
			header( $this->config->get_server_input( 'SERVER_PROTOCOL', '' ) . ' 304 Not Modified', true, 304 );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
			header( 'Cache-Control: no-cache, must-revalidate' );

			$this->log(
				'Serving `304` cache file.',
				[
					'path'     => $cache_filepath,
					'modified' => $if_modified_since,
				],
				'info'
			);
			exit;
		}

		// Serve the cache if file isn't store in the client browser cache.
		readfile( $cache_filepath );

		$this->log(
			'Serving cache file.',
			[
				'path'     => $cache_filepath,
				'modified' => $if_modified_since,
			],
			'info'
		);
		exit;
	}

	/**
	 * Serve a gzipped cache file.
	 *
	 * @since  3.3
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param string $cache_filepath Path to the gzip cache file.
	 */
	private function serve_gzip_cache_file( $cache_filepath ) {
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $cache_filepath ) ) . ' GMT' );

		$if_modified_since = $this->get_if_modified_since();

		// Checking if the client is validating his cache and if it is current.
		if ( $if_modified_since && ( strtotime( $if_modified_since ) === @filemtime( $cache_filepath ) ) ) {
			// Client's cache is current, so we just respond '304 Not Modified'.
			header( $this->config->get_server_input( 'SERVER_PROTOCOL', '' ) . ' 304 Not Modified', true, 304 );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
			header( 'Cache-Control: no-cache, must-revalidate' );

			$this->log(
				'Serving `304` gzip cache file.',
				[
					'path'     => $cache_filepath,
					'modified' => $if_modified_since,
				],
				'info'
			);
			exit;
		}

		// Serve the cache if file isn't store in the client browser cache.
		readgzfile( $cache_filepath );

		$this->log(
			'Serving gzip cache file.',
			[
				'path'     => $cache_filepath,
				'modified' => $if_modified_since,
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
		if ( ! $this->tests->can_process_buffer( $buffer ) ) {
			$this->log_last_test_error();
			return $buffer;
		}

		$footprint = '';
		$is_html   = $this->is_html( $buffer );

		if ( ! static::can_generate_caching_files() ) {
			// Not allowed to generate cache files.
			if ( $is_html ) {
				$footprint = $this->get_rocket_footprint();
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
			$footprint = $this->get_rocket_footprint( time() );
		}

		// Save the cache file.
		rocket_put_content( $cache_filepath, $buffer . $footprint );

		if ( function_exists( 'gzencode' ) ) {
			rocket_put_content( $cache_filepath . '_gzip', gzencode( $buffer . $footprint, apply_filters( 'rocket_gzencode_level_compression', 3 ) ) );
		}

		$this->maybe_create_nginx_mobile_file( $cache_dir_path );

		// Send headers with the last modified time of the cache file.
		if ( file_exists( $cache_filepath ) ) {
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $cache_filepath ) ) . ' GMT' );
		}

		if ( $is_html ) {
			$footprint = $this->get_rocket_footprint();
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

		$cookies          = $this->tests->get_cookies();
		$request_uri_path = $this->get_request_cache_path( $cookies );
		$filename         = 'index';

		$filename = $this->maybe_mobile_filename( $filename );

		// Rename the caching filename for SSL URLs.
		if ( is_ssl() && $this->config->get_config( 'cache_ssl' ) ) {
			$filename .= '-https';
		}

		$filename = $this->maybe_dynamic_cookies_filename( $filename, $cookies );

		// Ensure proper formatting of the path.
		$request_uri_path = preg_replace_callback( '/%[0-9A-F]{2}/', [ $this, 'reset_lowercase' ], $request_uri_path );
		// Directories in Windows can't contain question marks.
		$request_uri_path = str_replace( '?', '_', $request_uri_path );
		// Limit filename max length to 255 characters.
		$request_uri_path .= '/' . substr( $filename, 0, 250 ) . '.html';

		return $request_uri_path;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** VARIOUS TOOLS =========================================================================== */
	/** ----------------------------------------------------------------------------------------- */
	/**
	 * Declares and sets value of constant preventing Optimizations.
	 *
	 * @since  3.3
	 * @access private
	 * @author Grégory Viguier
	 */
	final private function define_donotoptimize_true() {
		if ( ! defined( 'DONOTROCKETOPTIMIZE' ) ) {
			define( 'DONOTROCKETOPTIMIZE', true ); // WPCS: prefix ok.
		}
	}

	/**
	 * Gets If-modified-since header value
	 *
	 * @since 3.3
	 * @access private
	 * @author Remy Perona
	 * @return string
	 */
	private function get_if_modified_since() {
		if ( function_exists( 'apache_request_headers' ) ) {
			$headers = apache_request_headers();

			return isset( $headers['If-Modified-Since'] ) ? $headers['If-Modified-Since'] : '';
		}

		return $this->config->get_server_input( 'HTTP_IF_MODIFIED_SINCE', '' );
	}

	/**
	 * Get WP Rocket footprint
	 *
	 * @since 3.0.5 White label footprint if WP_ROCKET_WHITE_LABEL_FOOTPRINT is defined.
	 * @since 2.0
	 *
	 * @param int $time UNIX timestamp when the cache file was saved.
	 * @return string The footprint that will be printed
	 */
	private function get_rocket_footprint( $time = '' ) {
		$footprint = defined( 'WP_ROCKET_WHITE_LABEL_FOOTPRINT' ) ?
						"\n" . '<!-- Cached for great performance' :
						"\n" . '<!-- This website is like a Rocket, isn\'t it? Performance optimized by ' . WP_ROCKET_PLUGIN_NAME . '. Learn more: https://wp-rocket.me';
		if ( ! empty( $time ) ) {
			$footprint .= ' - Debug: cached@' . $time;
		}
		$footprint .= ' -->';
		return $footprint;
	}

	/**
	 * Create a hidden empty file for mobile detection on NGINX with the Rocket NGINX configuration.
	 *
	 * @param string $cache_dir_path Path to the current cache directory.
	 * @return void
	 */
	private function maybe_create_nginx_mobile_file( $cache_dir_path ) {
		global $is_nginx;

		if ( ! $this->config->get_config( 'do_caching_mobile_files' ) ) {
			return;
		}

		if ( ! $is_nginx ) {
			return;
		}

		$nginx_mobile_detect = $cache_dir_path . '/.mobile-active';

		if ( rocket_direct_filesystem()->exists( $nginx_mobile_detect ) ) {
			return;
		}

		rocket_direct_filesystem()->touch( $nginx_mobile_detect );
	}

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
		return (bool) apply_filters( 'do_rocket_generate_caching_files', true ); // WPCS: prefix ok.
	}

	/**
	 * Gets the base cache path for the current request
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param array $cookies Cookies for the current request.
	 * @return string
	 */
	private function get_request_cache_path( $cookies ) {
		$host = $this->config->get_host();

		if ( $this->config->get_config( 'url_no_dots' ) ) {
			$host = str_replace( '.', '_', $host );
		}

		$request_uri              = $this->tests->get_clean_request_uri();
		$cookie_hash              = $this->config->get_config( 'cookie_hash' );
		$logged_in_cookie         = $this->config->get_config( 'logged_in_cookie' );
		$logged_in_cookie_no_hash = str_replace( $cookie_hash, '', $logged_in_cookie );

		// Get cache folder of host name.
		if ( $logged_in_cookie && isset( $cookies[ $logged_in_cookie ] ) && ! $this->tests->has_rejected_cookie( $logged_in_cookie_no_hash ) ) {
			if ( $this->config->get_config( 'common_cache_logged_users' ) ) {
				return $this->cache_dir_path . $host . '-loggedin' . rtrim( $request_uri, '/' );
			}

			$user_key = explode( '|', $cookies[ $logged_in_cookie ] );
			$user_key = reset( $user_key );
			$user_key = $user_key . '-' . $this->config->get_config( 'secret_cache_key' );

			// Get cache folder of host name.
			return $this->cache_dir_path . $host . '-' . $user_key . rtrim( $request_uri, '/' );
		}

		return $this->cache_dir_path . $host . rtrim( $request_uri, '/' );
	}

	/**
	 * Modifies the filename if the request is from a mobile device.
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param string $filename Cache filename.
	 * @return string
	 */
	private function maybe_mobile_filename( $filename ) {
		$cache_mobile_files_tablet = $this->config->get_config( 'cache_mobile_files_tablet' );

		if ( ! ( $this->config->get_config( 'cache_mobile' ) && $this->config->get_config( 'do_caching_mobile_files' ) ) ) {
			return $filename;
		}

		if ( ! $cache_mobile_files_tablet ) {
			return $filename;
		}

		if ( ! class_exists( 'Rocket_Mobile_Detect' ) ) {
			return $filename;
		}

		$detect = new \Rocket_Mobile_Detect();

		if ( $detect->isMobile() && ! $detect->isTablet() && 'desktop' === $cache_mobile_files_tablet || ( $detect->isMobile() || $detect->isTablet() ) && 'mobile' === $cache_mobile_files_tablet ) {
				return $filename .= '-mobile';
		}

		return $filename;
	}

	/**
	 * Modifies the filename if dynamic cookies are set
	 *
	 * @param string $filename Cache filename.
	 * @param array  $cookies  Cookies for the request.
	 * @return string
	 */
	private function maybe_dynamic_cookies_filename( $filename, $cookies ) {
		$cache_dynamic_cookies = $this->config->get_config( 'cache_dynamic_cookies' );

		if ( ! $cache_dynamic_cookies ) {
			return $filename;
		}

		foreach ( $cache_dynamic_cookies as $key => $cookie_name ) {
			if ( is_array( $cookie_name ) ) {
				if ( isset( $_COOKIE[ $key ] ) ) {
					foreach ( $cookie_name as $cookie_key ) {
						if ( '' !== $cookies[ $key ][ $cookie_key ] ) {
							$cache_key = $cookies[ $key ][ $cookie_key ];
							$cache_key = preg_replace( '/[^a-z0-9_\-]/i', '-', $cache_key );
							$filename .= '-' . $cache_key;
						}
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

		return $filename;
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
