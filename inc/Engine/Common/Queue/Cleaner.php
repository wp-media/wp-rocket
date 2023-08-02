<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\Queue;

class Cleaner extends \ActionScheduler_QueueCleaner {

	/**
	 * The duration of clean Hour In seconds.
	 *
	 * @var int
	 */
	protected $hour_in_seconds = 60 * 60;

	/**
	 * Store instance.
	 *
	 * @var \ActionScheduler_Store
	 */
	private $store = null;

	/**
	 * Group name to be cleaned.
	 *
	 * @var string
	 */
	private $group;

	/**
	 * Cleaner constructor.
	 *
	 * @param \ActionScheduler_Store|null $store The store instance.
	 * @param int                         $batch_size The batch size.
	 * @param string                      $group Current queue group.
	 */
	public function __construct( \ActionScheduler_Store $store = null, $batch_size = 20, $group = '' ) {
		parent::__construct( $store, $batch_size );
		$this->store = $store ? $store : \ActionScheduler_Store::instance();
		$this->group = $group;
	}

	/**
	 * Overrides the base method of action scheduler to do the clean process for our actions only.
	 *
	 * @return void
	 */
	public function delete_old_actions() {
		$lifespan = (int) apply_filters( 'action_scheduler_retention_period', $this->hour_in_seconds );// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		/**
		 * Filters the retention period for our tasks only.
		 *
		 * @since 3.11.0.5
		 *
		 * @param int $lifespan Lifespan in seconds.
		 *
		 * @return int
		 */
		$lifespan = (int) apply_filters( 'rocket_action_scheduler_retention_period', $lifespan, $this->group );
		$cutoff   = as_get_datetime_object( $lifespan . ' seconds ago' );

		$statuses_to_purge = [
			\ActionScheduler_Store::STATUS_COMPLETE,
			\ActionScheduler_Store::STATUS_CANCELED,
		];
		foreach ( $statuses_to_purge as $status ) {
			$actions_to_delete = $this->store->query_actions(
				[
					'status'           => $status,
					'modified'         => $cutoff,
					'modified_compare' => '<=',
					'per_page'         => $this->get_batch_size(),
					'orderby'          => 'none',
					'group'            => $this->group,
				]
			);

			foreach ( $actions_to_delete as $action_id ) {
				try {
					$this->store->delete_action( $action_id );
				} catch ( \Exception $e ) {

					/**
					 * Notify 3rd party code of exceptions when deleting a completed action older than the retention period
					 *
					 * This hook provides a way for 3rd party code to log or otherwise handle exceptions relating to their
					 * actions.
					 *
					 * @since 2.0.0
					 *
					 * @param int $action_id The scheduled actions ID in the data store
					 * @param \Exception $e The exception thrown when attempting to delete the action from the data store
					 * @param int $lifespan The retention period, in seconds, for old actions
					 * @param int $count_of_actions_to_delete The number of old actions being deleted in this batch
					 */
					do_action( 'action_scheduler_failed_old_action_deletion', $action_id, $e, $lifespan, count( $actions_to_delete ) );// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				}
			}
		}
	}

}
