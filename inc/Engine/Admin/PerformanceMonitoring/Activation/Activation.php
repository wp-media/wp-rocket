<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Activation;

use WP_Rocket\Engine\Activation\ActivationInterface;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Queue\Queue;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\ActivationContext;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

/**
 * Performance Monitoring Activation
 *
 * Handles performance monitoring tasks during plugin activation
 */
class Activation implements ActivationInterface, LoggerAwareInterface {
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
	 * @var ActivationContext
	 */
	private $context;

	/**
	 * Constructor.
	 *
	 * @param Queue             $queue Queue instance.
	 * @param ActivationContext $context Context instance.
	 */
	public function __construct( Queue $queue, ActivationContext $context ) {
		$this->queue   = $queue;
		$this->context = $context;
	}

	/**
	 * Hook into activation events.
	 */
	public function activate(): void {
		add_action( 'rocket_activation', [ $this, 'schedule_homepage_tests' ] );
	}

	/**
	 * Schedule homepage performance tests on activation.
	 *
	 * @return void
	 */
	public function schedule_homepage_tests(): void {
		$this->logger::debug( 'Performance Monitoring: Activation triggered' );

		// Check if we should run homepage tests (includes first install and allowed checks).
		if ( ! $this->context->should_run_homepage_tests() ) {
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
