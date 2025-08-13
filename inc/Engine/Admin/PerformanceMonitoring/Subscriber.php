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
	 * @phpstan-ignore-next-line
	 */
	private $queue;

	/**
	 * Context instance.
	 *
	 * @var PerformanceMonitoringContext
	 * @phpstan-ignore-next-line
	 */
	private $context;

	/**
	 * Query instance.
	 *
	 * @var PerformanceMonitoring_Query
	 * @phpstan-ignore-next-line
	 */
	private $query;

	/**
	 * Constructor.
	 *
	 * @param Queue                        $queue Queue instance.
	 * @param PerformanceMonitoringContext $context Context instance.
	 * @param PerformanceMonitoring_Query  $query Query instance.
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
			'wp_rocket_first_install' => 'schedule_homepage_tests',
		];
	}

	/**
	 * Schedules homepage performance tests on plugin activation.
	 *
	 * This method is triggered when the plugin is first installed.
	 * It schedules both desktop and mobile tests for the homepage URL.
	 *
	 * @return void
	 */
	public function schedule_homepage_tests(): void {
		$this->logger::debug( 'Performance Monitoring: Activation triggered' );

		// Check if we should run homepage tests (includes first install and allowed checks).
		if ( ! $this->context->is_allowed() ) {
			$this->logger::debug( 'Performance Monitoring: Homepage tests not allowed, skipping' );
			return;
		}

		$homepage_url = home_url();

		// Schedule desktop test.
		$desktop_options   = [
			'device'   => 'desktop',
			'location' => 'auto',
		];
		$desktop_action_id = $this->queue->schedule_test_initiation( $homepage_url, $desktop_options );

		// Schedule mobile test.
		$mobile_options   = [
			'device'   => 'mobile',
			'location' => 'auto',
		];
		$mobile_action_id = $this->queue->schedule_test_initiation( $homepage_url, $mobile_options );

		$this->logger::info(
			'Performance Monitoring: Scheduled homepage tests on activation',
			[
				'url'               => $homepage_url,
				'desktop_action_id' => $desktop_action_id,
				'mobile_action_id'  => $mobile_action_id,
			]
		);
	}
}
