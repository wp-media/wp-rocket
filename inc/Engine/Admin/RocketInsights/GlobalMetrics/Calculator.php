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
	public const METRIC_KEYS = [
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
		$default_metric = [
			'largest_contentful_paint' => null,
			'total_blocking_time'      => null,
			'cumulative_layout_shift'  => null,
			'time_to_first_byte'       => null,
		];

		// Get all completed tests with metric_data.
		$tests = $this->query->get_completed_metrics();

		// No completed tests - return null for all metrics.
		if ( empty( $tests ) ) {
			return $default_metric;
		}

		// Initialize accumulators.
		$totals = array_map(
			function () {
				return 0;
			},
			$default_metric
		);

		$test_count = 0;

		// Sum up all metrics.
		foreach ( $tests as $test ) {
			if ( empty( $test ) ) {
				continue;
			}

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
			return $default_metric;
		}

		// Calculate averages and format for Recommendations API.
		return [
			'largest_contentful_paint' => [
				'label'   => 'LCP',
				'value'   => $totals['largest_contentful_paint'] / $test_count,
				'tooltip' => __( 'Time until the largest visible content element renders and the main content becomes visible.', 'rocket' ),
			],
			'total_blocking_time'      => [
				'label'   => 'TBT',
				'value'   => $totals['total_blocking_time'] / $test_count,
				'tooltip' => __( 'Total time the main thread is blocked before the page becomes interactive during loading.', 'rocket' ),
			],
			'cumulative_layout_shift'  => [
				'label'   => 'CLS',
				'value'   => $totals['cumulative_layout_shift'] / $test_count,
				'tooltip' => __( 'Total amount of unexpected layout shifts during page loading, affecting visual stability.', 'rocket' ),
			],
			'time_to_first_byte'       => [
				'label'   => 'TTFB',
				'value'   => $totals['time_to_first_byte'] / $test_count,
				'tooltip' => __( 'Time from the request until the server responds, determining how soon the page starts loading.', 'rocket' ),
			],
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
