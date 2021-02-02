<?php
namespace WP_Rocket\Storage;

/**
 * Handle page cache.
 *
 * @since  3.9
 */
class FilesystemDirect extends Abstract_Storage {

	/**
	 * Outputs a file.
	 *
	 * @since  3.9
	 */
	public function readfile( $filename, $use_include_path = false, $context = null ) {
		return readfile( $filename, $use_include_path, $context );
	}

	/**
	 * Output a gz-file.
	 *
	 * @since  3.9
	 */
	public function readgzfile( $filename, $use_include_path = 0 ) {
		return readgzfile( $filename, $use_include_path );
	}
}
