<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\PerformanceHints;

use WP_Rocket\Engine\Common\PerformanceHints\AJAX\ControllerInterface as AjaxControllerInterface;

interface FactoryInterface {
	/**
	 * Provides an Ajax interface.
	 *
	 * @return AjaxControllerInterface
	 */
	public function get_ajax_controller(): AjaxControllerInterface; // To return Ajax interface when created.

	/**
	 * Provides a Frontend interface.
	 */
	public function get_frontend_controller(); // To return Frontend interface when created.

	/**
	 * Provides a Table interface.
	 */
	public function table(); // To return Table interface when created.

	/**
	 * Provides a Queries interface.
	 */
	public function queries(); // To return Queries interface when created.
}
