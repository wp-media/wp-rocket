<?php

/**
 * Class ActionScheduler_QueueRunner
 */
class ActionScheduler_QueueRunner extends ActionScheduler_Abstract_QueueRunner {
	const WP_CRON_HOOK = 'action_scheduler_run_queue';

	const WP_CRON_SCHEDULE = 'every_minute';

	/** @var ActionScheduler_AsyncRequest_QueueRunner */
	protected $async_request;

	/** @var ActionScheduler_QueueRunner  */
	private static $runner = null;

	/**
	 * @return ActionScheduler_QueueRunner
	 * @codeCoverageIgnore
	 */
	public static function instance() {
		if ( empty(self::$runner) ) {
			$class = apply_filters('action_scheduler_queue_runner_class', 'ActionScheduler_QueueRunner');
			self::$runner = new $class();
		}
		return self::$runner;
	}

	/**
	 * ActionScheduler_QueueRunner constructor.
	 *
	 * @param ActionScheduler_Store             $store
	 * @param ActionScheduler_FatalErrorMonitor $monitor
	 * @param ActionScheduler_QueueCleaner      $cleaner
	 */
	public function __construct( ActionScheduler_Store $store = null, ActionScheduler_FatalErrorMonitor $monitor = null, ActionScheduler_QueueCleaner $cleaner = null, ActionScheduler_AsyncRequest_QueueRunner $async_request = null ) {
		parent::__construct( $store, $monitor, $cleaner );
		$this->async_request = new ActionScheduler_AsyncRequest_QueueRunner( $this->store );

	}

	/**
	 * @codeCoverageIgnore
	 */
	public function init() {

		add_filter( 'cron_schedules', array( self::instance(), 'add_wp_cron_schedule' ) );

		if ( !wp_next_scheduled(self::WP_CRON_HOOK) ) {
			$schedule = apply_filters( 'action_scheduler_run_schedule', self::WP_CRON_SCHEDULE );
			wp_schedule_event( time(), $schedule, self::WP_CRON_HOOK );
		}

		add_action( self::WP_CRON_HOOK, array( self::instance(), 'run' ) );

		add_filter( 'shutdown', array( $this, 'maybe_dispatch_async_request' ) );
	}

	/**
	 * Check if we should dispatch an async request to process actions.
	 *
	 * This method is attached to 'shutdown', so is called frequently. To avoid slowing down
	 * the site, it mitigates the work performed in each request by:
	 * 1. checking if it's in the admin context and then
	 * 2. haven't run on the 'shutdown' hook within the lock time (60 seconds by default)
	 * 3. haven't exceeded the number of allowed batches.
	 *
	 * The order of these checks is important, because they run from a check on a value:
	 * 1. in memory - is_admin() maps to $GLOBALS or the WP_ADMIN constant
	 * 2. in memory - transients use autoloaded options by default
	 * 3. from a database query - has_maximum_concurrent_batches() run the query
	 *    $this->store->get_claim_count() to find the current number of claims in the DB.
	 *
	 * If all of these conditions are met, then we request an async runner check whether it
	 * should dispatch a request to process pending actions.
	 */
	public function maybe_dispatch_async_request() {
		if ( is_admin() && ! $this->is_locked( 'async-request-runner' ) && ! $this->has_maximum_concurrent_batches() ) {
			$this->async_request->maybe_dispatch();
		}
	}

	/**
	 * Check and set a lock using transients for a given amount of time (60 seconds by default).
	 *
	 * Use transient locks to avoid running database queries or other resource intensive tasks
	 * on frequently triggered hooks, like 'init' or 'shutdown'.
	 *
	 * For example, $this->maybe_dispatch_async_request() uses a lock to avoid calling
	 * $this->has_maximum_concurrent_batches() on 'shutdown', because that method calls
	 * $this->store->get_claim_count() to find the current number of claims in the database.
	 *
	 * @param string $lock_type A string to identify different lock types. Ideally, keep it below 23
	 *        characters to be compatible with versions of WordPress < 4.4, which has the 64 character
	 *        limit on option keys: https://www.barrykooij.com/maximum-option-transient-key-length/
	 * @return bool
	 */
	protected function is_locked( $lock_type ) {

		$transient_key = sprintf( 'action_scheduler_lock_%s', $lock_type );

		if ( 'yes' !== get_transient( $transient_key ) ) {
			set_transient( $transient_key, 'yes', apply_filters( 'action_scheduler_lock_time', 60, $lock_type ) );
			$is_locked = false;
		} else {
			$is_locked = true;
		}

		return $is_locked;
	}

	public function run() {
		ActionScheduler_Compatibility::raise_memory_limit();
		ActionScheduler_Compatibility::raise_time_limit( $this->get_time_limit() );
		do_action( 'action_scheduler_before_process_queue' );
		$this->run_cleanup();
		$processed_actions = 0;
		if ( false === $this->has_maximum_concurrent_batches() ) {
			$batch_size = apply_filters( 'action_scheduler_queue_runner_batch_size', 25 );
			do {
				$processed_actions_in_batch = $this->do_batch( $batch_size );
				$processed_actions         += $processed_actions_in_batch;
			} while ( $processed_actions_in_batch > 0 && ! $this->batch_limits_exceeded( $processed_actions ) ); // keep going until we run out of actions, time, or memory
		}

		do_action( 'action_scheduler_after_process_queue' );
		return $processed_actions;
	}

	protected function do_batch( $size = 100 ) {
		$claim = $this->store->stake_claim($size);
		$this->monitor->attach($claim);
		$processed_actions = 0;

		foreach ( $claim->get_actions() as $action_id ) {
			// bail if we lost the claim
			if ( ! in_array( $action_id, $this->store->find_actions_by_claim_id( $claim->get_id() ) ) ) {
				break;
			}
			$this->process_action( $action_id );
			$processed_actions++;

			if ( $this->batch_limits_exceeded( $processed_actions ) ) {
				break;
			}
		}
		$this->store->release_claim($claim);
		$this->monitor->detach();
		$this->clear_caches();
		return $processed_actions;
	}

	/**
	 * Running large batches can eat up memory, as WP adds data to its object cache.
	 *
	 * If using a persistent object store, this has the side effect of flushing that
	 * as well, so this is disabled by default. To enable:
	 *
	 * add_filter( 'action_scheduler_queue_runner_flush_cache', '__return_true' );
	 */
	protected function clear_caches() {
		if ( ! wp_using_ext_object_cache() || apply_filters( 'action_scheduler_queue_runner_flush_cache', false ) ) {
			wp_cache_flush();
		}
	}

	public function add_wp_cron_schedule( $schedules ) {
		$schedules['every_minute'] = array(
			'interval' => 60, // in seconds
			'display'  => __( 'Every minute' ),
		);

		return $schedules;
	}
}
