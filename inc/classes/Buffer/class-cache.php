<?php
namespace WP_Rocket\Buffer;

/**
 * Handle page cache.
 *
 * @since  3.3
 */
class Cache extends Abstract_Buffer {

	/**
	 * Process identifier used by the logger.
	 *
	 * @var    string
	 * @since  3.3
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
	 */
	private $cache_dir_path;

	/**
	 * Exclude urls from wp canonical redirect.
	 *
	 * @var array Array of url patterns to exclude from wp canonical redirect.
	 */
	private $wp_redirect_exclusions = [
		'(.*)wp\-json(/.*|$)',
	];

	/**
	 * Constructor.
	 *
	 * @since  3.3
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
	 */
	public function maybe_init_process() {
		if ( ! $this->tests->can_init_process() ) {
			$this->define_donotoptimize_true();
			$this->log_last_test_error();
			return;
		}

		if ( $this->maybe_allow_wp_redirect() ) {
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
		$accept_gzip         = $accept_encoding && false !== strpos( $accept_encoding, 'gzip' );

		// Check if cache file exist.
		if ( $accept_gzip && is_readable( $cache_filepath_gzip ) ) {
			$this->serve_gzip_cache_file( $cache_filepath_gzip );
		}

		if ( is_readable( $cache_filepath ) ) {
			$this->serve_cache_file( $cache_filepath );
		}

		// Maybe we're looking for a webp file.
		$cache_filename = basename( $cache_filepath );

		if ( strpos( $cache_filename, '-webp' ) !== false ) {
			// We're looking for a webp file that doesn't exist: try to locate any `.no-webp` file.
			$cache_dir_path = rtrim( dirname( $cache_filepath ), '/\\' ) . DIRECTORY_SEPARATOR;

			if ( file_exists( $cache_dir_path . '.no-webp' ) ) {
				// We have a `.no-webp` file: try to deliver a non-webp cache file.
				$cache_filepath      = $cache_dir_path . str_replace( '-webp', '', $cache_filename );
				$cache_filepath_gzip = $cache_filepath . '_gzip';

				$this->log(
					'Looking for non-webp cache file.',
					[
						'path' => $cache_filepath,
					]
				);

				// Try to deliver the non-webp version instead.
				if ( $accept_gzip && is_readable( $cache_filepath_gzip ) ) {
					$this->serve_gzip_cache_file( $cache_filepath_gzip );
				}

				if ( is_readable( $cache_filepath ) ) {
					$this->serve_cache_file( $cache_filepath );
				}
			}
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
		readfile( $cache_filepath ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile

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

		$webp_enabled   = preg_match( '@<!-- Rocket (has|no) webp -->@', $buffer, $webp_tag );
		$has_webp       = ! empty( $webp_tag ) ? 'has' === $webp_tag[1] : false;
		$cache_filepath = $this->get_cache_path( [ 'webp' => $has_webp ] );
		$cache_dir_path = dirname( $cache_filepath );

		// Create cache folders.
		rocket_mkdir_p( $cache_dir_path );

		if ( $is_html ) {
			$footprint = $this->get_rocket_footprint( time() );
		}

		// Webp request.
		if ( $webp_enabled ) {
			$buffer = str_replace( $webp_tag[0], '', $buffer );

			if ( ! $has_webp ) {
				// The buffer doesn’t contain webp files.
				$cache_dir_path = rtrim( dirname( $cache_filepath ), '/\\' );

				$this->maybe_create_nowebp_file( $cache_dir_path );
			}
		}

		$this->write_cache_file( $cache_filepath, $buffer . $footprint );
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
	 * Writes the cache file(s)
	 *
	 * @since 3.5
	 *
	 * @param string $cache_filepath Absolute path to the cache file.
	 * @param string $content Content to write in the cache file.
	 * @return void
	 */
	private function write_cache_file( $cache_filepath, $content ) {
		$gzip_filepath      = $cache_filepath . '_gzip';
		$temp_filepath      = $cache_filepath . '_temp';
		$temp_gzip_filepath = $gzip_filepath . '_temp';

		if ( rocket_direct_filesystem()->exists( $temp_filepath ) ) {
			return;
		}

		// Save the cache file.
		if ( ! rocket_put_content( $temp_filepath, $content ) ) {
			return;
		}

		rocket_direct_filesystem()->move( $temp_filepath, $cache_filepath, true );

		if ( function_exists( 'gzencode' ) ) {
			/**
			 * Filters the Gzip compression level to use for the cache file
			 *
			 * @param int $compression_level Compression level between 0 and 9.
			 */
			$compression_level = apply_filters( 'rocket_gzencode_level_compression', 6 );

			if ( ! rocket_put_content( $temp_gzip_filepath, gzencode( $content, $compression_level ) ) ) {
				return;
			}

			rocket_direct_filesystem()->move( $temp_gzip_filepath, $gzip_filepath, true );
		}
	}

	/**
	 * Get the path to the cache file.
	 *
	 * @since  3.3
	 *
	 * @param  array $args {
	 *     A list of arguments.
	 *
	 *     @type bool $webp Set to false to prevent adding the part related to webp.
	 * }
	 * @return string
	 */
	public function get_cache_path( $args = [] ) {
		$args             = array_merge(
			[
				'webp' => true,
			],
			$args
		);
		$cookies          = $this->tests->get_cookies();
		$request_uri_path = $this->get_request_cache_path( $cookies );
		$filename         = 'index';

		$filename = $this->maybe_mobile_filename( $filename );

		// Rename the caching filename for SSL URLs.
		if ( is_ssl() && $this->config->get_config( 'cache_ssl' ) ) {
			$filename .= '-https';
		}

		if ( $args['webp'] ) {
			$filename = $this->maybe_webp_filename( $filename );
		}

		$filename = $this->maybe_dynamic_cookies_filename( $filename, $cookies );

		// Ensure proper formatting of the path.
		$request_uri_path = preg_replace_callback( '/%[0-9A-F]{2}/', [ $this, 'reset_lowercase' ], $request_uri_path );
		// Directories in Windows can't contain question marks.
		$request_uri_path = str_replace( '?', '#', $request_uri_path );
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
	 */
	private function define_donotoptimize_true() {
		if ( ! defined( 'DONOTROCKETOPTIMIZE' ) ) {
			define( 'DONOTROCKETOPTIMIZE', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		}
	}

	/**
	 * Gets If-modified-since header value
	 *
	 * @since 3.3
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
	 * Create a hidden empty file when webp is enabled but the buffer doesn’t contain webp files.
	 *
	 * @since  3.4
	 *
	 * @param string $cache_dir_path Path to the current cache directory (without trailing slah).
	 */
	private function maybe_create_nowebp_file( $cache_dir_path ) {
		$nowebp_filepath = $cache_dir_path . DIRECTORY_SEPARATOR . '.no-webp';

		if ( rocket_direct_filesystem()->exists( $nowebp_filepath ) ) {
			return;
		}

		rocket_direct_filesystem()->touch( $nowebp_filepath );
	}

	/**
	 * Tell if generating cache files is allowed.
	 *
	 * @since  3.3
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
		return (bool) apply_filters( 'do_rocket_generate_caching_files', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}

	/**
	 * Gets the base cache path for the current request
	 *
	 * @since 3.3
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
				return $this->cache_dir_path . $host . '-loggedin-' . $this->config->get_config( 'secret_cache_key' ) . rtrim( $request_uri, '/' );
			}

			$user_key = explode( '|', $cookies[ $logged_in_cookie ] );
			$user_key = reset( $user_key );
			$user_key = $this->sanitize_user( $user_key ) . '-' . $this->config->get_config( 'secret_cache_key' );

			// Get cache folder of host name.
			return $this->cache_dir_path . $host . '-' . $user_key . rtrim( $request_uri, '/' );
		}

		return $this->cache_dir_path . $host . rtrim( $request_uri, '/' );
	}

	/**
	 * Modifies the filename if the request is from a mobile device.
	 *
	 * @since 3.3
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

		if ( ! class_exists( 'WP_Rocket_Mobile_Detect' ) ) {
			return $filename;
		}

		$detect = new \WP_Rocket_Mobile_Detect();

		if (
			( $detect->isMobile() && ! $detect->isTablet() && 'desktop' === $cache_mobile_files_tablet )
			||
			(
				( $detect->isMobile() || $detect->isTablet() )
				&&
				'mobile' === $cache_mobile_files_tablet
			)
		) {
				return $filename .= '-mobile';
		}

		return $filename;
	}

	/**
	 * Modifies the filename if the request is WebP compatible
	 *
	 * @since 3.4
	 *
	 * @param string $filename Cache filename.
	 * @return string
	 */
	private function maybe_webp_filename( $filename ) {
		if ( ! $this->config->get_config( 'cache_webp' ) ) {
			return $filename;
		}

		/**
		 * Force WP Rocket to disable its webp cache.
		 *
		 * @since  3.4
		 *
		 * @param bool $disable_webp_cache Set to true to disable the webp cache.
		 */
		$disable_webp_cache = apply_filters( 'rocket_disable_webp_cache', false );

		if ( $disable_webp_cache ) {
			return $filename;
		}

		if ( ! $this->is_browser_webp_compatible() ) {
			return $filename;
		}

		return $filename . '-webp';
	}

	/**
	 * Checks if the browser is WebP compatible
	 *
	 * @since 3.12.6
	 *
	 * @return bool
	 */
	private function is_browser_webp_compatible(): bool {
		// Only to supporting browsers.
		$http_accept = $this->config->get_server_input( 'HTTP_ACCEPT', '' );

		if (
			empty( $http_accept )
			&&
			function_exists( 'apache_request_headers' )
		) {
			$headers     = apache_request_headers();
			$http_accept = isset( $headers['Accept'] ) ? $headers['Accept'] : '';
		}

		if (
			! empty( $http_accept )
			&&
			false !== strpos( $http_accept, 'webp' )
		) {
			return true;
		}

		return $this->is_user_agent_compatible();
	}

	/**
	 * Check the User Agent if the Accept headers is missing the WebP info
	 *
	 * @since 3.12.6
	 *
	 * @return bool
	 */
	private function is_user_agent_compatible(): bool {
		$user_agent = $this->config->get_server_input( 'HTTP_USER_AGENT' );

		if ( empty( $user_agent ) ) {
			return false;
		}

		if ( preg_match( '#Firefox/(?<version>[0-9]{2,})#i', $user_agent, $matches ) ) {
			if ( 66 >= (int) $matches['version'] ) {
				return false;
			}
		}

		if ( preg_match( '#(?:iPad|iPhone)(.*)Version/(?<version>[0-9]{2,})#i', $user_agent, $matches ) ) {
			if ( 14 > (int) $matches['version'] ) {
				return false;
			}

			return true;
		}

		if ( preg_match( '#Version/(?<version>[0-9]{2,})(?:.*)Safari#i', $user_agent, $matches ) ) {
			if ( 16 > (int) $matches['version'] ) {
				return false;
			}
		}

		return true;
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
	 *
	 * @param  array $matches Cache path.
	 * @return string         Cache path in lowercase.
	 */
	protected function reset_lowercase( $matches ) {
		return strtolower( $matches[0] );
	}

	/**
	 * Sanitizes a string username.
	 *
	 * @param string $user String username.
	 *
	 * @return string
	 */
	private function sanitize_user( string $user = '' ): string {
		return strtolower( rawurlencode( $user ) );
	}

	/**
	 * Check if permalink structure and url match.
	 *
	 * @return bool
	 */
	private function maybe_allow_wp_redirect(): bool {

		$exclusions = implode( '|', $this->wp_redirect_exclusions );

		// Return early for excluded urls.
		if ( preg_match( '#' . $exclusions . '#', $this->tests->get_request_uri_base() )
		) {
			return false;
		}

		$permalink_structure = $this->config->get_config( 'permalink_structure' );

		// Last character of permalink.
		$permalink_last_char = '/' !== substr( $permalink_structure, -1 ) ? '' : '/';

		// Request uri without protocol & domain name.
		$request_uri = $this->tests->get_request_uri_base();

		// Last character of request uri.
		$request_uri_last_char = '/' !== substr( $request_uri, -1 ) ? '' : '/';

		// In cases where we have the home with a trailng slash (visible or invisible)
		// and permalink is without trailing slash.
		if ( '' === $permalink_last_char ) {
			// Check for root installation.
			$request_uri_last_char = '/' === $request_uri ? '' : $request_uri_last_char;

			/**
			 * Check for subdir installation.
			 * Use config file name to get home request_uri.
			 */
			$home = str_replace( $this->config->get_host(), '', basename( $this->config->get_config_file_path()['path'] ) );
			$home = str_replace( '.', '/', str_replace( '.php', '', $home ) );

			if ( '/' !== $request_uri && rtrim( $request_uri, '/' ) === $home ) {
				$request_uri_last_char = '';
			}
		}

		return $permalink_last_char !== $request_uri_last_char;
	}
}
