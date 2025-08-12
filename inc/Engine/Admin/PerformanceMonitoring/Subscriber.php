<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Queue\Queue;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring as PerformanceMonitoring_Query;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

/**
 * Performance Monitoring Subscriber
 *
 * Handles events and hooks for Performance Monitoring functionality
 */
class Subscriber implements Subscriber_Interface, LoggerAwareInterface {
	use LoggerAware;

	/**
	 * Queue instance.
	 *
	 * @var Queue
	 */
	private $queue;

	/**
	 * Context instance.
	 *
	 * @var PerformanceMonitoringContext
	 */
	private $context;

	/**
	 * Query instance.
	 *
	 * @var PerformanceMonitoring_Query
	 */
	private $query;

	/**
	 * Constructor.
	 *
	 * @param Queue                           $queue Queue instance.
	 * @param PerformanceMonitoringContext    $context Context instance.
	 * @param PerformanceMonitoring_Query     $query Query instance.
	 */
	public function __construct( Queue $queue, PerformanceMonitoringContext $context, PerformanceMonitoring_Query $query ) {
		$this->queue   = $queue;
		$this->context = $context;
		$this->query   = $query;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'init' => 'on_init',
		];
	}

	/**
	 * Initialize the performance monitoring functionality.
	 *
	 * @return void
	 */
	public function on_init(): void {
		// Basic initialization - the table creation is handled by the ServiceProvider.
		// This subscriber exists primarily to ensure the ServiceProvider is loaded.
	}
}
