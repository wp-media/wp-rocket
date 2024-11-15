<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WP_Filesystem_Direct;

abstract class AbstractFileSystem {
	/**
	 * WP Filesystem instance.
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

	/**
	 * Constructor method.
	 * Initializes a new instance of the Controller class.
	 *
	 * @param WP_Filesystem_Direct $filesystem Filesystem class.
	 */
	public function __construct( $filesystem = null ) {
		$this->filesystem = $filesystem ?? rocket_direct_filesystem();
	}

	/**
	 * Write to file.
	 *
	 * @param string $file_path File path to store the file.
	 * @param string $content   File content(data).
	 *
	 * @return bool
	 */
	protected function write_file( string $file_path, string $content ): bool {
		return $this->filesystem->put_contents( $file_path, $content, rocket_get_filesystem_perms( 'file' ) );
	}

	/**
	 * Get the content of a file
	 *
	 * @param string $file The file content to get.
	 *
	 * @return string
	 */
	protected function get_file_content( string $file ): string {
		return $this->filesystem->get_contents( $file );
	}

	/**
	 * Delete file from a directory
	 *
	 * @param string $file_path Path to file that would be deleted.
	 *
	 * @return bool
	 */
	protected function delete_file( string $file_path ): bool {
		return $this->filesystem->delete( $file_path, false, 'f' );
	}

	/**
	 * Checks if the dir path is writable and create dir if it doesn't exist.
	 *
	 * @param string $dir_path The directory to check.
	 *
	 * @return bool
	 */
	protected function is_folder_writable( string $dir_path ): bool {
		if ( ! $this->filesystem->exists( $dir_path ) ) {
			rocket_mkdir_p( $dir_path );
		}

		return $this->filesystem->is_writable( $dir_path );
	}

	/**
	 * Deletes all files in a given directory
	 *
	 * @param string $dir_path The directory path.
	 *
	 * @return void
	 */
	public function delete_all_files_from_directory( $dir_path ): void {
		try {
			$dir = new RecursiveDirectoryIterator( $dir_path, \FilesystemIterator::SKIP_DOTS );

			$items = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::CHILD_FIRST );

			foreach ( $items as $item ) {
				$this->filesystem->delete( $item );
			}
		} catch ( \Exception $e ) {
			return;
		}
	}
}
