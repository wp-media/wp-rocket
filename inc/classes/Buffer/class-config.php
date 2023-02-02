<?php
namespace WP_Rocket\Buffer;

/**
 * Configuration class for WP Rocket cache
 *
 * @since 3.3
 * @author Remy Perona
 */
class Config {
	use \WP_Rocket\Traits\Memoize;

	/**
	 * Path to the directory containing the config files.
	 *
	 * @var    string
	 * @since  3.3
	 * @access private
	 * @author Grégory Viguier
	 */
	private static $config_dir_path;

	/**
	 * Values of $_SERVER to use for some tests.
	 *
	 * @var    array
	 * @since  3.3
	 * @access private
	 * @author Grégory Viguier
	 */
	private static $server;

	/**
	 * Constructor
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $config_dir_path WP Rocket config directory path.
	 *     @type array  $server          Values of $_SERVER to use for the tests. Default is $_SERVER.
	 * }
	 */
	public function __construct( $args ) {
		if ( isset( self::$config_dir_path ) ) {
			// Make sure to keep the same values all along.
			return;
		}

		if ( ! isset( $args['server'] ) && ! empty( $_SERVER ) && is_array( $_SERVER ) ) {
			$args['server'] = $_SERVER;
		}

		self::$config_dir_path = rtrim( $args['config_dir_path'], '/' ) . '/';
		self::$server          = ! empty( $args['server'] ) && is_array( $args['server'] ) ? $args['server'] : [];
	}

	/**
	 * Get a $_SERVER entry.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $entry_name Name of the entry.
	 * @param  mixed  $default    Value to return if the entry is not set.
	 * @return mixed
	 */
	public function get_server_input( $entry_name, $default = null ) {
		if ( ! isset( self::$server[ $entry_name ] ) ) {
			return $default;
		}

		return self::$server[ $entry_name ];
	}

	/**
	 * Get the `server` property.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public function get_server() {
		return self::$server;
	}

	/**
	 * Get a specific config/option value.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $config_name Name of a specific config/option.
	 * @return mixed
	 */
	public function get_config( $config_name ) {
		$config = $this->get_configs();
		return isset( $config[ $config_name ] ) ? $config[ $config_name ] : null;
	}

	/**
	 * Get the whole current configuration.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array|bool An array containing the configuration. False on failure.
	 */
	public function get_configs() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$config_file_path = $this->get_config_file_path();

		if ( ! $config_file_path['success'] ) {
			return self::memoize( __FUNCTION__, [], false );
		}

		include $config_file_path['path'];

		$config = [
			'cookie_hash'               => '',
			'logged_in_cookie'          => '',
			'common_cache_logged_users' => 0,
			'cache_mobile_files_tablet' => 'desktop',
			'cache_ssl'                 => 0,
			'cache_webp'                => 0,
			'cache_mobile'              => 0,
			'do_caching_mobile_files'   => 0,
			'secret_cache_key'          => '',
			'cache_reject_uri'          => '',
			'cache_query_strings'       => [],
			'cache_ignored_parameters'  => [],
			'cache_reject_cookies'      => '',
			'cache_reject_ua'           => '',
			'cache_mandatory_cookies'   => '',
			'cache_dynamic_cookies'     => [],
			'url_no_dots'               => 0,
		];

		foreach ( $config as $entry_name => $entry_value ) {
			$var_name = 'rocket_' . $entry_name;

			if ( isset( $$var_name ) ) {
				$config[ $entry_name ] = $$var_name;
			}
		}

		return self::memoize( __FUNCTION__, [], $config );
	}

	/**
	 * Get the host, to use for config and cache file path.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_host() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$host = $this->get_server_input( 'HTTP_HOST', (string) time() );
		$host = preg_replace( '/:\d+$/', '', $host );
		$host = trim( strtolower( $host ), '.' );

		return self::memoize( __FUNCTION__, [], rawurlencode( $host ) );
	}

	/**
	 * Get the path to an existing config file.
	 *
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @return string|bool The path to the file. False if no file is found.
	 */
	public function get_config_file_path() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$config_dir_real_path = realpath( self::$config_dir_path ) . DIRECTORY_SEPARATOR;

		$host = $this->get_host();

		$path = str_replace( '\\', '/', strtok( $this->get_server_input( 'REQUEST_URI', '' ), '?' ) );
		$path = preg_replace( '|(?<=.)/+|', '/', $path );
		$path = explode( '%2F', preg_replace( '/^(?:%2F)*(.*?)(?:%2F)*$/', '$1', rawurlencode( $path ) ) );
		// Remove empty array values.
		$path = array_filter( $path );

		/**
		 * If path is not empty.
		 * i.e url with something like this `multisite/green/sample-page` after the host.
		 */
		if ( ! empty( $path ) ) {
			$config_file_paths = [];

			// Loop through paths and store valid config file paths matching the url current path in an array.
			foreach ( $path as $p ) {
				static $dir;

				if ( realpath( self::$config_dir_path . $host . '.' . $p . '.php' ) && 0 === stripos( realpath( self::$config_dir_path . $host . '.' . $p . '.php' ), $config_dir_real_path ) ) {
					$config_file_paths[] = self::$config_dir_path . $host . '.' . $p . '.php';
				}

				if ( realpath( self::$config_dir_path . $host . '.' . $dir . $p . '.php' ) && 0 === stripos( realpath( self::$config_dir_path . $host . '.' . $dir . $p . '.php' ), $config_dir_real_path ) ) {
					$config_file_paths[] = self::$config_dir_path . $host . '.' . $dir . $p . '.php';
				}

				$dir .= $p . '.';
			}

			// Reverse array order so that subsite config file paths can come first.
			$config_file_paths = array_reverse( $config_file_paths );

			/**
			 * Check if there was a matching config file for the url current path
			 * and return the first
			 */
			if ( ! empty( $config_file_paths ) ) {
				return self::memoize(
					__FUNCTION__,
					[],
					[
						'success' => true,
						'path'    => $config_file_paths[0],
					]
				);
			}
		}

		if ( realpath( self::$config_dir_path . $host . '.php' ) && 0 === stripos( realpath( self::$config_dir_path . $host . '.php' ), $config_dir_real_path ) ) {
			$config_file_path = self::$config_dir_path . $host . '.php';
			return self::memoize(
				__FUNCTION__,
				[],
				[
					'success' => true,
					'path'    => $config_file_path,
				]
			);
		}

		return self::memoize(
			__FUNCTION__,
			[],
			[
				'success' => false,
				'path'    => self::$config_dir_path . $host . implode( '/', $path ) . '.php',
			]
		);
	}

	/** ----------------------------------------------------------------------------------------- */
	/** SPECIFIC CONFIG GETTERS ================================================================= */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get rejected cookies as a regex pattern.
	 * `#` is used as pattern delimiter.
	 *
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_rejected_cookies() {
		$rejected_cookies = $this->get_config( 'cache_reject_cookies' );

		if ( '' === $rejected_cookies ) {
			return $rejected_cookies;
		}

		return '#' . $rejected_cookies . '#';
	}

	/**
	 * Get mandatory cookies as a regex pattern.
	 * `#` is used as pattern delimiter.
	 *
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_mandatory_cookies() {
		$mandatory_cookies = $this->get_config( 'cache_mandatory_cookies' );

		if ( '' === $mandatory_cookies ) {
			return $mandatory_cookies;
		}

		return '#' . $mandatory_cookies . '#';
	}

}
