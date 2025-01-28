<?php

/**
 * Class ActionScheduler_Versions
 */
class ActionScheduler_Versions {
	/**
	 * ActionScheduler_Versions instance.
	 *
	 * @var ActionScheduler_Versions
	 */
	private static $instance = null;

	/**
	 * Versions.
	 *
	 * @var array<string, callable>
	 */
	private $versions = array();

	/**
	 * Registered sources.
	 *
	 * @var array<string, string>
	 */
	private $sources = array();

	/**
	 * Register version's callback.
	 *
	 * @param string   $version_string          Action Scheduler version.
	 * @param callable $initialization_callback Callback to initialize the version.
	 */
	public function register( $version_string, $initialization_callback ) {
		if ( isset( $this->versions[ $version_string ] ) ) {
			return false;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		$source    = $backtrace[0]['file'];

		$this->versions[ $version_string ] = $initialization_callback;
		$this->sources[ $source ]          = $version_string;
		return true;
	}

	/**
	 * Get all versions.
	 */
	public function get_versions() {
		return $this->versions;
	}

	/**
	 * Get registered sources.
	 *
	 * @return array<string, string>
	 */
	public function get_sources() {
		return $this->sources;
	}

	/**
	 * Get latest version registered.
	 */
	public function latest_version() {
		$keys = array_keys( $this->versions );
		if ( empty( $keys ) ) {
			return false;
		}
		uasort( $keys, 'version_compare' );
		return end( $keys );
	}

	/**
	 * Get callback for latest registered version.
	 */
	public function latest_version_callback() {
		$latest = $this->latest_version();

		if ( empty( $latest ) || ! isset( $this->versions[ $latest ] ) ) {
			return '__return_null';
		}

		return $this->versions[ $latest ];
	}

	/**
	 * Get instance.
	 *
	 * @return ActionScheduler_Versions
	 * @codeCoverageIgnore
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize.
	 *
	 * @codeCoverageIgnore
	 */
	public static function initialize_latest_version() {
		$self = self::instance();
		call_user_func( $self->latest_version_callback() );
	}

	/**
	 * Returns information about the plugin or theme which contains the current active version
	 * of Action Scheduler.
	 *
	 * If this cannot be determined, or if Action Scheduler is being loaded via some other
	 * method, then it will return an empty array. Otherwise, if populated, the array will
	 * look like the following:
	 *
	 *     [
	 *         'type' => 'plugin', # or 'theme'
	 *         'name' => 'Name',
	 *     ]
	 *
	 * @return array
	 */
	public function active_source(): array {
		$file         = __FILE__;
		$dir          = __DIR__;
		$plugins      = get_plugins();
		$plugin_files = array_keys( $plugins );

		foreach ( $plugin_files as $plugin_file ) {
			$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . dirname( $plugin_file );
			$plugin_file = trailingslashit( WP_PLUGIN_DIR ) . $plugin_file;

			if ( 0 !== strpos( dirname( $dir ), $plugin_path ) ) {
				continue;
			}

			$plugin_data = get_plugin_data( $plugin_file );

			if ( ! is_array( $plugin_data ) || empty( $plugin_data['Name'] ) ) {
				continue;
			}

			return array(
				'type' => 'plugin',
				'name' => $plugin_data['Name'],
			);
		}

		$themes = (array) search_theme_directories();

		foreach ( $themes as $slug => $data ) {
			$needle = trailingslashit( $data['theme_root'] ) . $slug . '/';

			if ( 0 !== strpos( $file, $needle ) ) {
				continue;
			}

			$theme = wp_get_theme( $slug );

			if ( ! is_object( $theme ) || ! is_a( $theme, \WP_Theme::class ) ) {
				continue;
			}

			return array(
				'type' => 'theme',
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'name' => $theme->Name,
			);
		}

		return array();
	}

	/**
	 * Returns the directory path for the currently active installation of Action Scheduler.
	 *
	 * @return string
	 */
	public function active_source_path(): string {
		return trailingslashit( dirname( __DIR__ ) );
	}
}
