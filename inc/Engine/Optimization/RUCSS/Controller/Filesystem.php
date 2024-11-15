<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Controller;

use WP_Filesystem_Direct;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WP_Rocket\Engine\Common\AbstractFileSystem;

class Filesystem extends AbstractFileSystem {
	/**
	 * WP Filesystem instance
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

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
		parent::__construct( is_null( $filesystem ) ? rocket_direct_filesystem() : $filesystem );
		$this->path = $base_path . get_current_blog_id() . '/';
	}

	/**
	 * Get the file path for the used CSS file.
	 *
	 * @param string $hash Hash of the file contents.
	 *
	 * @return string Path for the used CSS file.
	 */
	private function get_usedcss_full_path( string $hash ) {
		return $this->path . $this->hash_to_path( $hash ) . '.css' . ( function_exists( 'gzdecode' ) ? '.gz' : '' );
	}

	/**
	 * Gets the used CSS content corresponding to the provided hash
	 *
	 * @param string $hash Hash of the corresponding used CSS.
	 *
	 * @return string
	 */
	public function get_used_css( string $hash ): string {
		$file = $this->get_usedcss_full_path( $hash );

		if ( ! $this->filesystem->exists( $file ) ) {
			return '';
		}

		$file_contents = $this->get_file_content( $file );
		$css           = function_exists( 'gzdecode' ) ? gzdecode( $file_contents ) : $file_contents;

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
		$file = $this->get_usedcss_full_path( $hash );

		if ( ! rocket_mkdir_p( dirname( $file ) ) ) {
			return false;
		}

		// This filter is documented in inc/classes/Buffer/class-cache.php.
		$css = function_exists( 'gzencode' ) ? gzencode( $used_css, apply_filters( 'rocket_gzencode_level_compression', 6 ) ) : $used_css;

		if ( ! $css ) {
			return false;
		}

		return $this->write_file( $file, $css );
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
		$file = $this->get_usedcss_full_path( $hash );

		return $this->delete_file( $file );
	}

	/**
	 * Deletes all the used CSS files
	 *
	 * @since 3.11.4
	 *
	 * @return void
	 */
	public function delete_all_used_css() {
		$this->delete_all_files_from_directory( $this->path );
	}

	/**
	 * Checks if the used CSS storage folder is writable
	 *
	 * @since 3.11.4
	 *
	 * @return bool
	 */
	public function is_writable_folder() {
		return $this->is_folder_writable( $this->path );
	}
}
