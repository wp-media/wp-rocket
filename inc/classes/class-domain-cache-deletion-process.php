<?php

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Background process for deleting invalidated domain cache directories
 *
 * @since 3.2.3
 * @author dotSILENT
 *
 * @see WP_Background_Process
 */
class Domain_Cache_Deletion_Process extends \WP_Background_Process {
	/**
	 * Prefix
	 *
	 * @since 3.2.3
	 * @author dotSILENT
	 *
	 * @var string
	 */
	protected $prefix = 'rocket';

	/**
	 * Specific action identifier for domain cache deletion
	 *
	 * @since 3.2.3
	 * @author dotSILENT
	 *
	 * @var string
	 */
	protected $action = 'domain_cache_delete';

	/**
	 * Recursively delete the directory provided in $item
	 *
	 * @since 3.2.3
	 * @author dotSILENT
	 *
	 * @param mixed $item Queue item to iterate over.
	 * @return null
	 */
	protected function task( $item ) {
		$dir_path = untrailingslashit( WP_ROCKET_CACHE_PATH . $item );

		if( strlen( $item ) > 0 && rocket_direct_filesystem()->exists( $dir_path ) ) {
			rocket_rrmdir( $dir_path );
			// Double check if the directory still exists and if it does, push the item back to the queue
			if( rocket_direct_filesystem()->exists ( $dir_path ) ) {
				return $item;
			}
		}
		return false;
	}
}

