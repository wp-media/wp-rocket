<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights;

use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;

/**
 * Global Score calculation and management class.
 *
 * Handles the calculation and caching of global performance scores based on
 * individual page scores in the Performance Monitoring system.
 */
class GlobalScore {
	/**
	 * Transient name for caching global score data.
	 */
	private const TRANSIENT_NAME = 'wpr_global_score_data';

	/**
	 * Cache expiration time in seconds (24 hours).
	 */
	private const CACHE_EXPIRATION = DAY_IN_SECONDS;

	/**
	 * Rocket Insights Query instance.
	 *
	 * @var Query
	 */
	private $query;

	/**
	 * Constructor.
	 *
	 * @param Query $query Rocket Insights Query instance.
	 */
	public function __construct( Query $query ) {
		$this->query = $query;
	}

	/**
	 * Retrieve all global score related data.
	 *
	 * @return array Array with keys: score, pages_num, status.
	 */
	public function get_global_score_data(): array {
		$cached_data = $this->get_cached_data();

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		return $this->calculate_and_cache_data();
	}

	/**
	 * Invalidate the cached data.
	 *
	 * Called when data changes to force recalculation on next request.
	 *
	 * @return void
	 */
	public function reset(): void {
		delete_transient( self::TRANSIENT_NAME );
	}

	/**
	 * Get cached data from transient.
	 *
	 * @return array|false Cached data or false if not cached.
	 */
	private function get_cached_data() {
		return get_transient( self::TRANSIENT_NAME );
	}

	/**
	 * Calculate all metrics and cache the results.
	 *
	 * @return array Calculated data containing score, pages_num, and status.
	 */
	private function calculate_and_cache_data(): array {
		$data = [
			'score'      => $this->calculate_global_score(),
			'pages_num'  => $this->calculate_pages_number(),
			'status'     => $this->calculate_current_status(),
			'is_running' => $this->calculate_current_status() === 'in-progress',
		];

		set_transient( self::TRANSIENT_NAME, $data, self::CACHE_EXPIRATION );

		return $data;
	}

	/**
	 * Calculate the global score from database.
	 *
	 * @return int Global score (0-100).
	 */
	private function calculate_global_score(): int {
		$scores = $this->query->query(
			[
				'fields'        => 'score',
				'status'        => 'completed',
				'score__not_in' => [ 0 ],
			]
			);

		if ( empty( $scores ) ) {
			return 0;
		}

		$total_score = array_sum( $scores );
		$count       = count( $scores );

		return (int) round( $total_score / $count );
	}

	/**
	 * Calculate the total number of pages being monitored.
	 *
	 * @return int Number of pages.
	 */
	private function calculate_pages_number(): int {
		return $this->query->get_total_count();
	}

	/**
	 * Calculate the current status of the monitoring system.
	 *
	 * @return string Current status.
	 */
	private function calculate_current_status(): string {
		$total_count = $this->query->get_total_count();

		// No URLs are being monitored.
		if ( 0 === $total_count ) {
			return 'no-url';
		}

		// Check if any URLs are in progress.
		$in_progress_count = $this->query->query(
			[
				'count'      => true,
				'status__in' => [ 'to-submit', 'pending', 'in-progress' ],
			]
			);

		if ( (int) $in_progress_count > 0 ) {
			return 'in-progress';
		}

		// Check if any URLs are blurred.
		$blurred_count = $this->query->query(
		[
			'count'      => true,
			'status__in' => [ 'completed' ],
			'is_blurred' => 1,
		]
		);

		if ( (int) $blurred_count > 0 ) {
			return 'blurred';
		}

		// Check if *all* URLs have failed.
		$failed_count = $this->query->query(
			[
				'count'      => true,
				'status__in' => [ 'failed' ],
			]
		);

		if ( (int) $failed_count === (int) $total_count ) {
			return 'failed';
		}

		// All tests are complete and none are blurred.
		return 'complete';
	}
}
