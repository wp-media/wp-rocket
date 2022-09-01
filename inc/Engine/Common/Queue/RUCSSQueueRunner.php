<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\Queue;

use WP_Rocket\Logger\Logger;
use ActionScheduler_Abstract_QueueRunner;

class RUCSSQueueRunner extends ActionScheduler_Abstract_QueueRunner {

	/**
	 * Cron hook name.
	 */
	const WP_CRON_HOOK = 'action_scheduler_run_queue_rucss';

	/**
	 * Cron schedule interval.
	 */
	const WP_CRON_SCHEDULE = 'every_minute';

	/**
	 * Async Request Queue Runner instance.
	 * We used the default one from AS.
	 *
	 * @var \ActionScheduler_AsyncRequest_QueueRunner Instance.
	 */
	protected $async_request;

	/**
	 * Current runner instance.
	 *
	 * @var RUCSSQueueRunner Instance.
	 */
	private static $runner = null;

	/**
	 * Current queue group.
	 *
	 * @var string
	 */
	private $group = 'rocket-rucss';

	/**
	 * Get singleton instance.
	 *
	 * @return RUCSSQueueRunner Instance.
	 */
	public static function instance() {
		if ( empty( self::$runner ) ) {
			self::$runner = new RUCSSQueueRunner();
		}
		return self::$runner;
	}

	/**
	 * ActionScheduler_QueueRunner constructor.
	 *
	 * @param \ActionScheduler_Store|null                    $store Store Instance.
	 * @param \ActionScheduler_FatalErrorMonitor|null        $monitor Fatal Error monitor instance.
	 * @param Cleaner|null                                   $cleaner Cleaner instance.
	 * @param \ActionScheduler_AsyncRequest_QueueRunner|null $async_request Async Request Queue Runner instance.
	 */
	public function __construct( \ActionScheduler_Store $store = null, \ActionScheduler_FatalErrorMonitor $monitor = null, Cleaner $cleaner = null, \ActionScheduler_AsyncRequest_QueueRunner $async_request = null ) {
		if ( is_null( $cleaner ) ) {
			/**
			 * Filters the clean batch size.
			 *
			 * @since 3.11.0.5
			 *
			 * @param int $batch_size Batch size.
			 *
			 * @return int
			 */
			$batch_size = (int) apply_filters( 'rocket_action_scheduler_clean_batch_size', 100, $this->group );
			$cleaner    = new Cleaner( $store, $batch_size, $this->group );
		}

		parent::__construct( $store, $monitor, $cleaner );

		if ( is_null( $async_request ) ) {
			$async_request = new \ActionScheduler_AsyncRequest_QueueRunner( $this->store );
		}

		$this->async_request = $async_request;
	}

	/**
	 * Initialize the queue runner.
	 */
	public function init() {

		// phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval
		add_filter( 'cron_schedules', [ self::instance(), 'add_wp_cron_schedule' ] );

		// Check for and remove any WP Cron hook scheduled by Action Scheduler < 3.0.0, which didn't include the $context param.
		$next_timestamp = wp_next_scheduled( self::WP_CRON_HOOK );
		if ( $next_timestamp ) {
			wp_unschedule_event( $next_timestamp, self::WP_CRON_HOOK );
		}

		$cron_context = [ 'WP Cron' ];

		if ( ! wp_next_scheduled( self::WP_CRON_HOOK, $cron_context ) ) {
			$schedule = apply_filters( 'rocket_action_scheduler_run_schedule', self::WP_CRON_SCHEDULE );
			wp_schedule_event( time(), $schedule, self::WP_CRON_HOOK, $cron_context );
		}

		add_action( self::WP_CRON_HOOK, [ self::instance(), 'run' ] );
		$this->hook_dispatch_async_request();
	}

	/**
	 * Hook check for dispatching an async request.
	 */
	public function hook_dispatch_async_request() {
		add_action( 'shutdown', [ $this, 'maybe_dispatch_async_request' ] );
	}

	/**
	 * Unhook check for dispatching an async request.
	 */
	public function unhook_dispatch_async_request() {
		remove_action( 'shutdown', [ $this, 'maybe_dispatch_async_request' ] );
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
		if ( is_admin() && ! \ActionScheduler::lock()->is_locked( 'async-request-runner' ) ) {
			// Only start an async queue at most once every 60 seconds.
			\ActionScheduler::lock()->set( 'async-request-runner' );
			$this->async_request->maybe_dispatch();
		}
	}

