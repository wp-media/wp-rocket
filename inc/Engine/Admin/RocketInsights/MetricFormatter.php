<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights;

/**
 * Handles formatting and classification of performance metrics for Rocket Insights.
 *
 * @since 3.20.5
 */
class MetricFormatter {

	/**
	 * Web Vitals thresholds for metric classification.
	 *
	 * Values are in milliseconds except CLS which is unitless.
	 *
	 * @var array<string, array{good: int|float, poor: int|float}>
	 */
	private const THRESHOLDS = [
		'largest_contentful_paint' => [
			'good' => 2500,
			'poor' => 4000,
		],
		'total_blocking_time'      => [
			'good' => 200,
			'poor' => 600,
		],
		'cumulative_layout_shift'  => [
			'good' => 0.1,
			'poor' => 0.25,
		],
		'time_to_first_byte'       => [
			'good' => 800,
			'poor' => 1800,
		],
	];

	/**
	 * Metric keys in display order.
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
	 * Get the CSS class for a metric based on its value and Web Vitals thresholds.
	 *
	 * @param string     $metric_key The metric key (e.g., 'largest_contentful_paint').
	 * @param mixed|null $value      The metric value.
	 *
	 * @return string The CSS class ('ri-success', 'ri-warning', 'ri-error', 'ri-na', or '').
	 */
	public function get_metric_class( string $metric_key, $value ): string {
		if ( null === $value || '' === $value ) {
			return 'ri-na';
		}

		if ( ! isset( self::THRESHOLDS[ $metric_key ] ) ) {
			return '';
		}

		$numeric_value = floatval( $value );
		$thresholds    = self::THRESHOLDS[ $metric_key ];

		if ( $numeric_value <= $thresholds['good'] ) {
			return 'ri-success';
		}

		if ( $numeric_value >= $thresholds['poor'] ) {
			return 'ri-error';
		}

		return 'ri-warning';
	}

	/**
	 * Format a metric value for display.
	 *
	 * @param string     $metric_key The metric key (e.g., 'largest_contentful_paint').
	 * @param mixed|null $value      The metric value in milliseconds (except CLS).
	 *
	 * @return string The formatted value (e.g., '2.5s', '150ms', '0.050', 'N/A').
	 */
	public function format_metric( string $metric_key, $value ): string {
		if ( null === $value || '' === $value ) {
			return 'N/A';
		}

		// CLS is unitless and displayed with 3 decimal places.
		if ( 'cumulative_layout_shift' === $metric_key ) {
			return number_format( floatval( $value ), 3 );
		}

		// Time-based metrics are in milliseconds.
		$ms_value = floatval( $value );

		// Convert to seconds if >= 1000ms.
		if ( $ms_value >= 1000 ) {
			return number_format( $ms_value / 1000, 1 ) . 's';
		}

		return round( $ms_value ) . 'ms';
	}

	/**
	 * Get formatted metrics data ready for rendering.
	 *
	 * @param array|null $metric_data Raw metric data from the database.
	 *
	 * @return array<int, array{key: string, value: mixed, class: string, formatted: string}> Formatted metrics array.
	 */
	public function get_formatted_metrics( ?array $metric_data ): array {
		$formatted_metrics = [];

		foreach ( self::METRIC_KEYS as $key ) {
			$value = $metric_data[ $key ] ?? null;

			$formatted_metrics[] = [
				'key'       => $key,
				'value'     => $value,
				'class'     => $this->get_metric_class( $key, $value ),
				'formatted' => $this->format_metric( $key, $value ),
			];
		}

		return $formatted_metrics;
	}
}
