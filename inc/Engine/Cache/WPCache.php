<?php

namespace WP_Rocket\Engine\Cache;

use WP_Rocket\Engine\Activation\ActivationInterface;
use WP_Rocket\Engine\Deactivation\DeactivationInterface;

class WPCache implements ActivationInterface, DeactivationInterface {
	/**
	 * Filesystem instance.
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Instantiate the class
	 *
	 * @param WP_Filesystem_Direct $filesystem Filesystem instance.
	 */
	public function __construct( $filesystem ) {
		$this->filesystem = $filesystem;
	}

	/**
	 * Performs these actions during the plugin activation
	 *
	 * @return void
	 */
	public function activate() {
		add_action( 'rocket_activation', [ $this, 'update_wp_cache' ] );
	}

	/**
	 * Performs these actions during the plugin deactivation
	 *
	 * @return void
	 */
	public function deactivate() {
		add_action( 'rocket_deactivation', [ $this, 'update_wp_cache' ] );
		add_filter( 'rocket_prevent_deactivation', [ $this, 'maybe_prevent_deactivation' ] );
	}

	/**
	 * Sets the WP_CACHE constant on (de)activation
	 *
	 * @since 3.6.3
	 *
	 * @param int $sites_number Number of WP Rocket config files found.
	 * @return void
	 */
	public function update_wp_cache( $sites_number = 0 ) {
		if ( ! rocket_valid_key() ) {
			return;
		}

		$value = true;

		if ( 'rocket_deactivation' === current_filter() ) {
			if ( is_multisite() && 0 !== $sites_number ) {
				return;
			}

			$value = false;
		}

		$this->set_wp_cache_constant( $value );
	}

	/**
	 * Updates the causes array on deactivation if needed
	 *
	 * @since 3.6.3
	 *
	 * @param array $causes Array of causes to pass to the notice.
	 */
	public function maybe_prevent_deactivation( $causes ) {
		if (
			$this->find_wpconfig_path()
			||
			// This filter is documented in inc/Engine/Cache/WPCache.php.
			! (bool) apply_filters( 'rocket_set_wp_cache_constant', true )
		) {
			return $causes;
		}

		$causes[] = 'wpconfig';

		return $causes;
	}

	/**
	 * Set WP_CACHE constant to true if needed
	 *
	 * @since 3.6.1
	 *
	 * @return void
	 */
	public function maybe_set_wp_cache() {
		if (
			rocket_get_constant( 'DOING_AJAX' )
			||
			rocket_get_constant( 'DOING_AUTOSAVE' )
		) {
			return;
		}

		if ( rocket_get_constant( 'WP_CACHE' ) ) {
			return;
		}

		$this->set_wp_cache_constant( true );
	}

