<?php

namespace WP_Rocket\Engine\Cache;

use WP_Filesystem_Direct;
use WP_Rocket\Engine\Activation\ActivationInterface;
use WP_Rocket\Engine\Deactivation\DeactivationInterface;

class AdvancedCache implements ActivationInterface, DeactivationInterface {

	/**
	 * Absolute path to template files
	 *
	 * @var string
	 */
	private $template_path;

	/**
	 * WP Content directory path
	 *
	 * @var string
	 */
	private $content_dir;

	/**
	 * Instance of the filesystem handler.
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Instantiate of the class.
	 *
	 * @param string               $template_path Absolute path to template files.
	 * @param WP_Filesystem_Direct $filesystem    Instance of the filesystem handler.
	 */
	public function __construct( $template_path, $filesystem ) {
		$this->template_path = $template_path;
		$this->content_dir   = rocket_get_constant( 'WP_CONTENT_DIR' );
		$this->filesystem    = $filesystem;
	}

	/**
	 * Actions to perform on plugin activation
	 *
	 * @since 3.6.3
	 *
	 * @return void
	 */
	public function activate() {
		add_action( 'rocket_activation', [ $this, 'update_advanced_cache' ] );
	}

	/**
	 * Actions to perform on plugin deactivation
	 *
	 * @since 3.6.3
	 *
	 * @return void
	 */
	public function deactivate() {
		add_action( 'rocket_deactivation', [ $this, 'update_advanced_cache' ] );
	}

	/**
	 * Generates the advanced-cache.php file with its content
	 *
	 * @since 3.6.3
	 *
	 * @param int $sites_number Number of WP Rocket config files found.
	 * @return void
	 */
	public function update_advanced_cache( $sites_number = 0 ) {
		/**
		 * Filters whether to generate the advanced-cache.php file.
		 *
		 * @since 3.6.3
		 *
		 * @param bool True (default) to go ahead with advanced cache file generation; false to stop generation.
		 */
		if ( ! (bool) apply_filters( 'rocket_generate_advanced_cache_file', true ) ) {
			return;
		}

		$content = $this->get_advanced_cache_content();

		if ( 'rocket_deactivation' === current_filter() ) {
			if ( is_multisite() && 0 !== $sites_number ) {
				return;
			}

			$content = '';
		}

		$this->filesystem->put_contents(
			"{$this->content_dir}/advanced-cache.php",
			$content,
			rocket_get_filesystem_perms( 'file' )
		);
	}

	/**
	 * Gets the content for the advanced-cache.php file
	 *
	 * @since 3.6
	 *
	 * @return string
	 */
	public function get_advanced_cache_content() {
		$content = $this->filesystem->get_contents( $this->template_path . 'advanced-cache.php' );
		$mobile  = is_rocket_generate_caching_mobile_files() ? '$1' : '';
		$content = preg_replace( "/'{{MOBILE_CACHE}}';(\X*)'{{\/MOBILE_CACHE}}';/", $mobile, $content );

		$replacements = [
			'{{WP_ROCKET_PHP_VERSION}}' => rocket_get_constant( 'WP_ROCKET_PHP_VERSION' ),
			'{{WP_ROCKET_PATH}}'        => rocket_get_constant( 'WP_ROCKET_PATH' ),
			'{{WP_ROCKET_CONFIG_PATH}}' => rocket_get_constant( 'WP_ROCKET_CONFIG_PATH' ),
			'{{WP_ROCKET_CACHE_PATH}}'  => rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ),
		];

		foreach ( $replacements as $key => $value ) {
			$content = str_replace( $key, $value, $content );
		}

		/**
		 * Filter the content of advanced-cache.php file.
		 *
		 * @since 2.1
		 *
		 * @param string $content The content that will be printed in advanced-cache.php.
		 */
		return (string) apply_filters( 'rocket_advanced_cache_file', $content );
	}

	/**
	 * This warning is displayed when the advanced-cache.php file isn't writeable
	 *
	 * @since 3.6 Moved to a method in AdvancedCache
	 * @since 2.0
	 *
	 * @return void
	 */
	public function notice_permissions() {
		if ( ! $this->is_user_allowed() ) {
			return;
		}

		// This filter is documented in inc/functions/files.php.
		if ( ! (bool) apply_filters( 'rocket_generate_advanced_cache_file', true ) ) {
			return;
		}

		if (
			$this->filesystem->is_writable( "{$this->content_dir}/advanced-cache.php" )
			||
			rocket_get_constant( 'WP_ROCKET_ADVANCED_CACHE' )
		) {
			return;
		}

		$notice_name = 'rocket_warning_advanced_cache_permissions';

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
				'message'          => $this->get_notice_message(),
				'dismiss_button'   => $notice_name,
				'readonly_content' => $this->get_advanced_cache_content(),
			]
		);
	}

	/**
	 * Checks if current user can see the notices
	 *
	 * @since 3.6
	 *
	 * @return bool
	 */
	private function is_user_allowed() {
		return current_user_can( 'rocket_manage_options' ) && rocket_valid_key();
	}

	/**
	 * Gets the message to display in the notice
	 *
	 * @since 3.6
	 *
	 * @return string
	 */
	private function get_notice_message() {
		return rocket_notice_writing_permissions( basename( $this->content_dir ) . '/advanced-cache.php' );
	}
}
