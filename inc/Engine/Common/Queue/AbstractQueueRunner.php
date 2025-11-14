<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\Queue;

use WP_Rocket\Engine\Common\Queue\Cleaner;
use WP_Rocket\Logger\Logger;
use ActionScheduler_Abstract_QueueRunner;
use ActionScheduler_Store;
use ActionScheduler_FatalErrorMonitor;
use ActionScheduler_AsyncRequest_QueueRunner;

/**
 * Abstract queue runner for WP Rocket SaaS features.
 *
 * Provides common functionality for RUCSS, Rocket Insights, and other queue runners.
 * Each implementation defines its own schedule interval and queue group.
 *
 * @since 3.20
 */
abstract class AbstractQueueRunner extends ActionScheduler_Abstract_QueueRunner {

	/**
	 * Async Request Queue Runner instance.
	 * We used the default one from AS.
	 *
	 * @var ActionScheduler_AsyncRequest_QueueRunner Instance.
	 */
	protected $async_request;

	/**
	 * Current queue group.
	 *
	 * @var string
	 */
	protected $group;

	/**
	 * Constructor
	 *
	 * @since 3.20
	 *
	 * @param ActionScheduler_Store|null                    $store Store Instance.
	 * @param ActionScheduler_FatalErrorMonitor|null        $monitor Fatal Error monitor instance.
	 * @param Cleaner|null                                  $cleaner Cleaner instance.
	 * @param ActionScheduler_AsyncRequest_QueueRunner|null $async_request Async Request Queue Runner instance.
	 */
	public function __construct( ?ActionScheduler_Store $store = null, ?ActionScheduler_FatalErrorMonitor $monitor = null, ?Cleaner $cleaner = null, ?ActionScheduler_AsyncRequest_QueueRunner $async_request = null ) {
		if ( is_null( $cleaner ) ) {
			/**
			 * Filters the batch size for cleaning action scheduler.
			 *
			 * @since 3.20
			 *
			 * @param int    $batch_size Batch size.
			 * @param string $group The group name.
			 *
			 * @return int
			 */
			$batch_size = wpm_apply_filters_typed( 'integer', 'rocket_action_scheduler_clean_batch_size', 100, $this->get_group() );
			$cleaner    = new Cleaner( $store, $batch_size, $this->get_group() );
		}
		parent::__construct( $store, $monitor, $cleaner );

		if ( is_null( $async_request ) ) {
			$async_request = new \ActionScheduler_AsyncRequest_QueueRunner( $this->store );
		}

		$this->async_request = $async_request;
	}

	/**
	 * Initialize the queue runner.
	 *
	 * @since 3.20
	 *
	 * @return void
	 */
	public function init() {
		// phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval
		add_filter( 'cron_schedules', [ $this, 'add_wp_cron_schedule' ] );

		// Check for and remove any WP Cron hook scheduled by Action Scheduler < 3.0.0, which didn't include the $context param.
		$next_timestamp = wp_next_scheduled( $this->get_wp_cron_hook() );
		if ( $next_timestamp ) {
			wp_unschedule_event( $next_timestamp, $this->get_wp_cron_hook() );
		}

		$cron_context = [ 'WP Cron' ];

		if ( ! wp_next_scheduled( $this->get_wp_cron_hook(), $cron_context ) ) {
			/**
			 * Filters the schedule for action scheduler.
			 *
			 * @since 3.20
			 *
			 * @param string $schedule The schedule name.
			 *
			 * @return string
			 */
			$schedule = wpm_apply_filters_typed( 'string', 'rocket_action_scheduler_run_schedule', $this->get_wp_cron_schedule() );
			wp_schedule_event( time(), $schedule, $this->get_wp_cron_hook(), $cron_context );
		}

		// @phpstan-ignore-next-line Action callback returns int but should not return anything.
		add_action( $this->get_wp_cron_hook(), [ $this, 'run' ] );
		$this->hook_dispatch_async_request();
	}

	/**
	 * Hook check for dispatching an async request.
	 *
	 * @since 3.20
	 *
	 * @return void
	 */
	public function hook_dispatch_async_request() {
		add_action( 'shutdown', [ $this, 'maybe_dispatch_async_request' ] );
	}

	/**
	 * Unhook check for dispatching an async request.
	 *
	 * @since 3.20
	 *
	 * @return void
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
	 *
	 * @since 3.20
	 *
	 * @return void
	 */
	public function maybe_dispatch_async_request() {
		if ( is_admin() && ! \ActionScheduler::lock()->is_locked( 'async-request-runner' ) ) {
			// Only start an async queue at most once every 60 seconds.
			\ActionScheduler::lock()->set( 'async-request-runner' );
			$this->async_request->maybe_dispatch();
		}
	}