	/**
	 * Sets the value of the WP_CACHE constant in wp-config.php
	 *
	 * @since 3.6.1
	 *
	 * @param bool $value The value to set for WP_CACHE constant.
	 * @return bool true on success, false otherwise.
	 */
	public function set_wp_cache_constant( $value ) {
		if ( ! $this->should_set_wp_cache_constant( $value ) ) {
			return false;
		}

		$config_file_path = $this->find_wpconfig_path();

		if ( ! $config_file_path ) {
			return false;
		}

		$config_file_contents = $this->filesystem->get_contents( $config_file_path );
		$value                = $value ? 'true' : 'false';

		/**
		 * Filter allow to change the value of WP_CACHE constant
		 *
		 * @since 2.1
		 *
		 * @param string $value The value of WP_CACHE constant.
		*/
		$value = apply_filters( 'set_rocket_wp_cache_define', $value ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		$wp_cache_found = preg_match( '/^\s*define\(\s*\'WP_CACHE\'\s*,\s*(?<value>[^\s\)]*)\s*\)/m', $config_file_contents, $matches );

		if (
			! empty( $matches['value'] )
			&&
			$matches['value'] === $value
		) {
			return false;
		}

		$constant = $this->get_wp_cache_content( $value );

		if ( ! $wp_cache_found ) {
			$config_file_contents = preg_replace( '/(<\?php)/i', "<?php\r\n{$constant}\r\n", $config_file_contents, 1 );
		} elseif ( ! empty( $matches['value'] ) && $matches['value'] !== $value ) {
			$config_file_contents = preg_replace( '/^\s*define\(\s*\'WP_CACHE\'\s*,\s*([^\s\)]*)\s*\).+/m', $constant, $config_file_contents );
		}

		return $this->filesystem->put_contents( $config_file_path, $config_file_contents, rocket_get_filesystem_perms( 'file' ) );
	}

	/**
	 * Checks if we should set the WP_CACHE constant
	 *
	 * @since 3.6.1
	 *
	 * @param bool $value The value to set for WP_CACHE constant.
	 * @return bool
	 */
	private function should_set_wp_cache_constant( $value ) {
		if ( ! $this->is_user_allowed() ) {
			return false;
		}

		if (
			true === $value
			&&
			rocket_get_constant( 'WP_CACHE' )
		) {
			return false;
		}

		/**
		 * Filters the writing of the WP_CACHE constant in wp-config.php
		 *
		 * @since 3.6.1
		 * @param bool $set True to allow writing, false otherwise.
		 */
		return (bool) apply_filters( 'rocket_set_wp_cache_constant', true );
	}

	/**
	 * Try to find the correct wp-config.php file, support one level up in file tree.
	 *
	 * @since 3.6.1
	 *
	 * @return string|bool The path of wp-config.php file or false if not found.
	 */
	private function find_wpconfig_path() {
		/**
		 * Filter the wp-config's filename.
		 *
		 * @since 2.11
		 *
		 * @param string $filename The WP Config filename, without the extension.
		 */
		$config_file_name = apply_filters( 'rocket_wp_config_name', 'wp-config' );
		$abspath          = rocket_get_constant( 'ABSPATH' );
		$config_file      = "{$abspath}{$config_file_name}.php";

		if ( $this->filesystem->is_writable( $config_file ) ) {
			return $config_file;
		}

		$abspath_parent  = dirname( $abspath ) . DIRECTORY_SEPARATOR;
		$config_file_alt = "{$abspath_parent}{$config_file_name}.php";

		if (
			$this->filesystem->exists( $config_file_alt )
			&&
			$this->filesystem->is_writable( $config_file_alt )
			&&
			! $this->filesystem->exists( "{$abspath_parent}wp-settings.php" )
		) {
			return $config_file_alt;
		}

		// No writable file found.
		return false;
	}

	/**
	 * This warning is displayed when the wp-config.php file isn't writable
	 *
	 * @since 3.6.1
	 *
	 * @return void
	 */
	public function notice_wp_config_permissions() {
		global $pagenow;

		if (
			'plugins.php' === $pagenow
			||
			isset( $_GET['activate'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			return;
		}

		if ( ! $this->is_user_allowed() ) {
			return;
		}

		if ( rocket_get_constant( 'WP_CACHE' ) ) {
			return;
		}

		// This filter is documented in inc/Engine/Cache/WPCache.php.
		if ( ! (bool) apply_filters( 'rocket_set_wp_cache_constant', true ) ) {
			return;
		}

		if ( $this->find_wpconfig_path() ) {
			return;
		}

		$notice_name = 'rocket_warning_wp_config_permissions';

		if (
			in_array(
				$notice_name,
				(array) get_user_meta( get_current_user_id(), 'rocket_boxes', true ),
				true
			)
		) {
			return;
		}

		rocket_notice_html(
			[
				'status'           => 'error',
				'dismissible'      => '',
				'message'          => rocket_notice_writing_permissions( 'wp-config.php' ),
				'dismiss_button'   => $notice_name,
				'readonly_content' => $this->get_wp_cache_content(),
			]
		);
	}

	/**
	 * Checks if current user can perform the action
	 *
	 * @since 3.6.1
	 *
	 * @return bool
	 */
	private function is_user_allowed() {
		return ( rocket_get_constant( 'WP_CLI', false ) || current_user_can( 'rocket_manage_options' ) ) && rocket_valid_key();
	}

	/**
	 * Gets the content to add to the wp-config.php file
	 *
	 * @since 3.6.1
	 *
	 * @param string $value Value for the WP_CACHE constant.
	 * @return string
	 */
	private function get_wp_cache_content( $value = 'true' ) {
		$plugin_name = rocket_get_constant( 'WP_ROCKET_PLUGIN_NAME' );

		return "define( 'WP_CACHE', {$value} ); // Added by {$plugin_name}";
	}


	/**
	 * Adds a Site Health check for the WP_CACHE constant value
	 *
	 * @since 3.6.1
	 *
	 * @param array $tests An array of tests to perform.
	 * @return array
	 */
	public function add_wp_cache_status_test( $tests ) {

		// This filter is documented in inc/Engine/Cache/WPCache.php.
		if ( ! (bool) apply_filters( 'rocket_set_wp_cache_constant', true ) ) {
			return $tests;
		}

		$tests['direct']['wp_cache_status'] = [
			'label' => __( 'WP_CACHE value', 'rocket' ),
			'test'  => [ $this, 'check_wp_cache_value' ],
		];

		return $tests;
	}

	/**
	 * Checks the WP_CACHE constant value and return the result for Site Health
	 *
	 * @since 3.6.1
	 *
	 * @return array
	 */
	public function check_wp_cache_value() {
		$result = [
			'badge'       => [
				'label' => __( 'Cache', 'rocket' ),
			],
			'description' => sprintf(
				'<p>%s</p>',
				__( 'The WP_CACHE constant needs to be set to true for WP Rocket cache to work properly', 'rocket' )
			),
			'actions'     => '',
			'test'        => 'wp_cache_status',
		];

		$value = rocket_get_constant( 'WP_CACHE' );

		if ( true === $value ) {
			$result['label']          = __( 'WP_CACHE is set to true', 'rocket' );
			$result['status']         = 'good';
			$result['badge']['color'] = 'green';

			return $result;
		}

		if ( null === $value ) {
			$result['label']          = __( 'WP_CACHE is not set', 'rocket' );
			$result['status']         = 'critical';
			$result['badge']['color'] = 'red';

			return $result;
		}

		if ( false === $value ) {
			$result['label']          = __( 'WP_CACHE is set to false', 'rocket' );
			$result['status']         = 'critical';
			$result['badge']['color'] = 'red';

			return $result;
		}

		return $result;
	}
}
