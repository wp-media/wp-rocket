<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring;

class Controller {
	/**
	 * Query object.
	 *
	 * @var PerformanceMonitoring
	 */
	private $query;

	/**
	 * Constructor.
	 *
	 * @param PerformanceMonitoring $query Query object.
	 */
	public function __construct( PerformanceMonitoring $query ) {
		$this->query = $query;
	}

	/**
	 * Get items from the database.
	 *
	 * @return array|int
	 */
	public function get_items() {
		$query_params = [
			'orderby' => 'modified',
			'order'   => 'asc',
			'number'  => 20,
		];
		return $this->query->query( $query_params );
	}
}
