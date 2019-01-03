<?php
namespace WP_Rocket\Subscriber;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for domain cache deletion background process
 *
 * @since 3.2.3
 * @author dotSILENT
 */
class Domain_Cache_Deletion_Subscriber implements Subscriber_Interface {
	/**
	 * Domain cache deletion background process instance
	 *
	 * @since 3.2.3
	 * @author dotSILENT
	 *
	 * @var Domain_Cache_Deletion_Process
	 */
	private $process;

	/**
	 * Stores the names of invalidated directories
	 *
	 * @since 3.2.3
	 * @author dotSILENT
	 *
	 * @var array
	 */
	private $invalidated_dirs = [];

	/**
	 * Constructor
	 *
	 * @since 3.2.3
	 * @author dotSILENT
	 *
	 * @param Domain_Cache_Deletion_Process $process Instance of the domain cache deletion process
	 */
	public function __construct( \Domain_Cache_Deletion_Process $process ) {
		$this->process = $process;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'after_rocket_invalidate_dir' => [ 'delete_after_invalidate_dir', 10, 2 ],
			'shutdown'                    => [ 'maybe_dispatch', 0 ],
		];
	}

	/**
	 * Queues the invalidated directory for deletion
	 *
	 * @since 3.2.3
	 *
	 * @param string $oldName Old name (full path) of the directory
	 * @param string $newName New, invalidated name (full path) of the directory
	 * @return void
	 */
	public function delete_after_invalidate_dir( $oldName, $newName ) {
		// Store only the path relative to WP_ROCKET_CACHE_PATH, it's safer this way because the process can only delete wp-rocket relative paths
		$this->invalidated_dirs[] = str_replace( WP_ROCKET_CACHE_PATH, '', $newName );
	}

	/**
	 * Starts the deletion process if needed
	 *
	 * @since 3.2.3
	 * @author dotSILENT
	 *
	 * @return void
	 */
	public function maybe_dispatch() {
		if ( empty( $this->invalidated_dirs ) ) {
			return;
		}

		foreach ( $this->invalidated_dirs as $dir ) {
			$this->process->push_to_queue( $dir );
		}

		$this->process->save()->dispatch();
	}
}
