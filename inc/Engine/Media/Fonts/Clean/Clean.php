<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Clean;

use WP_Rocket\Engine\Media\Fonts\Filesystem;

class Clean {
	/**
	 * Filesystem instance
	 *
	 * @var Filesystem
	 */
	private $filesystem;

	/**
	 * Base path for fonts
	 *
	 * @var string
	 */
	private $base_path;

	/**
	 * Constructor
	 *
	 * @param Filesystem $filesystem Filesystem instance.
	 */
	public function __construct( $filesystem ) {
		$this->filesystem = $filesystem;
		$this->base_path  = rocket_get_constant( 'WP_ROCKET_CACHE_ROOT_PATH', '' ) . 'fonts/' . get_current_blog_id() . '/';
	}

	/**
	 * Clean fonts CSS files stored locally
	 *
	 * @return void
	 */
	public function clean_fonts_css() {
		$path = $this->base_path . 'google-fonts/css/';

		$this->filesystem->delete_all_files_from_directory( $path );
	}

	/**
	 * Clean fonts files stored locally
	 *
	 * @return void
	 */
	public function clean_fonts() {
		$path = $this->base_path . 'google-fonts/fonts/';

		$this->filesystem->delete_all_files_from_directory( $path );
	}

	/**
	 * Clean CSS & fonts files stored locally on option change
	 *
	 * @param mixed $old_value Old option value.
	 * @param mixed $value     New option value.
	 *
	 * @return void
	 */
	public function clean_on_option_change( $old_value, $value ) {
		if ( ! $this->did_setting_change( 'host_fonts_locally', $old_value, $value ) ) {
			return;
		}

		$this->clean_fonts_css();

		/**
		 * Fires when the option to host fonts locally is changed
		 *
		 * @since 3.18
		 */
		do_action( 'rocket_host_fonts_locally_changed' );
	}

	/**
	 * Clean CSS & fonts files stored locally on CDN change
	 *
	 * @param mixed $old_value Old option value.
	 * @param mixed $value     New option value.
	 *
	 * @return void
	 */
	public function clean_on_cdn_change( $old_value, $value ) {
		if ( ! $this->did_setting_change( 'cdn', $old_value, $value ) ) {
			return;
		}

		if ( ! $this->did_setting_change( 'cdn_cnames', $old_value, $value ) ) {
			return;
		}

		$this->clean_fonts_css();
	}

	/**
	 * Checks if the given setting's value changed.
	 *
	 * @param string $setting The settings's value to check in the old and new values.
	 * @param mixed  $old_value Old option value.
	 * @param mixed  $value     New option value.
	 *
	 * @return bool
	 */
	private function did_setting_change( $setting, $old_value, $value ) {
		return (
			array_key_exists( $setting, $old_value )
			&&
			array_key_exists( $setting, $value )
			&&
			// phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
			$old_value[ $setting ] != $value[ $setting ]
		);
	}
}
