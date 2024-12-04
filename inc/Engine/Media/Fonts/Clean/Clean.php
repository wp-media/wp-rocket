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
	 * Clean CSS & fonts files stored locally
	 *
	 * @return void
	 */
	public function clean_css_fonts() {
		$this->filesystem->delete_all_files_from_directory( $this->base_path );
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
		if ( ! isset( $old_value['host_fonts_locally'], $value['host_fonts_locally'] ) ) {
			return;
		}

		if ( $old_value['host_fonts_locally'] === $value['host_fonts_locally'] ) {
			return;
		}

		$this->clean_css_fonts();

		/**
		 * Fires when the option to host fonts locally is changed
		 *
		 * @since 3.18
		 */
		do_action( 'rocket_host_fonts_locally_changed' );
	}
}
