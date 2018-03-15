<?php

/**
 * Class ActionScheduler_QueueCleaner
 */
class ActionScheduler_QueueCleaner {

	/** @var int */
	protected $batch_size;

	/** @var ActionScheduler_Store */
	private $store = null;

	/**
	 * 31 days in seconds.
	 *
	 * @var int
	 */
	private $month_in_seconds = 2678400;

	/**
	 * Five minutes in seconds
	 *
	 * @var int
	 */
	private $five_minutes = 300;

	/**
	 * ActionScheduler_QueueCleaner constructor.
	 *
	 * @param ActionScheduler_Store $store      The store instance.
	 * @param int                   $batch_size The batch size.
	 */
	public function __construct( ActionScheduler_Store $store = null, $batch_size = 20 ) {
		$this->store = $store ? $store : ActionScheduler_Store::instance();
		$this->batch_size = $batch_size;
	}

	public function delete_old_actions() {
		$lifespan = apply_filters( 'action_scheduler_retention_period', $this->month_in_seconds );
		$cutoff = as_get_datetime_object($lifespan.' seconds ago');

		$statuses_to_purge = array(
			ActionScheduler_Store::STATUS_COMPLETE,
			ActionScheduler_Store::STATUS_CANCELED,
		);
		foreach ( $statuses_to_purge as $status ) {
			$actions_to_delete = $this->store->query_actions( array(
				'status'           => $status,
				'modified'         => $cutoff,
				'modified_compare' => '<=',
				'per_page'         => $this->get_batch_size(),
			) );

			foreach ( $actions_to_delete as $action_id ) {
				$this->store->delete_action( $action_id );
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
			'status'           => ActionScheduler_Store::STATUS_PENDING,
			'modified'         => $cutoff,
			'modified_compare' => '<=',
			'claimed'          => true,
			'per_page'         => $this->get_batch_size(),
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
			'status'           => ActionScheduler_Store::STATUS_RUNNING,
			'modified'         => $cutoff,
			'modified_compare' => '<=',
			'per_page'         => $this->get_batch_size(),
		) );

		foreach ( $actions_to_reset as $action_id ) {
			$this->store->mark_failure( $action_id );
			do_action( 'action_scheduler_failed_action', $action_id, $timeout );
		}
	}

	/**
	 * Do all of the cleaning actions.
	 *
	 * @author Jeremy Pry
	 */
	public function clean() {
		$this->delete_old_actions();
		$this->reset_timeouts();
		$this->mark_failures();
	}

	/**
	 * Get the batch size for cleaning the queue.
	 *
	 * @author Jeremy Pry
	 * @return int
	 */
	protected function get_batch_size() {
		/**
		 * Filter the batch size when cleaning the queue.
		 *
		 * @param int $batch_size The number of actions to clean in one batch.
		 */
		return absint( apply_filters( 'action_scheduler_cleanup_batch_size', $this->batch_size ) );
	}
}
