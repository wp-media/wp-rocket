<?php

/**
 * Class ActionScheduler_QueueRunner
 */
class ActionScheduler_QueueRunner {
	const WP_CRON_HOOK = 'action_scheduler_run_queue';

	const WP_CRON_SCHEDULE = 'every_minute';

	/** @var ActionScheduler_QueueRunner  */
	private static $runner = NULL;
	/** @var ActionScheduler_Store */
	private $store = NULL;

	/** @var ActionScheduler_FatalErrorMonitor */
	private $monitor = NULL;

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

	public function __construct( ActionScheduler_Store $store = NULL ) {
		$this->store = $store ? $store : ActionScheduler_Store::instance();
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
	}

	public function run() {
		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );
		@set_time_limit( apply_filters( 'action_scheduler_queue_runner_time_limit', 600 ) );
		do_action( 'action_scheduler_before_process_queue' );
		$this->run_cleanup();
		$count = 0;
		if ( $this->store->get_claim_count() < apply_filters( 'action_scheduler_queue_runner_concurrent_batches', 5 ) ) {
			$batch_size = apply_filters( 'action_scheduler_queue_runner_batch_size', 25 );
			$this->monitor = new ActionScheduler_FatalErrorMonitor( $this->store );
			$count = $this->do_batch( $batch_size );
			unset( $this->monitor );
		}

		do_action( 'action_scheduler_after_process_queue' );
		return $count;
	}

	protected function run_cleanup() {
		$cleaner = new ActionScheduler_QueueCleaner( $this->store );
		$cleaner->delete_old_actions();
		$cleaner->reset_timeouts();
		$cleaner->mark_failures();
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
		}
		$this->store->release_claim($claim);
		$this->monitor->detach();
		$this->clear_caches();
		return $processed_actions;
	}

	public function process_action( $action_id ) {
		try {
			do_action( 'action_scheduler_before_execute', $action_id );
			$action = $this->store->fetch_action( $action_id );
			$this->store->log_execution( $action_id );
			$action->execute();
			do_action( 'action_scheduler_after_execute', $action_id );
			$this->store->mark_complete( $action_id );
		} catch ( Exception $e ) {
			$this->store->mark_failure( $action_id );
			do_action( 'action_scheduler_failed_execution', $action_id, $e );
		}
		$this->schedule_next_instance( $action );
	}

	protected function schedule_next_instance( ActionScheduler_Action $action ) {

		$schedule = $action->get_schedule();
		$next     = $schedule->next( as_get_datetime_object() );

		if ( ! is_null( $next ) && $schedule->is_recurring() ) {
			$this->store->save_action( $action, $next );
		}
	}

	/**
	 * Running large batches can eat up memory, as WP adds data to its object cache.
	 *
	 * If using a persistent object store, this has the side effect of flushing that
	 * as well, so this is disabled by default. To enable:
	 *
	 * add_filter( 'action_scheduler_queue_runner_flush_cache', '__return_true' );
	 *
	 * @return void
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
 