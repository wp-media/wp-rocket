<?php

/**
 * Class ActionScheduler_QueueCleaner
 */
class ActionScheduler_QueueCleaner {
	/** @var ActionScheduler_Store */
	private $store = NULL;

	private $month_in_seconds = 2678400; // 31 days
	private $five_minutes = 300;

	public function __construct( ActionScheduler_Store $store = NULL ) {
		$this->store = $store ? $store : ActionScheduler_Store::instance();
	}

	public function delete_old_actions() {
		$lifespan = apply_filters( 'action_scheduler_retention_period', $this->month_in_seconds );
		$cutoff = as_get_datetime_object($lifespan.' seconds ago');

		$actions_to_delete = $this->store->query_actions( array(
			'status' => ActionScheduler_Store::STATUS_COMPLETE,
			'modified' => $cutoff,
			'modified_compare' => '<=',
			'per_page' => apply_filters( 'action_scheduler_cleanup_batch_size', 20 ),
		) );

		foreach ( $actions_to_delete as $action_id ) {
			try {
				$this->store->delete_action( $action_id );
			} catch ( Exception $e ) {

				/**
				 * Notify 3rd party code of exceptions when deleting a completed action older than the retention period
				 *
				 * This hook provides a way for 3rd party code to log or otherwise handle exceptions relating to their
				 * actions.
				 *
				 * @since 1.6.0
				 *
				 * @param int $action_id The scheduled actions ID in the data store
				 * @param Exception $e The exception thrown when attempting to delete the action from the data store
				 * @param int $lifespan The retention period, in seconds, for old actions
				 * @param int $count_of_actions_to_delete The number of old actions being deleted in this batch
				 */
				do_action( 'action_scheduler_failed_old_action_deletion', $action_id, $e, $lifespan, count( $actions_to_delete ) );
			}
		}
	}

	public function reset_timeouts() {
		$timeout = apply_filters( 'action_scheduler_timeout_period', $this->five_minutes );
		if ( $timeout < 0 ) {
			return;
		}
		$cutoff = as_get_datetime_object($timeout.' seconds ago');
		$actions_to_reset = $this->store->query_actions( array(
			'status' => ActionScheduler_Store::STATUS_PENDING,
			'modified' => $cutoff,
			'modified_compare' => '<=',
			'claimed' => TRUE,
			'per_page' => apply_filters( 'action_scheduler_cleanup_batch_size', 20 ),
		) );

		foreach ( $actions_to_reset as $action_id ) {
			$this->store->unclaim_action( $action_id );
			do_action( 'action_scheduler_reset_action', $action_id );
		}
	}

	public function mark_failures() {
		$timeout = apply_filters( 'action_scheduler_failure_period', $this->five_minutes );
		if ( $timeout < 0 ) {
			return;
		}
		$cutoff = as_get_datetime_object($timeout.' seconds ago');
		$actions_to_reset = $this->store->query_actions( array(
			'status' => ActionScheduler_Store::STATUS_RUNNING,
			'modified' => $cutoff,
			'modified_compare' => '<=',
			'per_page' => apply_filters( 'action_scheduler_cleanup_batch_size', 20 ),
		) );

		foreach ( $actions_to_reset as $action_id ) {
			$this->store->mark_failure( $action_id );
			do_action( 'action_scheduler_failed_action', $action_id, $timeout );
		}
	}
}
 