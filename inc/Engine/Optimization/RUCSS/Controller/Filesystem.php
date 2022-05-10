<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Controller;

class Filesystem {
	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $filesystem;

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $path;

	/**
	 * Instantiate the class
	 *
	 * @param [type] $filesystem
	 * @param [type] $base_path
	 */
	public function __construct( $filesystem = null, $base_path ) {
		$this->filesystem = is_null( $filesystem ) ? rocket_direct_filesystem() : $filesystem;
		$this->path = $base_path . get_current_blog_id() . '/';
	}

	/**
	 * Gets the used CSS content corresponding to the provided hash
	 *
	 * @param string $hash Hash of the corresponding used CSS
	 *
	 * @return string
	 */
	public function get_used_css( $hash ) {
		$file = $this->path . $hash . '.css.gz';

		if ( ! $this->filesystem->exists( $file ) ) {
			return '';
		}

		$css = gzdecode( $this->filesystem->get_contents( $file ) );

		if ( ! $css ) {
			return '';
		}

		return $css;
	}

	/**
	 * Writes the used CSS to the filesystem
	 *
	 * @param string $hash Hash to use for the filename.
	 * @param string $used_css Used CSS content.
	 *
	 * @return bool
	 */
	public function write_used_css( $hash, $used_css ) {
		$file = $this->path . $hash . '.css.gz';

		// This filter is documented in inc/classes/Buffer/class-cache.php.
		$css = gzencode( $used_css, apply_filters( 'rocket_gzencode_level_compression', 6 ) );

		return $this->filesystem->put_contents( $file, $css );
	}

	public function delete_used_css() {}
}
