<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\GlobalMetrics;

use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;

/**
 * Calculates average performance metrics across all completed tests.
 */
class Calculator {
	/**
	 * Metric keys to calculate averages for.
	 *
	 * @var string[]
	 */
	private const METRIC_KEYS = [
		'largest_contentful_paint',
		'total_blocking_time',
		'cumulative_layout_shift',
		'time_to_first_byte',
	];

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
	 * Calculate average metrics from all completed tests.
	 *
	 * Metrics are returned in the format expected by the Recommendations API:
	 * - LCP, TTFB: seconds (converted from milliseconds)
	 * - CLS: unitless decimal
	 * - TBT: milliseconds (kept as-is)
	 *
	 * @return array {
	 *     Average performance metrics.
	 *
	 *     @type float|null $lcp  Largest Contentful Paint in seconds.
	 *     @type float|null $ttfb Time to First Byte in seconds.
	 *     @type float|null $cls  Cumulative Layout Shift.
	 *     @type int|null   $tbt  Total Blocking Time in milliseconds.
	 * }
	 */
	public function calculate_average_metrics(): array {
		// Get all completed tests with metric_data.
		$tests = $this->query->get_completed_metrics();

		// No completed tests - return null for all metrics.
		if ( empty( $tests ) ) {
			return [
				'lcp'  => null,
				'ttfb' => null,
				'cls'  => null,
				'tbt'  => null,
			];
		}

		// Initialize accumulators.
		$totals = [
			'largest_contentful_paint' => 0,
			'total_blocking_time'      => 0,
			'cumulative_layout_shift'  => 0,
			'time_to_first_byte'       => 0,
		];

		$test_count = 0;

		// Sum up all metrics.
		foreach ( $tests as $test ) {
			$metric_data = json_decode( $test, true );

			// Skip if metric_data is empty or not an array.
			if ( empty( $metric_data ) || ! is_array( $metric_data ) ) {
				continue;
			}

			// Sum all metric values.
			foreach ( self::METRIC_KEYS as $key ) {
				if ( isset( $metric_data[ $key ] ) ) {
					$totals[ $key ] += (float) $metric_data[ $key ];
				}
			}

			++$test_count;
		}

		// No valid tests found.
		if ( 0 === $test_count ) {
			return [
				'lcp'  => null,
				'ttfb' => null,
				'cls'  => null,
				'tbt'  => null,
			];
		}

		// Calculate averages and format for Recommendations API.
		return [
			// LCP: milliseconds → seconds.
			'lcp'  => round( ( $totals['largest_contentful_paint'] / $test_count ) / 1000, 3 ),
			// TTFB: milliseconds → seconds.
			'ttfb' => round( ( $totals['time_to_first_byte'] / $test_count ) / 1000, 3 ),
			// CLS: unitless decimal.
			'cls'  => round( $totals['cumulative_layout_shift'] / $test_count, 3 ),
			// TBT: keep in milliseconds (integer).
			'tbt'  => (int) round( $totals['total_blocking_time'] / $test_count ),
		];
	}

	/**
	 * Check if there are any completed tests available.
	 *
	 * @return bool True if at least one completed test exists.
	 */
	public function has_completed_tests(): bool {
		return 0 < (int) $this->query->get_completed_count();
	}
}
