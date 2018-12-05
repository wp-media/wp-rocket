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
	 * @return false|string
	 */
	protected function task( $item ) {
		$dir_path = untrailingslashit( WP_ROCKET_CACHE_PATH . $item );

		if( strlen( $item ) > 0 && rocket_direct_filesystem()->exists( $dir_path ) ) {
			rocket_rrmdir( $dir_path );

			$transient = $this->get_dir_transient_name( 'retry_count', $item );
			// Double check if the directory still exists and if it does, push the item back to the queue
			if( rocket_direct_filesystem()->exists ( $dir_path ) ) {

				$retry_count = get_transient( $transient );
				$retry_count = ( $retry_count === false || !$retry_count ) ? 1 : $retry_count + 1;

				// Remove the item from the queue if the directory still exists after 20 retries, prevents any possible infinite looping
				if( $retry_count >= 20 ) {
					delete_transient( $transient );
					return false;
				}

				set_transient( $transient, $retry_count );
				// Push back
				return $item;
			}
			
			delete_transient( $transient );
		}
		return false;
	}

	/**
	 * Get a unique transient name for specified directory to use with transients
	 *
	 * @param string $name Name to distinguish different transient types
	 * @param string $dir Directory name (queue item)
	 * @return string
	 */
	protected function get_dir_transient_name( $name, $dir ) {
		return 'rocket_delete_cache_' . $name . '_' . md5( $dir );
	}
}

