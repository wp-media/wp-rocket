<?php

/**
 * Class ActionScheduler_QueueCleaner
 */
class ActionScheduler_QueueCleaner {
	/**
	 * The cleaner action hook is scheduled to run daily to initiate cleanup.
	 *
	 * @var string
	 */
	private const RUN_SCHEDULED_CLEANER_HOOK = 'action_scheduler_run_actions_cleanup_hook';

	/**
	 * Hook used to keep deleting old actions in batches, with at most one continuation pending at a time.
	 *
	 * @var string
	 */
	private const CONTINUE_SCHEDULED_CLEANER_HOOK = 'action_scheduler_continue_actions_cleanup_hook';

	/**
	 * The batch size.
	 *
	 * @var int
	 */
	protected $batch_size;

	/**
	 * ActionScheduler_Store instance.
	 *
	 * @var ActionScheduler_Store
	 */
	private $store = null;

	/**
	 * 31 days in seconds.
	 *
	 * @var int
	 */
	private $month_in_seconds = 2678400;

	/**
	 * Default list of statuses purged by the cleaner process.
	 *
	 * @var string[]
	 */
	private $default_statuses_to_purge = array(
		ActionScheduler_Store::STATUS_COMPLETE,
		ActionScheduler_Store::STATUS_CANCELED,
	);

	/**
	 * ActionScheduler_QueueCleaner constructor.
	 *
	 * @param ActionScheduler_Store|null $store      The store instance.
	 * @param int                        $batch_size The batch size.
	 */
	public function __construct( ?ActionScheduler_Store $store = null, $batch_size = 20 ) {
		$this->store      = $store ? $store : ActionScheduler_Store::instance();
		$this->batch_size = $batch_size;
	}

	/**
	 * Registers action hooks to perform action deletions as a separate task.
	 *
	 * @since 4.0.0
	 * @internal
	 *
	 * @return void
	 */
	public function register_cleaner_hooks() {
		add_action( self::RUN_SCHEDULED_CLEANER_HOOK, array( $this, 'delete_old_actions' ) );
		add_action( self::CONTINUE_SCHEDULED_CLEANER_HOOK, array( $this, 'delete_old_actions' ) );
		add_action( 'action_scheduler_ensure_recurring_actions', array( $this, 'register_recurring_actions' ) );
	}

	/**
	 * Register the recurring action deletion task.
	 *
	 * @since 4.0.0
	 * @internal
	 *
	 * @return void
	 */
	public function register_recurring_actions() {
		if ( ! as_has_scheduled_action( self::RUN_SCHEDULED_CLEANER_HOOK ) ) {
			$date = ActionScheduler_TimezoneHelper::set_local_timezone( new DateTime() )->modify( 'tomorrow 3am' );
			as_schedule_recurring_action(
				$date->getTimestamp(),
				DAY_IN_SECONDS,
				self::RUN_SCHEDULED_CLEANER_HOOK,
				array(),
				'ActionScheduler',
				true,
				0
			);
		}
	}