	/**
	 * Process actions in the queue. Attached to get_wp_cron_hook() hook.
	 *
	 * The $context param of this method defaults to 'WP Cron', because prior to Action Scheduler 3.0.0
	 * that was the only context in which this method was run, and the hook had no context
	 * passed along with it. New code calling this method directly, or by triggering the hook,
	 * should set a context as the first parameter.
	 *
	 * @since 3.20
	 *
	 * @param mixed $context Optional identifier for the context in which this action is being processed, e.g. 'WP CLI' or 'WP Cron'
	 *        Generally, this should be capitalised and not localised as it's a proper noun.
	 *
	 * @return int The number of actions processed.
	 */
	public function run( $context = 'WP Cron' ) {
		\ActionScheduler_Compatibility::raise_memory_limit();
		\ActionScheduler_Compatibility::raise_time_limit( $this->get_time_limit() );
		do_action( 'action_scheduler_before_process_queue' );// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$this->run_cleanup();
		$processed_actions = 0;
		if ( false === $this->has_maximum_concurrent_batches() ) {
			/**
			 * Filters the batch size for action scheduler queue runner.
			 *
			 * @since 3.20
			 *
			 * @param int $batch_size Batch size.
			 *
			 * @return int
			 */
			$batch_size = wpm_apply_filters_typed( 'integer', 'action_scheduler_queue_runner_batch_size', 25 );// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
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
	 * @since 3.20
	 *
	 * @param int    $size The maximum number of actions to process.
	 * @param string $context Optional identifier for the context in which this action is being processed.
	 *
	 * @return int Number of actions processed.
	 */
	protected function do_batch( $size = 100, $context = '' ) {
		// Set group filter if the store supports it
		if ( method_exists( $this->store, 'set_claim_filter' ) ) {
			$this->store->set_claim_filter( 'group', $this->get_group() );
		}

		$claim             = null;
		$processed_actions = 0;

		try {
			$claim = $this->store->stake_claim( $size );
			$this->monitor->attach( $claim );

			foreach ( $claim->get_actions() as $action_id ) {
				// bail if we lost the claim.
				if ( ! in_array( $action_id, $this->store->find_actions_by_claim_id( $claim->get_id() ), true ) ) {
					break;
				}
				$this->process_action( $action_id, $context );
				++$processed_actions;

				if ( $this->batch_limits_exceeded( $processed_actions ) ) {
					break;
				}
			}
		} catch ( \Throwable $e ) {
			// Log the exception if Logger is available
			if ( class_exists( '\WP_Rocket\Logger\Logger' ) ) {
				Logger::error( 'Exception in do_batch: ' . $e->getMessage(), [ 'exception' => $e ] );
			}
			// Re-throw to maintain existing error handling behavior.
			throw $e;
		} finally {
			if ( $claim ) {
				$this->store->release_claim( $claim );
			}
			$this->monitor->detach();
			// Reset group filter
			$this->reset_group();
			// Clear caches to prevent memory issues.
			$this->clear_caches();
		}

		return $processed_actions;
	}

	/**
	 * Reset group in store's claim filter.
	 *
	 * @since 3.20
	 *
	 * @return void
	 */
	private function reset_group() {
		if ( ! method_exists( $this->store, 'set_claim_filter' ) ) {
			return;
		}
		$this->store->set_claim_filter( 'group', '' );
	}

	/**
	 * Running large batches can eat up memory, as WP adds data to its object cache.
	 *
	 * If using a persistent object store, this has the side effect of flushing that
	 * as well, so this is disabled by default. To enable:
	 *
	 * add_filter( 'action_scheduler_queue_runner_flush_cache', '__return_true' );
	 *
	 * @since 3.20
	 *
	 * @return void
	 */
	protected function clear_caches() {
		/**
		 * Filters whether to flush cache in action scheduler queue runner.
		 *
		 * @since 3.20
		 *
		 * @param bool $flush_cache Whether to flush cache.
		 *
		 * @return bool
		 */
		if ( ! wp_using_ext_object_cache() || wpm_apply_filters_typed( 'boolean', 'action_scheduler_queue_runner_flush_cache', false ) ) {// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			wp_cache_flush();
		}
	}

	/**
	 * Add the cron schedule.
	 *
	 * @since 3.20
	 *
	 * @param array $schedules Array of current schedules.
	 *
	 * @return array
	 */
	abstract public function add_wp_cron_schedule( $schedules );

	/**
	 * Get the number of concurrent batches a runner allows.
	 *
	 * @since 3.20
	 *
	 * @return int
	 */
	public function get_allowed_concurrent_batches() {
		return 2;
	}

	/**
	 * Get the cron hook name.
	 *
	 * @since 3.20
	 *
	 * @return string
	 */
	abstract protected function get_wp_cron_hook(): string;

	/**
	 * Get the cron schedule interval.
	 *
	 * @since 3.20
	 *
	 * @return string
	 */
	abstract protected function get_wp_cron_schedule(): string;

	/**
	 * Get the queue group name.
	 *
	 * @since 3.20
	 *
	 * @return string
	 */
	abstract protected function get_group(): string;
}
