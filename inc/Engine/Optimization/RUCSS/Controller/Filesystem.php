<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Controller;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Filesystem {
	/**
	 * WP Filesystem instance
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Path to the used CSS storage
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Instantiate the class
	 *
	 * @param string               $base_path Base path to the used CSS storage.
	 * @param WP_Filesystem_Direct $filesystem WP Filesystem instance.
	 */
	public function __construct( $base_path, $filesystem = null ) {
		$this->filesystem = is_null( $filesystem ) ? rocket_direct_filesystem() : $filesystem;
		$this->path       = $base_path . get_current_blog_id() . '/';
	}

	/**
	 * Gets the used CSS content corresponding to the provided hash
	 *
	 * @param string $hash Hash of the corresponding used CSS.
	 *
	 * @return string
	 */
	public function get_used_css( string $hash ): string {
		$file = $this->path . $this->hash_to_path( $hash ) . '.css.gz';

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
	public function write_used_css( string $hash, string $used_css ): bool {
		$file = $this->path . $this->hash_to_path( $hash ) . '.css.gz';

		if ( ! rocket_mkdir_p( dirname( $file ) ) ) {
			return false;
		}

		// This filter is documented in inc/classes/Buffer/class-cache.php.
		$css = gzencode( $used_css, apply_filters( 'rocket_gzencode_level_compression', 6 ) );

		if ( ! $css ) {
			return false;
		}

		return $this->filesystem->put_contents( $file, $css, rocket_get_filesystem_perms( 'file' ) );
	}

	/**
	 * Deletes the used CSS files for the corresponding hash
	 *
	 * @since 3.11.4
	 *
	 * @param string $hash md5 hash string.
	 *
	 * @return bool
	 */
	public function delete_used_css( string $hash ): bool {
		$file = $this->path . $this->hash_to_path( $hash ) . '.css.gz';

		return $this->filesystem->delete( $file, false, 'f' );
	}

	/**
	 * Deletes all the used CSS files
	 *
	 * @since 3.11.4
	 *
	 * @return void
	 */
	public function delete_all_used_css() {
		try {
			$dir = new RecursiveDirectoryIterator( $this->path, \FilesystemIterator::SKIP_DOTS );

			$items = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::CHILD_FIRST );

			foreach ( $items as $item ) {
				$this->filesystem->delete( $item );
			}
		} catch ( \Exception $e ) {
			return;
		}
	}

	/**
	 * Checks if the used CSS storage folder is writable
	 *
	 * @since 3.11.4
	 *
	 * @return bool
	 */
	public function is_writable_folder() {
		if ( ! $this->filesystem->exists( $this->path ) ) {
			rocket_mkdir_p( $this->path );
		}

		return $this->filesystem->is_writable( $this->path );
	}

	/**
	 * Converts hash to path with filtered number of levels
	 *
	 * @since 3.11.4
	 *
	 * @param string $hash md5 hash string.
	 *
	 * @return string
	 */
	private function hash_to_path( string $hash ): string {
		/**
		 * Filters the number of sub-folders level to create for used CSS storage
		 *
		 * @since 3.11.4
		 *
		 * @param int $levels Number of levels.
		 */
		$levels = apply_filters( 'rocket_used_css_dir_level', 3 );

		$base   = substr( $hash, 0, $levels );
		$remain = substr( $hash, $levels );

		$path_array   = str_split( $base );
		$path_array[] = $remain;

		return implode( '/', $path_array );
	}
}
