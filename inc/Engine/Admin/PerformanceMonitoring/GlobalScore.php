<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring as PMQuery;

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
	 * Performance Monitoring Query instance.
	 *
	 * @var PMQuery
	 */
	private $query;

	/**
	 * Constructor.
	 *
	 * @param PMQuery $query Performance Monitoring Query instance.
	 */
	public function __construct( PMQuery $query ) {
		$this->query = $query;
	}

	/**
	 * Get the global score.
	 *
	 * Calculates the average of all successful individual scores.
	 * Uses transient caching for performance.
	 *
	 * @return int The global score (0-100) or 0 if no valid scores.
	 */
	public function get_global_score(): int {
		$cached_data = $this->get_cached_data();

		if ( false !== $cached_data && isset( $cached_data['score'] ) ) {
			return (int) $cached_data['score'];
		}

		return $this->calculate_and_cache_data()['score'];
	}

	/**
	 * Get the number of pages being monitored.
	 *
	 * Returns the total count of pages regardless of status.
	 * Uses transient caching for performance.
	 *
	 * @return int The number of pages being monitored.
	 */
	public function get_pages_number(): int {
		$cached_data = $this->get_cached_data();

		if ( false !== $cached_data && isset( $cached_data['pages_num'] ) ) {
			return (int) $cached_data['pages_num'];
		}

		return $this->calculate_and_cache_data()['pages_num'];
	}

	/**
	 * Get the current status of the monitoring system.
	 *
	 * Returns one of: 'no-url', 'in-progress', 'complete', 'blurred'.
	 * Uses transient caching for performance.
	 *
	 * @return string Current status.
	 */
	public function get_current_status(): string {
		$cached_data = $this->get_cached_data();

		if ( false !== $cached_data && isset( $cached_data['status'] ) ) {
			return $cached_data['status'];
		}

		return $this->calculate_and_cache_data()['status'];
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
			'score'     => $this->calculate_global_score(),
			'pages_num' => $this->calculate_pages_number(),
			'status'    => $this->calculate_current_status(),
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
		return $this->query->query( [ 'count' => true ] );
	}

	/**
	 * Calculate the current status of the monitoring system.
	 *
	 * @return string Current status.
	 */
	private function calculate_current_status(): string {
		$total_count = $this->query->query( [ 'count' => true ] );

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
		// Note: is_blurred column will be added in issue #7599.
		$blurred_count = 0;
		// TODO: Uncomment when is_blurred column is available
		// $blurred_count = $this->query->query( [
		// 'count'      => true,
		// 'is_blurred' => 1,
		// ] );.

		if ( $blurred_count > 0 ) { // @phpstan-ignore-line
			return 'blurred';
		}

		// All tests are complete and none are blurred.
		return 'complete';
	}
}
