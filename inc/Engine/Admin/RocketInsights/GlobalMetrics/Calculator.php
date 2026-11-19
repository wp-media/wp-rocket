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
	 * Get the display label and tooltip of each metric, in display order.
	 *
	 * @return array<string, array{label: string, tooltip: string}> Metric definitions keyed by metric key.
	 */
	public static function get_metric_definitions(): array {
		return [
			'largest_contentful_paint' => [
				'label'   => 'LCP',
				'tooltip' => __( 'Time until the largest visible content element renders and the main content becomes visible.', 'rocket' ),
			],
			'total_blocking_time'      => [
				'label'   => 'TBT',
				'tooltip' => __( 'Total time the main thread is blocked before the page becomes interactive during loading.', 'rocket' ),
			],
			'cumulative_layout_shift'  => [
				'label'   => 'CLS',
				'tooltip' => __( 'Total amount of unexpected layout shifts during page loading, affecting visual stability.', 'rocket' ),
			],
			'time_to_first_byte'       => [
				'label'   => 'TTFB',
				'tooltip' => __( 'Time from the request until the server responds, determining how soon the page starts loading.', 'rocket' ),
			],
		];
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
		$metrics = [];

		foreach ( self::get_metric_definitions() as $key => $definition ) {
			$metrics[ $key ] = [
				'label'   => $definition['label'],
				'value'   => $totals[ $key ] / $test_count,
				'tooltip' => $definition['tooltip'],
			];
		}

		return $metrics;
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
