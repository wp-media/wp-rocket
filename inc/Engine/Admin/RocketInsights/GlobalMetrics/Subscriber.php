<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\GlobalMetrics;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber that injects average metrics into global score data.
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * GlobalMetricsCalculator instance.
	 *
	 * @var Calculator
	 */
	private $calculator;

	/**
	 * Constructor.
	 *
	 * @param Calculator $calculator Metrics calculator instance.
	 */
	public function __construct( Calculator $calculator ) {
		$this->calculator = $calculator;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_insights_global_score_data' => 'add_average_metrics',
		];
	}

	/**
	 * Add average metrics to global score data.
	 *
	 * @param array $data Existing global score data.
	 * @return array Modified data with average_metrics added.
	 */
	public function add_average_metrics( array $data ): array {
		// Only add metrics if there are completed tests.
		if ( in_array( $data['status'], [ 'in-progress', 'no-url' ], true ) ) {
			$data['average_metrics'] = null;
			return $data;
		}

		$data['average_metrics'] = $this->calculator->calculate_average_metrics();

		return $data;
	}
}
