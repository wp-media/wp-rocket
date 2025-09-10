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
	 * Get global score data.
	 *
	 * @return string Array of global score data.
	 */
	public function get_global_score_data(): array {
		return [
			'status'    => 'no-url',
			'pages_num' => 0,
			'score'     => 0,
		];
	}
}
