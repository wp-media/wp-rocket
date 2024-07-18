<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\PerformanceHints;

use WP_Rocket\Engine\Common\PerformanceHints\Database\Queries;
use WP_Rocket\Engine\Common\PerformanceHints\Database\Table;

interface AbstractFactory {
	/**
	 * Provides an Ajax interface.
	 */
	public function ajax(); // To return Ajax interface when created.

	/**
	 * Provides a Frontend interface.
	 */
	public function frontend(); // To return Frontend interface when created.

	/**
	 * Provides a Table interface.
	 *
	 * @return Table
	 */
	public function table(): Table;

	/**
	 * Provides a Queries interface.
	 *
	 * @return Queries
	 */
	public function queries(): Queries;
}
