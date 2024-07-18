<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\PerformanceHints;

use WP_Rocket\Engine\Common\PerformanceHints\Database\QueriesInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Database\TableInterface;

interface FactoryInterface {
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
	 * @return TableInterface
	 */
	public function table(): TableInterface;

	/**
	 * Provides a Queries interface.
	 *
	 * @return QueriesInterface
	 */
	public function queries(): QueriesInterface;
}
