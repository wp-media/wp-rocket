<?php

namespace WP_Rocket\Engine\Preload\Admin;

use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\Logger;

class Subscriber implements Subscriber_Interface {

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Preload queue.
	 *
	 * @var Queue
	 */
	protected $queue;

	/**
	 * Preload queue runner.
	 *
	 * @var PreloadQueueRunner
	 */
	protected $queue_runner;

	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Settings           $settings Settings instance.
	 * @param Queue              $queue preload queue.
	 * @param PreloadQueueRunner $preload_queue_runner preload queue runner.
	 * @param Logger             $logger logger instance.
	 */
	public function __construct( Settings $settings, Queue $queue, PreloadQueueRunner $preload_queue_runner, Logger $logger ) {
		$this->settings     = $settings;
		$this->queue        = $queue;
		$this->queue_runner = $preload_queue_runner;
		$this->logger       = $logger;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices' => [ 'maybe_display_preload_notice' ],
			'init'          => [ 'maybe_init_preload_queue' ],
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

	/**
	 * Set the preload queue runner.
	 *
	 * @return void
	 */
	public function maybe_init_preload_queue() {
		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		$this->queue_runner->init();

	}
}
