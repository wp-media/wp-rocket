<?php

namespace WP_Rocket\Engine\Preload\Admin;

use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\Logger;

class Subscriber implements Subscriber_Interface {

	/**
	 * Settings instance
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * @var Queue
	 */
	protected $queue;

	/**
	 * @var PreloadQueueRunner
	 */
	protected $queueRunner;

	/**
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Settings $settings Settings instance.
	 */
	public function __construct( Settings $settings, Queue $queue, PreloadQueueRunner $preloadQueueRunner, Logger $logger) {
		$this->settings = $settings;
		$this->queue = $queue;
		$this->queueRunner = $preloadQueueRunner;
		$this->logger = $logger;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices' => [ 'maybe_display_preload_notice' ],
		];
	}

	/**
	 * Maybe display the preload notice.
	 *
	 * @return void
	 */
	public function maybe_display_preload_notice() {
		$this->settings->maybe_display_preload_notice();
	}

	public function schedule_preload_pending_jobs_cron() {
		if ( ! $this->settings->is_enabled() ) {
			if ( ! $this->queue->is_pending_jobs_cron_scheduled() ) {
				return;
			}

			$this->logger->debug( 'PRELOAD: Cancel pending jobs cron job because of disabling PRELOAD option.' );

			$this->queue->cancel_pending_jobs_cron();
			return;
		}

		$this->queueRunner->init();

		/**
		 * Filters the cron interval.
		 *
		 * @param int $interval Interval in seconds.
		 */
		$interval = apply_filters( 'rocket_preload_pending_jobs_cron_interval', 1 * rocket_get_constant( 'MINUTE_IN_SECONDS', 60 ) );

		$this->logger->debug( "PRELOAD: Schedule pending jobs Cron job with interval {$interval} seconds." );

		$this->queue->schedule_pending_jobs_cron( $interval );
	}
}
