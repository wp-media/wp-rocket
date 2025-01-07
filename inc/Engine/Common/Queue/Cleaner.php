<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\Queue;

use ActionScheduler_QueueCleaner;
use ActionScheduler_Store;

class Cleaner extends ActionScheduler_QueueCleaner {

	/**
	 * The duration of clean Hour In seconds.
	 *
	 * @var int
	 */
	protected $hour_in_seconds = 60 * 60;

	/**
	 * Default list of statuses purged by the cleaner process.
	 *
	 * @var string[]
	 */
	private $default_statuses_to_purge = [
		ActionScheduler_Store::STATUS_COMPLETE,
		ActionScheduler_Store::STATUS_CANCELED,
	];

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
	 * @param ActionScheduler_Store|null $store The store instance.
	 * @param int                        $batch_size The batch size.
	 * @param string                     $group Current queue group.
	 */
	public function __construct( ActionScheduler_Store $store = null, $batch_size = 20, $group = '' ) {
		parent::__construct( $store, $batch_size );
		$this->store = $store ? $store : ActionScheduler_Store::instance();
		$this->group = $group;
	}

	/**
	 * Overrides the base method of action scheduler to do the clean process for our actions only.
	 *
	 * @return array
	 */
	public function delete_old_actions() {
		/**
		 * Filter the minimum scheduled date age for action deletion.
		 *
		 * @param int $retention_period Minimum scheduled age in seconds of the actions to be deleted.
		 */
		$lifespan = (int) apply_filters( 'action_scheduler_retention_period', $this->hour_in_seconds ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		/**
		 * Filters the retention period for our tasks only.
		 *
		 * @since 3.11.0.5
		 *
		 * @param int    $lifespan Lifespan in seconds.
		 * @param string $group The group name.
		 *
		 * @return int
		 */
		$lifespan = (int) apply_filters( 'rocket_action_scheduler_retention_period', $lifespan, $this->group );

		try {
			$cutoff = as_get_datetime_object( $lifespan . ' seconds ago' );
		} catch ( \Exception $e ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf(
					/* Translators: %s is the exception message. */
					esc_html__( 'It was not possible to determine a valid cut-off time: %s.', 'rocket' ),
					esc_html( $e->getMessage() )
				),
				'3.5.5'
			);

			return [];
		}

		$statuses_to_purge = [
			\ActionScheduler_Store::STATUS_COMPLETE,
			\ActionScheduler_Store::STATUS_CANCELED,
			\ActionScheduler_Store::STATUS_FAILED,
		];

		return $this->clean_actions( $statuses_to_purge, $cutoff, $this->get_batch_size() );
	}

	/**
	 * Delete selected actions limited by status and date.
	 *
	 * @param string[]  $statuses_to_purge List of action statuses to purge. Defaults to canceled, complete.
	 * @param \DateTime $cutoff_date Date limit for selecting actions. Defaults to 31 days ago.
	 * @param int|null  $batch_size Maximum number of actions per status to delete. Defaults to 20.
	 * @param string    $context Calling process context. Defaults to `old`.
	 * @return array Actions deleted.
	 */
	public function clean_actions( array $statuses_to_purge, \DateTime $cutoff_date, $batch_size = null, $context = 'old' ) {
		$batch_size = null !== $batch_size ? $batch_size : $this->batch_size;
		$cutoff     = $cutoff_date;
		$lifespan   = time() - $cutoff->getTimestamp();

		if ( empty( $statuses_to_purge ) ) {
			$statuses_to_purge = $this->default_statuses_to_purge;
		}

		$deleted_actions = [];
		foreach ( $statuses_to_purge as $status ) {
			$actions_to_delete = $this->store->query_actions(
				[
					'status'           => $status,
					'modified'         => $cutoff,
					'modified_compare' => '<=',
					'per_page'         => $batch_size,
					'orderby'          => 'none',
					'group'            => $this->group,
				]
			);

			$deleted_actions = array_merge( $deleted_actions, $this->delete_actions( $actions_to_delete, $lifespan, $context ) );
		}

		return $deleted_actions;
	}

	/**
	 * Delete actions
	 *
	 * @param int[]  $actions_to_delete List of action IDs to delete.
	 * @param int    $lifespan Minimum scheduled age in seconds of the actions being deleted.
	 * @param string $context Context of the delete request.
	 * @return array Deleted action IDs.
	 */
	private function delete_actions( array $actions_to_delete, $lifespan = null, $context = 'old' ) {
		$deleted_actions = [];
		if ( null === $lifespan ) {
			$lifespan = $this->hour_in_seconds;
		}

		foreach ( $actions_to_delete as $action_id ) {
			try {
				$this->store->delete_action( $action_id );
				$deleted_actions[] = $action_id;
			} catch ( \Exception $e ) {
				/**
				 * Notify 3rd party code of exceptions when deleting a completed action older than the retention period
				 *
				 * This hook provides a way for 3rd party code to log or otherwise handle exceptions relating to their
				 * actions.
				 *
				 * @param int $action_id The scheduled actions ID in the data store
				 * @param \Exception $e The exception thrown when attempting to delete the action from the data store
				 * @param int $lifespan The retention period, in seconds, for old actions
				 * @param int $count_of_actions_to_delete The number of old actions being deleted in this batch
				 * @since 2.0.0
				 */
				do_action( "action_scheduler_failed_{$context}_action_deletion", $action_id, $e, $lifespan, count( $actions_to_delete ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			}
		}
		return $deleted_actions;
	}
}
