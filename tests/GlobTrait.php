<?php

namespace WP_Rocket\Tests;

use WPMedia\PHPUnit\VirtualFilesystemDirect;

trait GlobTrait {

	/**
	 * Delete domain callback.
	 *
	 * @param string $root Virtual root directory absolute path.
	 * @param string $dir  Virtual directory absolute path.
	 */
	public function deleteDomainCallback( $root, $filesystem ) {
		$root = rtrim( $root, '*' );
		$this->deleteFiles( $root, $filesystem );
	}

	/**
	 * Recursively deletes all the files in the given virtual directory.
	 *
	 * @param string                  $dir        Virtual directory absolute path.
	 * @param VirtualFilesystemDirect $filesystem Instance of the virtual filesystem.
	 */
	protected function deleteFiles( $dir, VirtualFilesystemDirect $filesystem ) {
		foreach ( $filesystem->getFilesListing( $dir ) as $file ) {
			$filesystem->delete( $file );
		}
	}
}
