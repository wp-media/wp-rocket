<?php

namespace WP_Rocket\Engine\Cache;

class WPCache {
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
	 * Set WP_CACHE constant to true if needed
	 *
	 * @since 3.6.1
	 *
	 * @return void
	 */
	public function maybe_set_wp_cache() {
		if (
			rocket_get_constant( 'DOING_AJAX' )
			|| rocket_get_constant( 'DOING_AUTOSAVE' )
		) {
			return;
		}

		if ( rocket_get_constant( 'WP_CACHE' ) ) {
			return;
		}
	
		$this->set_wp_cache_define( true );
	}

	/**
	 * Added or set the value of the WP_CACHE constant
	 *
	 * @since 3.6.1
	 *
	 * @param bool $turn_it_on The value of WP_CACHE constant.
	 * @return void
	 */
	public function set_wp_cache_define( $turn_it_on ) {
		// If WP_CACHE is already define, return to get a coffee.
		if ( ! rocket_valid_key() || ( $turn_it_on && defined( 'WP_CACHE' ) && WP_CACHE ) ) {
			return;
		}

		if ( defined( 'IS_PRESSABLE' ) && IS_PRESSABLE ) {
			return;
		}

		// Get path of the config file.
		$config_file_path = $this->find_wpconfig_path();

		if ( ! $config_file_path ) {
			return;
		}

		// Get content of the config file.
		$config_file = file( $config_file_path );

		// Get the value of WP_CACHE constant.
		$turn_it_on = $turn_it_on ? 'true' : 'false';

		/**
		 * Filter allow to change the value of WP_CACHE constant
		 *
		 * @since 2.1
		 *
		 * @param string $turn_it_on The value of WP_CACHE constant.
		*/
		$turn_it_on = apply_filters( 'set_rocket_wp_cache_define', $turn_it_on ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		// Lets find out if the constant WP_CACHE is defined or not.
		$is_wp_cache_exist = false;

		// Get WP_CACHE constant define.
		$constant = "define('WP_CACHE', $turn_it_on); // Added by WP Rocket\r\n";

		foreach ( $config_file as &$line ) {
			if ( ! preg_match( '/^define\(\s*\'([A-Z_]+)\',(.*)\)/', $line, $match ) ) {
				continue;
			}

			if ( 'WP_CACHE' === $match[1] ) {
				$is_wp_cache_exist = true;
				$line              = $constant;
			}
		}
		unset( $line );

		// If the constant does not exist, create it.
		if ( ! $is_wp_cache_exist ) {
			array_shift( $config_file );
			array_unshift( $config_file, "<?php\r\n", $constant );
		}

		// Insert the constant in wp-config.php file.
		// @codingStandardsIgnoreStart
		$handle = @fopen( $config_file_path, 'w' );
		foreach ( $config_file as $line ) {
			@fwrite( $handle, $line );
		}

		@fclose( $handle );
		// @codingStandardsIgnoreEnd
		// Update the writing permissions of wp-config.php file.
		$chmod = rocket_get_filesystem_perms( 'file' );
		rocket_direct_filesystem()->chmod( $config_file_path, $chmod );
	}

	/**
	 * Try to find the correct wp-config.php file, support one level up in file tree.
	 *
	 * @since 3.6.1
	 *
	 * @return string|bool The path of wp-config.php file or false if not found.
	 */
	public function find_wpconfig_path() {
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

		$config_file = $this->find_wpconfig_path();

		if (
			(
				false !== $config_file
				&&
				$this->filesystem->is_writable( $config_file )
			)
			||
			rocket_get_constant( 'WP_CACHE' )
		) {
			return;
		}

		$notice_name = 'rocket_warning_wp_config_permissions';

		if (
		in_array(
			$notice_name,
			(array) get_user_meta( get_current_user_id(), 'rocket_boxes', true ),
			true )
		) {
			return;
		}

		rocket_notice_html(
			[
				'status'           => 'error',
				'dismissible'      => '',
				'message'          => rocket_notice_writing_permissions( 'wp-config.php' ),
				'dismiss_button'   => $notice_name,
				'readonly_content' => $this->get_wp_config_content(),
			]
		);
	}

	/**
	 * Checks if current user can see the notice
	 *
	 * @since 3.6.1
	 *
	 * @return bool
	 */
	private function is_user_allowed() {
		return current_user_can( 'rocket_manage_options' ) && rocket_valid_key();
	}

	/**
	 * Gets content to add to the wp-config.php file
	 *
	 * @since 3.6.1
	 *
	 * @return string
	 */
	private function get_wp_config_content() {
		$plugin_name = rocket_get_constant( 'WP_ROCKET_PLUGIN_NAME' );
	
		return "/** Enable Cache by {$plugin_name} */\r\ndefine( 'WP_CACHE', true );\r\n";
	}
}
