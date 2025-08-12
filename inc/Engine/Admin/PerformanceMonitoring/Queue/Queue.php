<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Queue;

use WP_Rocket\Engine\Common\Queue\AbstractASQueue;

/**
 * Performance Monitoring Queue
 *
 * Manages Action Scheduler jobs for performance testing workflow
 */
class Queue extends AbstractASQueue {

	/**
	 * Queue group for Performance Monitoring.
	 *
	 * @var string
	 */
	protected $group = 'performance-monitoring';

	/**
	 * Performance test initiation hook.
	 *
	 * @var string
	 */
	private $initiate_test_hook = 'pma_initiate_test';

	/**
	 * Performance test status check hook.
	 *
	 * @var string
	 */
	private $check_status_hook = 'pma_check_test_status';

	/**
	 * Cleanup old tests hook.
	 *
	 * @var string
	 */
	private $cleanup_hook = 'pma_cleanup_old_tests';

	/**
	 * Schedule a performance test initiation.
	 *
	 * @param string $page_url URL to test.
	 * @param array  $options Test options (device, location, etc.).
	 * @return int Action ID.
	 */
	public function schedule_test_initiation( string $page_url, array $options = [] ): int {
		error_log('Schedule test initiation for ' . $page_url);
		$args = [
			'page_url' => $page_url,
			'options'  => $options,
		];

		return $this->add_async( $this->initiate_test_hook, $args );
	}

	/**
	 * Schedule recurring status check for a test.
	 *
	 * @param string $test_id External test ID.
	 * @param int    $db_id Database record ID.
	 * @param int    $max_attempts Maximum number of status checks.
	 * @return int Action ID.
	 */
	public function schedule_status_check( string $test_id, int $db_id, int $max_attempts = 20 ): int {
		$args = [
			'test_id'      => $test_id,
			'db_id'        => $db_id,
			'attempts'     => 0,
			'max_attempts' => $max_attempts,
		];

		// Schedule first check in 30 seconds
		return $this->schedule_single( time() + 30, $this->check_status_hook, $args );
	}

	/**
	 * Schedule cleanup job (weekly).
	 */
	public function schedule_cleanup_job(): void {
		if ( ! $this->is_scheduled( $this->cleanup_hook ) ) {
			// Schedule weekly cleanup
			$this->schedule_recurring(
				time() + DAY_IN_SECONDS,
				WEEK_IN_SECONDS,
				$this->cleanup_hook,
				[]
			);
		}
	}

	/**
	 * Cancel all status checks for a specific test.
	 *
	 * @param string $test_id External test ID.
	 */
	public function cancel_status_checks( string $test_id ): void {
		// Cancel all pending status checks for this test
		$this->cancel_all( $this->check_status_hook, [ 'test_id' => $test_id ] );
	}

	/**
	 * Check if a test initiation job is already scheduled for a URL.
	 *
	 * @param string $page_url URL to check.
	 * @return bool
	 */
	public function is_test_scheduled( string $page_url ): bool {
		return $this->is_scheduled( $this->initiate_test_hook, [ 'page_url' => $page_url ] );
	}

	/**
	 * Get all pending performance monitoring actions.
	 *
	 * @return array
	 */
	public function get_pending_actions(): array {
		if ( ! function_exists( 'as_get_scheduled_actions' ) ) {
			return [];
		}

		return as_get_scheduled_actions(
			[
				'status' => 'pending',
				'group'  => $this->group,
			]
		);
	}

	/**
	 * Cancel all performance monitoring jobs.
	 */
	public function cancel_all_jobs(): void {
		$this->cancel_all( $this->initiate_test_hook );
		$this->cancel_all( $this->check_status_hook );
		$this->cancel_all( $this->cleanup_hook );
	}
}
