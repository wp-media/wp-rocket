<?php

namespace WP_Rocket\Tests;

trait GlobTrait {

	public function deleteDomainCallback( $root ) {
		var_dump( $root );
		exit;
		$root = rtrim( $root, '*' );
		$this->delete_files( $root );
	}

	/**
	 * Recursively deletes all the files in the given virtual directory.
	 *
	 * @param string $dir Virtual directory absolute path.
	 */
	protected function deleteFiles( $dir ) {
		foreach ( $this->scandir( $dir ) as $item ) {
			if ( $this->filesystem->is_dir( $item ) ) {
				$this->delete_files( $item );
			} else {
				$this->filesystem->delete( $item );
			}
		}
	}
}