	/**
	 * Performs action deletions by aggregating configurations and coordinating clean_actions as needed.
	 *
	 * @since 4.0.0 by default, failed actions are removed after three months.
	 * @return array
	 */
	public function delete_old_actions() {
		/**
		 * Filter the minimum scheduled date age for action deletion.
		 *
		 * @param int $retention_period Minimum scheduled age in seconds of the actions to be deleted.
		 */
		$lifespan = apply_filters( 'action_scheduler_retention_period', $this->month_in_seconds );
		$lifespan = is_numeric( $lifespan ) ? max( 0, (int) $lifespan ) : $this->month_in_seconds;

		/**
		 * Set the retention period in seconds for actions with a failed status. If the action_scheduler_default_cleaner_statuses filter includes
		 * a failed status, this filter result will be ignored, and the retention period for failed actions will match that of other statuses.
		 *
		 * @param int $retention_period Retention period in seconds.
		 */
		$lifespan_failed = apply_filters( 'action_scheduler_retention_period_for_failed', 3 * $this->month_in_seconds );
		$lifespan_failed = is_numeric( $lifespan_failed ) ? max( 0, (int) $lifespan_failed ) : 3 * $this->month_in_seconds;
		// We considered 12-month, 3-month, and 1-month options for failed action retention and selected a 3-month period
		// to align with the quarterly accounting cycle. Store owners may adjust the retention period to achieve PCI DSS
		// compliance or to align with a different accounting cycle, as needed.

		try {
			$cutoff_failed = as_get_datetime_object( $lifespan_failed . ' seconds ago' );
			$cutoff        = as_get_datetime_object( $lifespan . ' seconds ago' );
		} catch ( Exception $e ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf(
					/* Translators: %s is the exception message. */
					esc_html__( 'It was not possible to determine a valid cut-off time: %s.', 'action-scheduler' ),
					esc_html( $e->getMessage() )
				),
				'3.5.5'
			);

			return array();
		}

		/**
		 * Filter the statuses when cleaning the queue.
		 *
		 * @param string[] $default_statuses_to_purge Action statuses to clean.
		 */
		$statuses_to_purge = apply_filters( 'action_scheduler_default_cleaner_statuses', $this->default_statuses_to_purge );
		// Only an explicit empty array disables the purge; a non-array (e.g. a filter that forgot to return) falls back to the defaults.
		if ( ! is_array( $statuses_to_purge ) ) {
			$statuses_to_purge = $this->default_statuses_to_purge;
		}

		/**
		 * Filter whether failed actions are purged. Return false to disable failed action cleanup.
		 *
		 * @since 4.0.0
		 *
		 * @param bool $enabled Whether failed actions should be purged. Default true.
		 */
		$clean_failed = (bool) apply_filters( 'action_scheduler_enable_failed_action_cleanup', true );

		$deleted_failed_entries = array();
		// Backward compatibility note: if store already purging the failed statuses, don't change the behaviour.
		if ( $clean_failed && ! in_array( ActionScheduler_Store::STATUS_FAILED, $statuses_to_purge, true ) ) {
			// Use a fixed default batch size to ensure that the cleanup of failed actions does not interfere with the regular cleanup.
			$deleted_failed_entries = $this->clean_actions( array( ActionScheduler_Store::STATUS_FAILED ), $cutoff_failed, 20 );
		}

		$deleted_entries = array();
		if ( ! empty( $statuses_to_purge ) ) {
			$deleted_entries = $this->clean_actions( $statuses_to_purge, $cutoff, $this->get_batch_size() );
		}