	/**
	 * Process actions in the queue. Attached to self::WP_CRON_HOOK i.e. 'action_scheduler_run_queue'
	 *
	 * The $context param of this method defaults to 'WP Cron', because prior to Action Scheduler 3.0.0
	 * that was the only context in which this method was run, and the self::WP_CRON_HOOK hook had no context
	 * passed along with it. New code calling this method directly, or by triggering the self::WP_CRON_HOOK,
	 * should set a context as the first parameter. For an example of this, refer to the code seen in
	 *
	 * @see ActionScheduler_AsyncRequest_QueueRunner::handle()
	 *
	 * @param string $context Optional identifer for the context in which this action is being processed, e.g. 'WP CLI' or 'WP Cron'
	 *        Generally, this should be capitalised and not localised as it's a proper noun.
	 * @return int The number of actions processed.
	 */
	public function run( $context = 'WP Cron' ) {
		\ActionScheduler_Compatibility::raise_memory_limit();
		\ActionScheduler_Compatibility::raise_time_limit( $this->get_time_limit() );
		do_action( 'action_scheduler_before_process_queue' );// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$this->run_cleanup();
		$processed_actions = 0;
		if ( false === $this->has_maximum_concurrent_batches() ) {
			$batch_size = apply_filters( 'action_scheduler_queue_runner_batch_size', 25 );// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			do {
				$processed_actions_in_batch = $this->do_batch( $batch_size, $context );
				$processed_actions         += $processed_actions_in_batch;
			} while ( $processed_actions_in_batch > 0 && ! $this->batch_limits_exceeded( $processed_actions ) ); // keep going until we run out of actions, time, or memory.
		}

		do_action( 'action_scheduler_after_process_queue' );// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		return $processed_actions;
	}

	/**
	 * Process a batch of actions pending in the queue.
	 *
	 * Actions are processed by claiming a set of pending actions then processing each one until either the batch
	 * size is completed, or memory or time limits are reached, defined by @see $this->batch_limits_exceeded().
	 *
	 * @param int    $size The maximum number of actions to process in the batch.
	 * @param string $context Optional identifer for the context in which this action is being processed, e.g. 'WP CLI' or 'WP Cron'
	 *        Generally, this should be capitalised and not localised as it's a proper noun.
	 * @return int The number of actions processed.
	 */
	protected function do_batch( $size = 100, $context = '' ) {
		try {
			$claim = $this->store->stake_claim( $size, null, [], $this->group );
			$this->monitor->attach( $claim );
			$processed_actions = 0;

			foreach ( $claim->get_actions() as $action_id ) {
				// bail if we lost the claim.
				if ( ! in_array( $action_id, $this->store->find_actions_by_claim_id( $claim->get_id() ), true ) ) {
					break;
				}
				$this->process_action( $action_id, $context );
				$processed_actions++;

				if ( $this->batch_limits_exceeded( $processed_actions ) ) {
					break;
				}
			}
			$this->store->release_claim( $claim );
			$this->monitor->detach();
			$this->clear_caches();

			return $processed_actions;
		} catch ( \Exception $exception ) {
			Logger::debug( $exception->getMessage() );

			return 0;
		}
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
		if ( ! wp_using_ext_object_cache() || apply_filters( 'action_scheduler_queue_runner_flush_cache', false ) ) {// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			wp_cache_flush();
		}
	}

	/**
	 * Add the cron schedule.
	 *
	 * @param array $schedules Array of current schedules.
	 *
	 * @return array
	 */
	public function add_wp_cron_schedule( $schedules ) {
		if ( isset( $schedules['every_minute'] ) ) {
			return $schedules;
		}

		$schedules['every_minute'] = [
			'interval' => 60, // in seconds.
			'display'  => __( 'Every minute', 'rocket' ),
		];

		return $schedules;
	}

	/**
	 * Get the number of concurrent batches a runner allows.
	 *
	 * @return int
	 */
	public function get_allowed_concurrent_batches() {
		return 2;
	}

}
