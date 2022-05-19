<?php

namespace WP_Rocket\Engine\Common\Queue;

use ActionScheduler_Abstract_QueueRunner;
use ActionScheduler_Compatibility;
use WP_Rocket\Logger\Logger;

class PreloadQueueRunner extends ActionScheduler_Abstract_QueueRunner
{
	/**
	 * Cron hook name.
	 */
	const WP_CRON_HOOK = 'action_scheduler_run_queue_preload';
	/**
	 * Current runner instance.
	 *
	 * @var PreloadQueueRunner Instance.
	 */
	private static $runner = null;

	protected $group = 'rocket-preload';

	/**
	 * Async Request Queue Runner instance.
	 * We used the default one from AS.
	 *
	 * @var \ActionScheduler_AsyncRequest_QueueRunner Instance.
	 */
	protected $async_request;

	/**
	 * @var Logger
	 */
	protected $logger;

	/**
	 * @var ActionScheduler_Compatibility
	 */
	protected $compatibility;

	/**
	 * @var \ActionScheduler_Lock
	 */
	protected $locker;

	/**
	 * ActionScheduler_QueueRunner constructor.
	 *
	 * @param \ActionScheduler_Store|null                    $store Store Instance.
	 * @param \ActionScheduler_FatalErrorMonitor|null        $monitor Fatal Error monitor instance.
	 * @param \ActionScheduler_QueueCleaner|null             $cleaner Cleaner instance.
	 * @param \ActionScheduler_AsyncRequest_QueueRunner|null $async_request Async Request Queue Runner instance.
	 */
	public function __construct( \ActionScheduler_Store $store = null, \ActionScheduler_FatalErrorMonitor $monitor =
	null, \ActionScheduler_QueueCleaner $cleaner = null, \ActionScheduler_AsyncRequest_QueueRunner $async_request =
	null , ActionScheduler_Compatibility $compatibility = null, Logger $logger = null, \ActionScheduler_Lock $locker
	= null) {
		parent::__construct( $store, $monitor, $cleaner );
		$this->async_request = $async_request;
		$this->compatibility = $compatibility;
		$this->logger = $logger;
		$this->locker = $locker;
	}

	public function run($context = '')
	{
		do_action('action_scheduler_before_process_queue');
		$this->compatibility->raise_memory_limit();
		$this->compatibility->raise_time_limit($this->get_time_limit());
		$this->run_cleanup();
		$total = 0;
		while ( false === $this->has_maximum_concurrent_batches() ) {
			$size = apply_filters('action_scheduler_queue_runner_batch_size', 25);
			$total += $this->do_batch($size, $context);
		}
		do_action('action_scheduler_after_process_queue');
		return $total;
	}

	public static function instance() {
		if ( empty( self::$runner ) ) {
			self::$runner = new self();
		}
		return self::$runner;
	}

	public function init() {
		apply_filters('cron_schedules', [$this, 'add_wp_cron_schedule']);
		$next_timestamp = wp_next_scheduled(self::WP_CRON_HOOK);

		if($next_timestamp) {
			wp_unschedule_event($next_timestamp, self::WP_CRON_HOOK);
		}

		$cron_params = [ 'WP Cron' ];

		$next_schedule = wp_next_scheduled($cron_params, self::WP_CRON_HOOK);

		if(! $next_schedule) {
			$schedule = apply_filters('rocket_action_scheduler_run_schedule', [self::WP_CRON_HOOK]);
			wp_schedule_event(time(), $schedule, self::WP_CRON_HOOK, $cron_params);
		}
		add_action(self::WP_CRON_HOOK, [$this, 'run']);
		add_action('shutdown', [$this, 'maybe_dispatch_async_request']);

	}

	public function maybe_dispatch_async_request() {
		if ( is_admin() && ! $this->locker->is_locked( 'async-request-runner' ) ) {
			// Only start an async queue at most once every 60 seconds.
			$this->locker->set( 'async-request-runner' );
			$this->async_request->maybe_dispatch();
		}
	}

	public function do_batch( $size = 100, $context = '' ) {
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
			$this->logger->debug( $exception->getMessage() );

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
}
