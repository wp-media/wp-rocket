<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\PerformanceHints;

use WP_Rocket\Engine\Common\PerformanceHints\AJAX\ControllerInterface as AjaxControllerInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Admin\ControllerInterface as AdminControllerInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface as FrontendControllerInterface;
use WP_Rocket\Engine\Common\Context\ContextInterface;

interface FactoryInterface {
	/**
	 * Provides an Ajax interface.
	 *
	 * @return AjaxControllerInterface
	 */
	public function get_ajax_controller(): AjaxControllerInterface;

	/**
	 * Provides a Frontend interface.
	 *
	 * @return FrontendControllerInterface
	 */
	public function get_frontend_controller(): FrontendControllerInterface;

	/**
	 * Provides a Table interface.
	 */
	public function table(); // To return Table interface when created.

	/**
	 * Provides a Queries interface.
	 */
	public function queries(); // To return Queries interface when created.

	/**
	 * Provides a Context interface
	 *
	 * @return ContextInterface
	 */
	public function get_context(): ContextInterface;

	/**
	 * Provides an Admin interface
	 */
	public function get_admin_controller(): AdminControllerInterface;
}