		return array_merge( $deleted_failed_entries, $deleted_entries );
	}

	/**
	 * Delete selected actions based on status and date. The function's behavior depends on the context:
	 * - For scheduled cleanup actions, the function operates within execution budget constraints optimized for high-traffic stores.
	 * - Otherwise, it strictly follows the provided parameters without the scheduled cleanup optimizations.
	 *
	 * @param string[] $statuses_to_purge List of action statuses to purge. Defaults to canceled, complete.
	 * @param DateTime $cutoff_date       Date limit for selecting actions. Defaults to 31 days ago.
	 * @param int|null $batch_size        Maximum number of actions per status to delete. Defaults to 20.
	 * @param string   $context           Calling process context. Defaults to `old`.
	 *
	 * @return array Actions deleted.
	 */
	public function clean_actions( array $statuses_to_purge, DateTime $cutoff_date, $batch_size = null, $context = 'old' ) {
		$batch_size        = ! is_null( $batch_size ) ? $batch_size : $this->batch_size;
		$cutoff            = ! is_null( $cutoff_date ) ? $cutoff_date : as_get_datetime_object( $this->month_in_seconds . ' seconds ago' );
		$lifespan          = time() - $cutoff->getTimestamp();
		$statuses_to_purge = empty( $statuses_to_purge ) ? $this->default_statuses_to_purge : $statuses_to_purge;

		// When deletion is performed as a separate action, we can enforce a minimum batch size to achieve consistent deletion throughput.
		// For inline cleanup during a queue run, the batch size should remain unchanged to avoid increasing the process footprint.
		$is_scheduled_cleanup = doing_action( self::RUN_SCHEDULED_CLEANER_HOOK )
			|| doing_action( self::CONTINUE_SCHEDULED_CLEANER_HOOK );
		// 250 balances replication safety, backlog clearance speed, and claim slot duration on high-volume stores.
		$iteration_batch_size       = $is_scheduled_cleanup ? max( 250, $batch_size ) : $batch_size;
		$iteration_unused_budget    = 0;
		$continue_scheduled_cleanup = false;
		if ( $is_scheduled_cleanup ) {
			// Sort the statuses to optimize execution budget usage based on the typical status distribution.
			usort(
				$statuses_to_purge,
				static function( $a, $b ) {
					// Place the 'canceled' status first to help ensure that any unspent execution budget can be used for processing other statuses.
					if ( ActionScheduler_Store::STATUS_CANCELED === $a ) {
						return -1;
					}
					if ( ActionScheduler_Store::STATUS_CANCELED === $b ) {
						return 1;
					}

					// Place the 'complete' status at the end to use any remaining execution budget for processing.
					if ( ActionScheduler_Store::STATUS_COMPLETE === $a ) {
						return 1;
					}
					if ( ActionScheduler_Store::STATUS_COMPLETE === $b ) {
						return -1;
					}

					return 0;
				}
			);
		}

		$deleted_actions = array();
		foreach ( $statuses_to_purge as $status ) {
			$iteration_execution_budget = $iteration_batch_size + $iteration_unused_budget;
			$actions_to_delete          = $this->store->query_actions(
				array(
					'status'           => $status,
					'modified'         => $cutoff,
					'modified_compare' => '<=',
					'per_page'         => $iteration_execution_budget,
					'orderby'          => 'none',
				)
			);
			$deleted_actions[]          = $this->delete_actions( $actions_to_delete, $lifespan, $context );

			$fetched_actions_count      = count( $actions_to_delete );
			$iteration_unused_budget    = $is_scheduled_cleanup ? ( $iteration_execution_budget - $fetched_actions_count ) : 0;
			$continue_scheduled_cleanup = $continue_scheduled_cleanup || ( $iteration_execution_budget === $fetched_actions_count );
		}

		// When called from the scheduled cleanup hook, unique=true prevents duplicates at the SQL level. When called
		// from a continuation, that same flag would match the running entry, so check for a pending continuation first.
		$called_from_run = doing_action( self::RUN_SCHEDULED_CLEANER_HOOK );
		if ( $is_scheduled_cleanup && $continue_scheduled_cleanup && ( $called_from_run || ! $this->has_pending_continuation() ) ) {
			as_schedule_single_action( time(), self::CONTINUE_SCHEDULED_CLEANER_HOOK, array(), 'ActionScheduler', $called_from_run, 0 );
		}

		return array_merge( array(), ...$deleted_actions );
	}

	/**
	 * Whether a continuation of the cleanup is already queued.
	 *
	 * @return bool
	 */
	private function has_pending_continuation() {
		$pending = as_get_scheduled_actions(
			array(
				'hook'     => self::CONTINUE_SCHEDULED_CLEANER_HOOK,
				'status'   => ActionScheduler_Store::STATUS_PENDING,
				'per_page' => 1,
			),
			'ids'
		);

		return ! empty( $pending );
	}

	/**
	 * Delete actions.
	 *
	 * @param int[]  $actions_to_delete List of action IDs to delete.
	 * @param int    $lifespan          Minimum scheduled age in seconds of the actions being deleted.
	 * @param string $context           Context of the delete request.
	 *
	 * @return int[] Deleted action IDs.
	 */
	private function delete_actions( array $actions_to_delete, $lifespan, $context = 'old' ) {
		$deleted_actions = array();
		foreach ( $actions_to_delete as $action_id ) {
			try {
				$this->store->delete_action( $action_id );
				$deleted_actions[] = $action_id;
			} catch ( Exception $e ) {
				/**
				 * Notify 3rd party code of exceptions when deleting a completed action older than the retention period
				 *
				 * This hook provides a way for 3rd party code to log or otherwise handle exceptions relating to their
				 * actions.
				 *
				 * @param int $action_id The scheduled actions ID in the data store
				 * @param Exception $e The exception thrown when attempting to delete the action from the data store
				 * @param int $lifespan The retention period, in seconds, for old actions
				 * @param int $count_of_actions_to_delete The number of old actions being deleted in this batch
				 * @since 2.0.0
				 */
				do_action( "action_scheduler_failed_{$context}_action_deletion", $action_id, $e, $lifespan, count( $actions_to_delete ) );
			}
		}
		return $deleted_actions;
	}

	/**
	 * Unclaim pending actions that have not been run within a given time limit.
	 *
	 * When called by ActionScheduler_Abstract_QueueRunner::run_cleanup(), the time limit passed
	 * as a parameter is 10x the time limit used for queue processing.
	 *
	 * @param int $time_limit The number of seconds to allow a queue to run before unclaiming its pending actions. Default 300 (5 minutes).
	 */
	public function reset_timeouts( $time_limit = 300 ) {
		$timeout = apply_filters( 'action_scheduler_timeout_period', $time_limit );

		if ( $timeout < 0 ) {
			return;
		}

		$cutoff           = as_get_datetime_object( $timeout . ' seconds ago' );
		$actions_to_reset = $this->store->query_actions(
			array(
				'status'           => ActionScheduler_Store::STATUS_PENDING,
				'modified'         => $cutoff,
				'modified_compare' => '<=',
				'claimed'          => true,
				'per_page'         => $this->get_batch_size(),
				'orderby'          => 'none',
			)
		);

		foreach ( $actions_to_reset as $action_id ) {
			$this->store->unclaim_action( $action_id );
			do_action( 'action_scheduler_reset_action', $action_id );
		}
	}

	/**
	 * Mark actions that have been running for more than a given time limit as failed, based on
	 * the assumption some uncatchable and unloggable fatal error occurred during processing.
	 *
	 * When called by ActionScheduler_Abstract_QueueRunner::run_cleanup(), the time limit passed
	 * as a parameter is 10x the time limit used for queue processing.
	 *
	 * @param int $time_limit The number of seconds to allow an action to run before it is considered to have failed. Default 300 (5 minutes).
	 */
	public function mark_failures( $time_limit = 300 ) {
		$timeout = apply_filters( 'action_scheduler_failure_period', $time_limit );

		if ( $timeout < 0 ) {
			return;
		}

		$cutoff           = as_get_datetime_object( $timeout . ' seconds ago' );
		$actions_to_reset = $this->store->query_actions(
			array(
				'status'           => ActionScheduler_Store::STATUS_RUNNING,
				'modified'         => $cutoff,
				'modified_compare' => '<=',
				'per_page'         => $this->get_batch_size(),
				'orderby'          => 'none',
			)
		);

		foreach ( $actions_to_reset as $action_id ) {
			$this->store->mark_failure( $action_id );
			do_action( 'action_scheduler_failed_action', $action_id, $timeout );
		}
	}

	/**
	 * Do all of the cleaning actions.
	 *
	 * @param int $time_limit The number of seconds to use as the timeout and failure period. Default 300 (5 minutes).
	 */
	public function clean( $time_limit = 300 ) {
		$this->delete_old_actions();
		$this->reset_timeouts( $time_limit );
		$this->mark_failures( $time_limit );
	}

	/**
	 * Get the batch size for cleaning the queue.
	 *
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
