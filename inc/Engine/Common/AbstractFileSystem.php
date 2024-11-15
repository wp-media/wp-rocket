<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common;

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
	 * Checks if a given path exists in the filesystem.
	 *
	 * @param string $path The path to check.
	 * @return bool True if the path exists, false otherwise.
	 */
	public function exists( string $path ): bool {
		return $this->filesystem->exists( $path );
	}

	/**
	 * Retrieves the contents of a file at the given path.
	 *
	 * @param string $path The path to the file.
	 * @return string|false The file contents on success, false on failure.
	 */
	public function get_contents( string $path ) {
		return $this->filesystem->get_contents( $path );
	}
}
